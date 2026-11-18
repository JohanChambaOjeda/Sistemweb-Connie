# Prompt 1 — Base del sistema y datos maestros

> Cómo usarlo: crea la carpeta del proyecto, copia `CLAUDE.md` en la raíz, pon los Excel de SoftFlor en `docs/softflor/`, abre Claude Code en esa carpeta, actívale el modo plan (Shift+Tab) y pega todo lo que está debajo de la línea.

---

Lee `CLAUDE.md` completo antes de empezar. Vamos a construir la base del ERP florícola y los datos maestros. No escribas código hasta que yo apruebe tu plan.

## Objetivo de esta sesión
Dejar un proyecto Laravel 12 funcionando, con multi-empresa, roles, layout bilingüe y los catálogos maestros listos. Al terminar, el usuario admin debe poder entrar, ver el menú según sus módulos y cargar clientes y productos, a mano o importándolos desde los Excel de SoftFlor.

## Parte A — Base técnica
1. Proyecto Laravel 12 con el starter kit de React (Inertia + TypeScript + Tailwind + shadcn/ui), MySQL 8, Pest, Pint y Larastan.
2. Autenticación con login por correo y contraseña. Sin registro público: los usuarios los crea el admin.
3. Multi-empresa:
   - Tablas `companies` (nombre, RUC, dirección, teléfono, logo, idioma por defecto, zona horaria `America/Guayaquil`) y `company_user`.
   - Trait `BelongsToCompany` con global scope y llenado automático de `company_id` al crear.
   - Prueba Pest genérica que se pueda reutilizar en cada módulo para demostrar que la empresa A no ve datos de la empresa B.
4. Módulos por plan:
   - Tablas `modules` (clave, nombre) y `company_modules`.
   - Claves iniciales: `sales`, `postharvest`, `packing`, `billing`, `portal`, `warehouse`, `accounting`, `payroll`, `crop`, `third_party_flower`.
   - Middleware `module:<clave>` y un helper para mostrar u ocultar opciones del menú.
5. Roles con spatie/laravel-permission: admin, sales, billing, postharvest_lead y warehouse_lead, con permisos por acción (`customers.view`, `customers.create`, etc.). El rol `importer` queda solo como constante; su guard se hace en otra sesión.
6. Bitácora con spatie/laravel-activitylog en todos los modelos de negocio, y una pantalla de solo lectura "Bitácora" para el admin, con filtros por usuario, modelo y fecha.
7. Layout del back office:
   - Barra lateral con los módulos activos.
   - Selector de idioma español/inglés.
   - Nombre y logo de la empresa.
   - Modo claro y oscuro.
   - Diseño sobrio y profesional (nada de plantilla genérica).
   - Tablas con búsqueda, orden y paginación hechas como componente reutilizable.
8. Seeder demo:
   - Empresa "Cielos de América EC" con todos los módulos activos.
   - Un usuario por rol (contraseña `password` solo en el entorno local).
   - Una segunda empresa "Finca Demo" para probar el aislamiento entre empresas.

## Parte B — Datos maestros
Cada uno con CRUD completo, Form Requests, Policies, búsqueda, pruebas y textos en es/en.

1. **Clientes (importadores):**
   - Nombre, país, ciudad, idioma, correo de facturas, teléfono/WhatsApp.
   - Términos (FOB/CIF), días de crédito, límite de crédito (en centavos) y estado activo.
2. **Contactos del cliente:** nombre, cargo, correo, WhatsApp, y si recibe facturas o estados de cuenta.
3. **Agencias de carga:** nombre, RUC, contacto, teléfono y hora de corte.
4. **Marcaciones:** cliente, código de marca, consignatario, país o ciudad de destino, agencia de carga y notas. Un cliente tiene varias marcaciones.
5. **Variedades:** nombre, color, foto, tipo (estándar o garden), activa.
6. **Productos:** variedad, largo en cm y tallos por bonche; único por empresa + variedad + largo + tallos.
7. **Tipos de caja:** código (QB, HB, FB u otros), nombre, factor a full box y bonches por caja por defecto.
8. **Listas de precios:**
   - Precio base por producto con vigencia desde/hasta.
   - Precio especial por cliente.
   - Precio por temporada (campo `season`).
   - Una Action `ResolvePrice` que devuelva el precio vigente para cliente + producto + fecha, con prioridad cliente > temporada > base, y con pruebas de cada caso.

## Parte C — Importación desde SoftFlor
- En `docs/softflor/` hay archivos Excel exportados de SoftFlor. Ábrelos y muéstrame qué columnas tiene cada uno ANTES de programar el mapeo.
- Haz importadores con maatwebsite/excel para clientes, variedades y productos y precios. Cada uno debe tener:
  - Vista previa con las filas válidas y los errores antes de guardar.
  - Opción de importar solo las filas válidas.
  - Un reporte descargable de las filas rechazadas, con el motivo.
- Los importadores tienen que ser genéricos (mapeo de columnas configurable), porque otras fincas traerán Excel distintos.

## Fuera de esta sesión (no lo hagas todavía)
Pedidos, poscosecha/QR, impresión de etiquetas, facturación SRI, portal del importador, bodega de insumos y despliegue en Hostinger.

## Entregables y verificación
1. Plan por pasos para que yo lo apruebe, con lista de migraciones, modelos, Actions, rutas y pantallas.
2. Después de aprobado, implementa en commits pequeños, en este orden: base técnica → multi-empresa y módulos → roles → layout → cada maestro → importadores.
3. Al final de cada parte corre `php artisan test`, Pint, Larastan y `npm run build`, y arregla lo que falle.
4. Termina con:
   - Un resumen de lo hecho.
   - Cómo levantarlo en local.
   - Usuarios de prueba.
   - Lo que quedó pendiente o con dudas.
