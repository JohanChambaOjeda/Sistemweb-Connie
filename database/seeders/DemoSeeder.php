<?php

namespace Database\Seeders;

use App\Enums\Incoterm;
use App\Enums\ModuleKey;
use App\Enums\RoleName;
use App\Enums\Season;
use App\Models\BoxType;
use App\Models\CargoAgency;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\FlowerVariety;
use App\Models\Mark;
use App\Models\Module;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Database\Seeder;

/**
 * Datos de ejemplo con la operación real de Cielos de América EC, más una
 * segunda finca para comprobar que una empresa no ve los datos de la otra.
 */
class DemoSeeder extends Seeder
{
    /**
     * Variedades que cultiva la finca.
     *
     * @var array<string, string>
     */
    private const VARIETIES = [
        'Explorer' => 'Rojo',
        'Hot Spot' => 'Bicolor',
        'Gotcha' => 'Rosado',
        'Sweet Spot' => 'Bicolor',
        'Be Sweet' => 'Rosado',
        'Fatima' => 'Durazno',
        'Vicky' => 'Blanco',
        'Mondial' => 'Blanco',
    ];

    /** @var list<int> */
    private const LENGTHS = [40, 50, 60, 70, 80, 90];

    /** @var list<int> */
    private const COMMON_LENGTHS = [50, 60, 70];

    public function run(): void
    {
        $farm = $this->createCompany(
            name: 'Cielos de América EC',
            legalName: 'Cielos de América EC S.A.',
            taxId: '0291234567001',
            address: 'Vía a Guaranda, Bolívar, Ecuador',
            moduleKeys: ModuleKey::values(),
        );

        $demo = $this->createCompany(
            name: 'Finca Demo',
            legalName: 'Finca Demo Cía. Ltda.',
            taxId: '1790012345001',
            address: 'Cayambe, Pichincha, Ecuador',
            moduleKeys: [ModuleKey::Sales->value, ModuleKey::Postharvest->value],
        );

        $this->createUsers($farm, [
            'admin' => ['Johan Chamba', 'admin@cielosdeamerica.com'],
            'sales' => ['Daniela Suárez', 'ventas@cielosdeamerica.com'],
            'billing' => ['Pablo Andrade', 'facturacion@cielosdeamerica.com'],
            'postharvest_lead' => ['María Quishpe', 'poscosecha@cielosdeamerica.com'],
            'warehouse_lead' => ['Luis Caiza', 'bodega@cielosdeamerica.com'],
        ]);

        $this->createUsers($demo, [
            'admin' => ['Admin Finca Demo', 'admin@fincademo.com'],
        ]);

        CompanyContext::runFor($farm, fn () => $this->seedFarm());
        CompanyContext::runFor($demo, fn () => $this->seedDemoFarm());
    }

    /**
     * @param  list<string>  $moduleKeys
     */
    private function createCompany(
        string $name,
        string $legalName,
        string $taxId,
        string $address,
        array $moduleKeys,
    ): Company {
        /** @var Company $company */
        $company = Company::query()->updateOrCreate(
            ['name' => $name],
            [
                'legal_name' => $legalName,
                'tax_id' => $taxId,
                'address' => $address,
                'phone' => '+593 32 123 456',
                'email' => 'info@'.str_replace(' ', '', mb_strtolower($name)).'.com',
                'default_locale' => 'es',
                'timezone' => 'America/Guayaquil',
                'is_active' => true,
            ],
        );

        foreach ($moduleKeys as $key) {
            $module = Module::query()->firstOrCreate(['key' => $key], ['sort_order' => 0]);
            $company->modules()->syncWithoutDetaching([$module->getKey() => ['is_active' => true]]);
        }

        return $company;
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $users
     */
    private function createUsers(Company $company, array $users): void
    {
        foreach ($users as $role => [$name, $email]) {
            /** @var User $user */
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    // Solo para desarrollo: en producción el admin crea las cuentas.
                    'password' => 'password',
                    'locale' => 'es',
                    'is_active' => true,
                    'current_company_id' => $company->getKey(),
                    'email_verified_at' => now(),
                ],
            );

            $user->companies()->syncWithoutDetaching([$company->getKey()]);
            $user->syncRoles([$role === 'admin' ? RoleName::Admin->value : $role]);
        }
    }

    private function seedFarm(): void
    {
        foreach (self::VARIETIES as $name => $color) {
            $variety = FlowerVariety::query()->updateOrCreate(
                ['name' => $name],
                ['color' => $color, 'is_active' => true],
            );

            foreach (self::COMMON_LENGTHS as $length) {
                $this->product($variety, $length);
            }
        }

        $explorer = FlowerVariety::query()->where('name', 'Explorer')->firstOrFail();
        $mondial = FlowerVariety::query()->where('name', 'Mondial')->firstOrFail();

        // Explorer y Mondial se cortan en todos los largos; el resto en los
        // tres que más se venden.
        foreach (array_diff(self::LENGTHS, self::COMMON_LENGTHS) as $length) {
            $this->product($explorer, $length);
            $this->product($mondial, $length);
        }

        foreach (
            [
                ['QB', 'Quarter box', 0.25, 5],
                ['HB', 'Half box', 0.5, 10],
                ['FB', 'Full box', 1, 20],
            ] as [$code, $boxName, $factor, $bunches]
        ) {
            BoxType::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $boxName,
                    'full_box_factor' => $factor,
                    'default_bunches_per_box' => $bunches,
                    'is_active' => true,
                ],
            );
        }

        $panatlantic = CargoAgency::query()->updateOrCreate(
            ['name' => 'Panatlantic Logistics'],
            ['tax_id' => '1791234567001', 'contact_name' => 'Verónica Pazmiño', 'phone' => '+593 2 299 0000', 'email' => 'ops@panatlantic.com', 'cutoff_time' => '16:00', 'is_active' => true],
        );

        $aerovic = CargoAgency::query()->updateOrCreate(
            ['name' => 'Aerovic Cargo'],
            ['tax_id' => '1792146739001', 'contact_name' => 'Luis Vega', 'phone' => '+593 2 234 5678', 'email' => 'carga@aerovic.com', 'cutoff_time' => '14:30', 'is_active' => true],
        );

        $customers = [
            ['Miami Roses', 'United States', 'Miami', 'FOB', 30, 2_500_000, 'MR', $panatlantic],
            ['Moscow Flowers', 'Russia', 'Moscow', 'CIF', 45, 4_000_000, 'MF', $aerovic],
            ['Amsterdam Blooms', 'Netherlands', 'Aalsmeer', 'FOB', 15, 1_200_000, 'AB', $panatlantic],
            ['Toronto Petals', 'Canada', 'Toronto', 'FOB', 0, 0, 'TP', $aerovic],
        ];

        foreach ($customers as [$name, $country, $city, $incoterm, $creditDays, $creditLimit, $markCode, $agency]) {
            /** @var Customer $customer */
            $customer = Customer::query()->updateOrCreate(
                ['name' => $name],
                [
                    'country' => $country,
                    'city' => $city,
                    'locale' => 'en',
                    'invoice_email' => 'invoices@'.str_replace(' ', '', mb_strtolower($name)).'.com',
                    'phone' => '+1 305 555 '.random_int(1000, 9999),
                    'incoterm' => $incoterm,
                    'credit_days' => $creditDays,
                    'credit_limit_cents' => $creditLimit,
                    'is_active' => true,
                ],
            );

            CustomerContact::query()->updateOrCreate(
                ['customer_id' => $customer->getKey(), 'name' => 'Buyer '.$markCode],
                ['position' => 'Buyer', 'email' => 'buyer@'.str_replace(' ', '', mb_strtolower($name)).'.com', 'receives_invoices' => true, 'receives_statements' => true],
            );

            Mark::query()->updateOrCreate(
                ['customer_id' => $customer->getKey(), 'code' => $markCode.'-001'],
                [
                    'cargo_agency_id' => $agency->getKey(),
                    'consignee' => $name.' LLC',
                    'destination_country' => $country,
                    'destination_city' => $city,
                    'is_active' => true,
                ],
            );
        }

        $this->seedPrices();
    }

    private function seedPrices(): void
    {
        $from = now()->startOfYear()->toDateString();

        foreach (Product::query()->get() as $product) {
            // El precio base sube con el largo del tallo.
            $base = 0.18 + ($product->stem_length_cm / 100) * 0.35;

            ProductPrice::query()->updateOrCreate(
                [
                    'product_id' => $product->getKey(),
                    'customer_id' => null,
                    'season' => null,
                    'valid_from' => $from,
                ],
                ['price_per_stem' => round($base, 4), 'is_active' => true],
            );

            if (in_array($product->stem_length_cm, [60, 70], true)) {
                ProductPrice::query()->updateOrCreate(
                    [
                        'product_id' => $product->getKey(),
                        'customer_id' => null,
                        'season' => Season::Valentines->value,
                        'valid_from' => $from,
                    ],
                    ['price_per_stem' => round($base * 1.6, 4), 'is_active' => true, 'notes' => 'Temporada de San Valentín'],
                );
            }
        }

        $miami = Customer::query()->where('name', 'Miami Roses')->first();
        $explorer60 = Product::query()
            ->whereHas('flowerVariety', fn ($query) => $query->where('name', 'Explorer'))
            ->where('stem_length_cm', 60)
            ->first();

        if ($miami !== null && $explorer60 !== null) {
            ProductPrice::query()->updateOrCreate(
                [
                    'product_id' => $explorer60->getKey(),
                    'customer_id' => $miami->getKey(),
                    'season' => null,
                    'valid_from' => $from,
                ],
                ['price_per_stem' => 0.4200, 'is_active' => true, 'notes' => 'Precio negociado con el cliente'],
            );
        }
    }

    private function seedDemoFarm(): void
    {
        $variety = FlowerVariety::query()->updateOrCreate(
            ['name' => 'Freedom'],
            ['color' => 'Rojo', 'is_active' => true],
        );

        $this->product($variety, 60);

        Customer::query()->updateOrCreate(
            ['name' => 'Cliente de la Finca Demo'],
            [
                'country' => 'Spain',
                'city' => 'Madrid',
                'locale' => 'es',
                'incoterm' => Incoterm::Fob->value,
                'credit_days' => 15,
                'credit_limit_cents' => 500_000,
                'is_active' => true,
            ],
        );
    }

    private function product(FlowerVariety $variety, int $length): Product
    {
        /** @var Product $product */
        $product = Product::query()->updateOrCreate(
            [
                'flower_variety_id' => $variety->getKey(),
                'stem_length_cm' => $length,
                'stems_per_bunch' => 25,
            ],
            ['is_active' => true],
        );

        return $product;
    }
}
