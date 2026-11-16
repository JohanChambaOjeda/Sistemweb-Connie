# Grizzi Flor ERP

ERP florícola web de Grizzi Technology, construido sobre la operación real de
**Cielos de América EC** (finca de rosas en Bolívar, Ecuador) y pensado para
venderse después a otras fincas por planes de módulos. Reemplaza a SoftFlor.

Las reglas del proyecto (stack, arquitectura, dominio y forma de trabajar) están
en [`CLAUDE.md`](CLAUDE.md).

## Qué hay construido

- **Multi-empresa**: toda tabla de negocio lleva `company_id` y el trait
  `BelongsToCompany` filtra por la empresa activa. Ninguna consulta cruza fincas.
- **Módulos por plan**: `modules` y `company_modules`, con middleware
  `module:<clave>` y un menú que solo muestra lo contratado.
- **Roles y permisos** (spatie): admin, sales, billing, postharvest_lead,
  warehouse_lead e importer (este último para el portal, en otra sesión).
- **Bitácora** (spatie/activitylog) con pantalla de solo lectura y filtros.
- **Back office bilingüe** (es/en), modo claro y oscuro, selector de empresa y
  tabla reutilizable con búsqueda, orden y paginación en el servidor.
- **Datos maestros**: clientes y sus contactos, agencias de carga, marcaciones,
  variedades, productos, tipos de caja y listas de precios con la Action
  `ResolvePrice` (cliente > temporada > base).
- **Usuarios**: el admin crea las cuentas; no hay registro público y una cuenta
  desactivada no puede entrar.
- **Importación desde Excel/CSV**: mapeo de columnas configurable, vista previa
  con los errores de cada fila, importación de solo las válidas y reporte
  descargable de las rechazadas.

## Requisitos

- PHP 8.3 con las extensiones de Laravel más `gd`, `zip` e `intl`
- Composer 2
- MySQL 8
- Node 20 o superior (solo para compilar los assets)

## Levantarlo en local

```bash
git clone <repo> && cd Sistemweb-Connie
```

```bash
composer install && npm install
```

```bash
cp .env.example .env && php artisan key:generate
```

Crea la base y ajusta `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`:

```bash
mysql -u root -e "CREATE DATABASE grizzi_flor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

```bash
php artisan migrate --seed
```

```bash
php artisan storage:link
```

```bash
composer run dev
```

`composer run dev` levanta a la vez el servidor, la cola, los logs y Vite. Si
prefieres por separado: `php artisan serve` y `npm run dev`.

## Usuarios de prueba

Los crea `DemoSeeder`, que solo corre en los entornos `local` y `testing`.
Todos usan la contraseña **`password`**.

| Rol | Correo |
| --- | --- |
| Administrador | `admin@cielosdeamerica.com` |
| Ventas | `ventas@cielosdeamerica.com` |
| Facturación | `facturacion@cielosdeamerica.com` |
| Jefe de poscosecha | `poscosecha@cielosdeamerica.com` |
| Jefe de bodega | `bodega@cielosdeamerica.com` |
| Administrador de la segunda finca | `admin@fincademo.com` |

La segunda empresa, **Finca Demo**, existe para comprobar el aislamiento entre
fincas: entra con su admin y verás que no aparece ningún dato de Cielos de
América. Además tiene solo los módulos de ventas y poscosecha, así que el menú
sale más corto.

## Controles de calidad

Los cuatro tienen que pasar antes de dar algo por terminado:

```bash
php artisan test
```

```bash
./vendor/bin/pint --test
```

```bash
./vendor/bin/phpstan analyse
```

```bash
npm run build
```

Las pruebas corren sobre SQLite en memoria (ver `phpunit.xml`), así que no
necesitan MySQL; el desarrollo y la producción sí usan MySQL 8.

## Importar datos de SoftFlor

1. Entra como administrador y abre **Importar datos**.
2. Elige qué vas a importar (clientes, variedades, productos o precios), sube el
   Excel o CSV y di en qué fila están los encabezados.
3. El sistema adivina el mapeo de columnas por el encabezado; corrige lo que
   haga falta y guarda.
4. Revisa la vista previa: cada fila con problema muestra su motivo.
5. Confirma para importar **solo las filas válidas** y descarga el reporte de
   las rechazadas.

Los importadores son genéricos, así que sirven igual para los archivos de otras
fincas. Cuando estén los Excel reales de SoftFlor en `docs/softflor/`, se pueden
guardar perfiles de mapeo ya listos para esos archivos.

## Despliegue en Hostinger (pendiente)

Producción es Hostinger Business (hosting compartido): los assets se compilan en
local o en CI y se suben, las colas usan el driver `database` y las tareas
programadas corren con `schedule:run` por cron. No hay Redis, Supervisor ni Node
en el servidor. El despliegue se arma en una sesión aparte.
