# Grizzi Flor ERP — finca piloto: Cielos de América EC

ERP florícola web de Grizzi Technology. Se construye sobre la operación real de Cielos de América EC (finca de rosas en Bolívar, Ecuador: 8 variedades, 50 personas, poscosecha propia, exportación aérea desde Quito/UIO) y luego se vende a otras fincas por planes de módulos. Reemplaza a SoftFlor.

Desarrollador: Johan Chamba (solo, con Claude Code). Explica tus decisiones en español.

## Stack (no cambiar sin preguntar)
- Laravel 12, PHP 8.3, MySQL 8
- Inertia + React + TypeScript + Tailwind + shadcn/ui
- Pest para pruebas, Laravel Pint para formato, Larastan nivel 6
- spatie/laravel-permission (roles y permisos), spatie/laravel-activitylog (bitácora)
- maatwebsite/excel (importar/exportar), barryvdh/laravel-dompdf (PDF)
- Colas con driver `database`; tareas con `schedule:run` por cron. Sin Redis, sin Supervisor, sin Node en el servidor.
- Producción: Hostinger Business (hosting compartido). Todo debe funcionar ahí: assets compilados en local o CI, nada de procesos persistentes.

## Arquitectura
- Monolito Laravel con tres frentes: back office (escritorio), pantallas operativas (poscosecha/bodega, táctiles y con lector) y portal del importador (guard propio, solo ve sus datos).
- Lógica de negocio en clases Action (`app/Actions/<Modulo>/`), una acción por caso de uso. Controladores delgados.
- Validación con Form Requests. Autorización con Policies + permisos de spatie.
- Código, tablas y columnas en inglés. Textos de interfaz en español e inglés con archivos lang (`lang/es`, `lang/en`); nunca strings sueltos en componentes.

## Multi-finca (multi-empresa) — regla dura
- Toda tabla de negocio lleva `company_id` (FK a `companies`) y usa el trait `BelongsToCompany` con un global scope que filtra por la empresa del usuario autenticado.
- Ninguna consulta cruza empresas. Cada módulo nuevo incluye una prueba que demuestra que la empresa A no ve datos de la empresa B.
- Los módulos se activan por empresa (`company_modules`) según su plan. Rutas y menú se protegen con middleware `module:<clave>`.

## Dominio florícola
- Cliente = importador. Marcación (mark) = código de marca del cliente con su agencia de carga y consignatario; un cliente puede tener varias.
- Producto = variedad + largo (cm) + tallos por bonche (normalmente 25).
- Cajas: QB = 0.25 full box, HB = 0.5, FB = 1. Se guarda el factor por tipo de caja.
- Ramo/bonche = unidad física con etiqueta QR propia (trazabilidad).
- Precio por tallo en USD, `decimal(10,4)`. Montos de dinero en centavos (`bigInteger`).
- Estados de pedido: draft, confirmed, packing, shipped, invoiced, cancelled.
- Temporadas pico: San Valentín (ene–feb) y Día de la Madre (abr–may).

## Reglas que nunca se rompen
- Nunca borrar facturas, pagos ni movimientos de inventario: se anulan o se ajustan con motivo, usuario y fecha.
- Movimientos de bodega de insumos: solo inserción. Sin stock negativo. Salidas solo contra requisición aprobada.
- Toda tabla con datos de negocio registra auditoría (activitylog).
- Hardware sin marca fija: lectores en modo teclado (campo con foco + Enter); impresoras por perfiles (ZPL, TSPL, DPL, PDF) detrás de una interfaz `LabelPrinter`.
- Facturación electrónica SRI detrás de una interfaz propia (`ElectronicInvoicingGateway`) para poder cambiar de proveedor.

## Roles
admin, sales, billing, postharvest_lead, warehouse_lead, importer (este último en el guard del portal).

## Forma de trabajar
1. Antes de escribir código en una tarea nueva: propone un plan y espera aprobación.
2. Cada migración nueva va con su factory, seeder y pruebas.
3. Datos de ejemplo realistas: variedades Explorer, Hot Spot, Gotcha, Sweet Spot, Be Sweet, Fatima, Vicky, Mondial; largos 40–90 cm.
4. Antes de dar algo por terminado deben pasar: `php artisan test`, `./vendor/bin/pint --test`, `./vendor/bin/phpstan analyse` y `npm run build`.
5. Commits pequeños con mensajes en español, uno por paso lógico.
6. Si algo de este archivo choca con lo que te piden, pregunta antes de seguir.

## Documentos de referencia
- `/docs/plan.md`: plan del sistema (exportado del documento de planificación).
- `/docs/formatos/`: packing list, factura comercial, etiquetas y estado de cuenta reales de la finca.
- `/docs/softflor/`: exportaciones en Excel de SoftFlor para la migración.
