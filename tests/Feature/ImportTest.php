<?php

use App\Enums\ImportStatus;
use App\Models\Customer;
use App\Models\FlowerVariety;
use App\Models\ImportRun;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Support\CompanyContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function uploadCsv(string $type, string $contents, string $filename = 'datos.csv'): ImportRun
{
    test()->post(route('imports.store'), [
        'type' => $type,
        'file' => UploadedFile::fake()->createWithContent($filename, $contents),
        'header_row' => 1,
    ])->assertRedirect();

    $run = ImportRun::query()->latest('id')->first();

    expect($run)->not->toBeNull();

    return $run;
}

it('adivina el mapeo de columnas por el encabezado en español', function () {
    actingAsRole('admin');

    $run = uploadCsv('customers', <<<'CSV'
    Cliente,País,Ciudad,Correo,Días de crédito,Límite de crédito
    Miami Roses,United States,Miami,pagos@miamiroses.com,30,15000
    CSV);

    expect($run->column_map['name'])->toBe(0)
        ->and($run->column_map['country'])->toBe(1)
        ->and($run->column_map['city'])->toBe(2)
        ->and($run->column_map['invoice_email'])->toBe(3)
        ->and($run->column_map['credit_days'])->toBe(4)
        ->and($run->column_map['credit_limit'])->toBe(5)
        ->and($run->column_map['whatsapp'])->toBeNull();
});

it('muestra la vista previa separando filas válidas y con error', function () {
    actingAsRole('admin');

    $run = uploadCsv('customers', <<<'CSV'
    Cliente,País,Correo
    Miami Roses,United States,pagos@miamiroses.com
    Moscow Flowers,Russia,no-es-un-correo
    ,Ecuador,x@y.com
    CSV);

    $this->get(route('imports.show', $run))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('imports/show')
            ->where('preview.total', 3)
            ->where('preview.valid', 1)
            ->where('preview.invalid', 2)
            ->has('preview.invalid_rows', 2));
});

it('importa solo las filas válidas y deja el reporte de rechazos', function () {
    actingAsRole('admin');

    $run = uploadCsv('customers', <<<'CSV'
    Cliente,País,Correo,Días de crédito,Límite de crédito
    Miami Roses,United States,pagos@miamiroses.com,30,"15.000,50"
    Moscow Flowers,Russia,no-es-un-correo,15,2000
    ,Ecuador,x@y.com,0,0
    CSV);

    $this->post(route('imports.commit', $run))->assertRedirect(route('imports.show', $run->id));

    $run->refresh();

    expect($run->status)->toBe(ImportStatus::Committed)
        ->and($run->committed_rows)->toBe(1)
        ->and($run->invalid_rows)->toBe(2)
        ->and($run->error_report_path)->not->toBeNull()
        ->and(Customer::query()->count())->toBe(1);

    $customer = Customer::query()->first();

    expect($customer->name)->toBe('Miami Roses')
        ->and($customer->credit_days)->toBe(30)
        ->and($customer->credit_limit_cents)->toBe(1500050);

    Storage::disk('local')->assertExists($run->error_report_path);

    $this->get(route('imports.report', $run))->assertOk();
});

it('deja corregir el mapeo a mano', function () {
    actingAsRole('admin');

    $run = uploadCsv('customers', <<<'CSV'
    Columna A,Columna B
    Quito Roses,Ecuador
    CSV);

    expect($run->column_map['name'])->toBeNull();

    $this->put(route('imports.update', $run), [
        'header_row' => 1,
        'column_map' => ['name' => 0, 'country' => 1],
    ])->assertRedirect(route('imports.show', $run->id));

    $run->refresh();

    expect($run->column_map['name'])->toBe(0)
        ->and($run->status)->toBe(ImportStatus::Mapped);

    $this->post(route('imports.commit', $run));

    expect(Customer::query()->where('name', 'Quito Roses')->exists())->toBeTrue();
});

it('importa variedades', function () {
    actingAsRole('admin');

    $run = uploadCsv('varieties', <<<'CSV'
    Variedad,Color,Tipo
    Explorer,Rojo,standard
    Fatima,Durazno,garden
    CSV);

    $this->post(route('imports.commit', $run));

    expect(FlowerVariety::query()->count())->toBe(2)
        ->and(FlowerVariety::query()->where('name', 'Fatima')->first()->type->value)->toBe('garden');
});

it('importa productos aceptando el largo con unidad', function () {
    actingAsRole('admin');

    $run = uploadCsv('products', <<<'CSV'
    Variedad,Largo,Tallos
    Explorer,60 cm,25
    Explorer,70cm,25
    Mondial,abc,25
    CSV);

    $this->post(route('imports.commit', $run));
    $run->refresh();

    expect($run->committed_rows)->toBe(2)
        ->and($run->invalid_rows)->toBe(1)
        ->and(Product::query()->count())->toBe(2)
        ->and(Product::query()->with('flowerVariety')->get()->map->label->all())
        ->toEqualCanonicalizing(['Explorer 60 cm x25', 'Explorer 70 cm x25']);
});

it('importa precios y rechaza clientes que no existen', function () {
    actingAsRole('admin');
    Customer::factory()->create(['name' => 'Miami Roses']);

    $run = uploadCsv('prices', <<<'CSV'
    Variedad,Largo,Cliente,Temporada,Precio,Desde
    Explorer,60,,,0.45,2026-01-01
    Explorer,60,Miami Roses,San Valentín,1.10,2026-01-01
    Explorer,60,Cliente Fantasma,,0.50,2026-01-01
    CSV);

    $this->post(route('imports.commit', $run));
    $run->refresh();

    expect($run->committed_rows)->toBe(2)
        ->and($run->invalid_rows)->toBe(1)
        ->and(ProductPrice::query()->count())->toBe(2)
        ->and(ProductPrice::query()->whereNotNull('season')->first()->season->value)->toBe('valentines');
});

it('solo importa a la empresa activa', function () {
    $otherCompany = companyWithModules();
    actingAsRole('admin');

    $run = uploadCsv('customers', <<<'CSV'
    Cliente
    Solo Mi Finca
    CSV);

    $this->post(route('imports.commit', $run));

    expect(Customer::query()->count())->toBe(1);

    CompanyContext::runFor($otherCompany, function () {
        expect(Customer::query()->count())->toBe(0);
    });
});

it('rechaza archivos que no son hojas de cálculo', function () {
    actingAsRole('admin');

    $this->post(route('imports.store'), [
        'type' => 'customers',
        'file' => UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg'),
        'header_row' => 1,
    ])->assertSessionHasErrors('file');
});

it('solo deja importar a quien tiene el permiso', function () {
    $company = companyWithModules();

    actingAsRole('sales', $company);
    $this->get(route('imports.index'))->assertOk();

    actingAsRole('warehouse_lead', $company);
    $this->get(route('imports.index'))->assertForbidden();
});

it('aisla las corridas de importación entre empresas', function () {
    assertCompanyScoped(ImportRun::class);
});
