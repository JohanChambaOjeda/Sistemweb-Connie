# Prompt 2 — Pedidos, standing orders y pre-ventas

> Cómo usarlo: abre una sesión NUEVA de Claude Code en la carpeta del proyecto (que el
> contexto empiece limpio), actívale el modo plan (Shift+Tab) y pega todo lo que está
> debajo de la línea. Antes, asegúrate de que la rama del prompt 1 esté en git y con las
> cuatro verificaciones en verde.

---

Lee `CLAUDE.md` completo y luego `README.md`. El prompt 1 ya está hecho: base del sistema
(multi-empresa, módulos por plan, roles, bitácora, layout bilingüe), datos maestros
(clientes, contactos, agencias de carga, marcaciones, variedades, productos, tipos de caja,
listas de precios con `ResolvePrice`) e importación genérica desde Excel. En esta sesión
construimos el módulo de **pedidos**. No escribas código hasta que yo apruebe tu plan.

## Paso 0 — Revisa lo que hay

Antes de planificar, revisa el código existente y corre las cuatro verificaciones:

```bash
php artisan test && ./vendor/bin/pint --test && ./vendor/bin/phpstan analyse && npm run build
```

Dime en pocas líneas:

- Qué falla, si algo falla (y arréglalo antes de seguir, o avísame si no es trivial).
- Qué convenciones encontraste y vas a seguir, nombrándolas: dónde van las Actions, cómo
  se arman los controladores, qué componentes de tabla y formulario se reutilizan, cómo se
  escriben las pruebas.

## Lo que ya existe y tienes que reutilizar

No inventes mecanismos nuevos para cosas que ya están resueltas:

**Multi-empresa**
- Trait `App\Models\Concerns\BelongsToCompany`: global scope por empresa activa y relleno
  automático de `company_id` al crear. Toda tabla nueva de negocio lo usa.
- `App\Support\CompanyContext`: `id()`, `set()`, `runFor($company, $callback)`.
- `Modelo::acrossCompanies()` solo para mantenimiento, nunca desde un controlador.

**Módulos y permisos**
- `App\Enums\ModuleKey` y middleware `module:<clave>`. Pedidos, standing orders y
  pre-ventas van en el módulo `sales`. El catálogo (variedades, productos, tipos de caja)
  está en `postharvest`.
- `App\Support\PermissionCatalog` es la única fuente de permisos: `RESOURCES` (con las
  acciones view/create/update/delete), `EXTRA` para los que no siguen ese patrón, y
  `forRole()` para repartirlos. `RoleSeeder` los siembra. Agregar un permiso es tocar esa
  clase, nunca escribir el nombre suelto en un controlador.
- `App\Policies\ResourcePolicy` traduce cada acción a su permiso y comprueba la empresa;
  las policies de cada modelo solo declaran su `resource()`.

**Dominio y dinero**
- `App\Actions\ProductPrices\ResolvePrice`:
  `handle(Product $product, ?Customer $customer = null, ?Carbon $date = null): ?ProductPrice`
  y `priceFor(...): ?string`. Prioridad cliente+temporada > cliente > temporada > base, con
  desempate por `valid_from` más reciente.
- Dinero en centavos (`bigInteger`), precio por tallo `decimal(10,4)`.
- `box_types` ya trae `full_box_factor` (`decimal(5,3)`: QB 0.25, HB 0.5, FB 1) y
  `default_bunches_per_box`.
- `App\Enums\Season` (`valentines`, `mothers_day`) con rangos en `config/floriculture.php`.
  `products` es variedad + largo + tallos por bonche, y `Product::label` da
  "Explorer 60 cm x25".
- Fechas que viajan al front se castean con formato explícito (`'date:Y-m-d'`), si no
  llegan como marca de tiempo completa.

**Pantallas**
- `App\Support\TableQuery::paginate()` y `::state()` resuelven búsqueda, orden y paginación
  en el servidor con columnas en lista blanca.
- Componentes React: `DataTable` (con su tipo `Column`), `Field`, `PageHeader`,
  `FlashMessages`, `DeleteRecordButton`; tipos `Paginated<T>`, `TableState`, `SharedData`.
- Textos: `lang/es/<grupo>.php` y `lang/en/<grupo>.php`, que viajan por Inertia y se leen
  con `useTranslations()` → `t('grupo.clave')`. Nunca un string suelto en un componente.
- Permisos y módulos en el front: `useAuthorization()` → `can()`, `hasModule()`.
- Las páginas viven en `resources/js/pages` (minúscula), ya configurado en
  `config/inertia.php`.

**Pruebas**
- Pest con helpers propios en `tests/Pest.php`: `actingAsRole($rol, $empresa = null)`,
  `companyWithModules([...])` y `assertCompanyScoped(Modelo::class)`.
- Corren sobre SQLite en memoria: las migraciones deben servir igual en MySQL 8 (nada de
  SQL crudo específico de un motor).

**Producción**
- Hostinger compartido: colas con driver `database` y `schedule:run` por cron. Nada de
  procesos persistentes, así que el envío de correos y cualquier trabajo pesado va a la
  cola y se procesa desde el programador de tareas.

## Decisiones que quiero resolver en el plan, antes de codear

1. **Temporadas**: hoy la temporada es un enum (`App\Enums\Season`) con rangos en
   `config/floriculture.php`, y `product_prices.season` guarda esa clave. El alcance de
   abajo pide una tabla `seasons` por empresa. Propónme cómo unificarlo sin romper
   `ResolvePrice` ni los precios ya cargados, y qué pasa con los importadores de precios.
2. **Capacidad de caja**: `box_types.default_bunches_per_box` ya existe. Dime cómo encaja
   con la tabla `box_capacities` por tipo de caja y largo, y cuál manda.
3. **Precios en los props de Inertia**: `postharvest_lead` no debe recibirlos. Propón dónde
   se recorta (recurso, policy, una capa de presentación) para que no se escape en ninguna
   pantalla ni en el PDF.
4. **Correlativo del pedido**: cómo lo generas sin huecos ni choques bajo concurrencia en
   MySQL, y si reinicia por año.

## Alcance

### 1. Pedidos

- Cabecera:
  - Número correlativo por empresa.
  - Tipo (`open_market`, `standing`, `preorder`) y temporada opcional.
  - Cliente, marcación, agencia de carga, fecha de vuelo y términos (FOB/CIF).
  - Notas para el cliente y notas internas.
  - Vendedor (usuario).
- Tablas:
  - `order_boxes`: tipo de caja, cantidad y orden.
  - `order_box_contents`: producto, bonches por caja, tallos por bonche y precio por tallo.
  - Guardar también `price_source`: `base`, `customer`, `season` o `manual`.
- Cómo se arma el pedido:
  - Una línea de caja dice tipo de caja (QB, HB, FB) y cuántas cajas iguales van.
  - Caja sólida: una sola variedad y largo. Caja mixta: varias.
  - Tallos de la línea = cajas × Σ(bonches × tallos por bonche).
  - Full boxes = cajas × factor del tipo de caja.
  - Total = Σ(tallos × precio por tallo).
- Precio:
  - Se propone con `ResolvePrice` y se guarda junto con su `price_source`.
  - Cambiarlo a mano exige el permiso `orders.edit_price`.
  - Si queda por debajo del precio base, pedir motivo y dejarlo en la bitácora.
- Validaciones:
  - La suma de bonches de una caja no puede exceder la capacidad del tipo de caja.
  - La marcación debe pertenecer al cliente.
  - No se pueden poner fechas de vuelo pasadas, salvo con permiso de admin.
- Máquina de estados:
  - `draft → confirmed → packing → shipped → invoiced`, más `cancelled`.
  - Cada transición en su Action, con pruebas de la válida y de la inválida.
  - En esta sesión solo se usan `draft`, `confirmed` y `cancelled`; el resto lo moverán los
    módulos siguientes, pero la máquina queda completa.
  - Cancelar exige motivo.
  - Un pedido confirmado solo se edita con `orders.edit_confirmed`, y cada cambio queda en
    la bitácora.
- Control de crédito al confirmar:
  - Interfaz `CustomerExposure` con una implementación inicial: suma de pedidos confirmados
    aún no facturados. Cuando exista facturación se le sumarán las facturas pendientes.
  - Si la exposición más el pedido supera el límite de crédito del cliente
    (`credit_limit_cents`), bloquear la confirmación.
  - Con `orders.override_credit` se puede forzar, con motivo.
- Disponibilidad:
  - Todavía no existe la poscosecha. Crea la interfaz `AvailabilityQuery` (disponible por
    producto y fecha) con una implementación nula que devuelva "sin dato".
  - La pantalla muestra la columna igual, para que en el prompt de poscosecha solo se
    cambie la implementación.
- Acciones extra:
  - Duplicar pedido con otra fecha de vuelo.
  - Copiar el último pedido del cliente.
- PDF de confirmación de pedido (proforma) con dompdf:
  - En el idioma del cliente (`customers.locale`), no en el del usuario.
  - Logo de la empresa, marcación, agencia, fecha de vuelo, detalle por caja, tallos, full
    boxes y total.
  - Botón para enviarlo por correo a los contactos del cliente marcados con
    `receives_invoices`, usando la cola.

### 2. Standing orders

- Plantilla con:
  - Cliente, marcación y agencia.
  - Días de vuelo de la semana.
  - Frecuencia: semanal, cada dos semanas o mensual.
  - Vigencia desde/hasta.
  - Estado activa o pausada.
  - Las mismas cajas y contenidos que un pedido.
- Comando programado diario `orders:generate-standing`:
  - Crea los pedidos en `draft` con N días de anticipación (configurable por empresa, 7 por
    defecto).
  - Precio resuelto a la fecha de vuelo, no a la fecha de generación.
  - Idempotente: correrlo dos veces no duplica pedidos, y hay una prueba que lo demuestra.
  - Queda registrado en `routes/console.php` o donde corresponda, y funciona con
    `schedule:run` por cron.
- Excepciones:
  - Saltar una fecha concreta (por ejemplo un feriado).
  - Cambiar cantidades solo para una semana sin tocar la plantilla.
- Desde un pedido generado se ve de qué plantilla vino.

### 3. Pre-ventas de temporada

- Temporadas por empresa: nombre, fechas de vuelo desde/hasta y su precio de temporada
  (los precios ya existen; solo enlázalos), según lo que acordemos en la decisión 1.
- Pedido tipo `preorder` asociado a una temporada.
- Reporte "Comprometido por temporada": tallos y full boxes por variedad y largo, y por
  cliente, con exportación a Excel (maatwebsite/excel ya está instalado). Después lo
  compararemos contra la proyección de producción.

### 4. Pantallas

- **Lista de pedidos**: filtros por estado, tipo, cliente y rango de fechas de vuelo, con
  los totales del filtro (pedidos, cajas, full boxes, tallos y USD).
- **Semana de vuelos**: vista por días (lunes a domingo) con los pedidos de cada fecha,
  agrupados por agencia, navegable por semanas. Es la pantalla que más usará ventas.
- **Formulario de pedido**: rápido de llenar con teclado; agregar caja, agregar contenido y
  duplicar caja; totales en vivo (tallos, full boxes, USD); precio sugerido visible junto al
  precio usado; columna de disponibilidad aunque diga "sin dato".
- **Standing orders**: lista y formulario, con el calendario de las próximas fechas que se
  generarán.
- Todo en es/en y con permisos por rol: `sales` y `admin` crean y editan; `billing` solo ve;
  `postharvest_lead` solo ve los pedidos confirmados y sin precios.

### 5. Datos de ejemplo

Amplía `DemoSeeder` (sigue siendo idempotente y solo corre en local y testing) con:

- Pedidos de open market para las próximas dos semanas, de los clientes que ya existen.
- 2 standing orders: una semanal y una cada dos semanas.
- Pre-ventas de San Valentín 2027.
- Cajas sólidas y mixtas con las variedades reales de la finca.

## Pruebas obligatorias (Pest)

- Cálculo de tallos, full boxes y total en cajas sólidas y mixtas.
- Validación de capacidad de caja.
- Cada transición de estado, la válida y la inválida.
- Bloqueo por crédito y su override con permiso y motivo.
- Generación de standing orders: anticipación, frecuencia, excepciones, pausa e idempotencia.
- Precio propuesto según cliente, temporada y base, y el cambio manual con su permiso.
- Aislamiento entre empresas en pedidos y standing orders (`assertCompanyScoped`).
- `postharvest_lead` no recibe precios en los props de Inertia: verifícalo en los props con
  `assertInertia`, no solo en la vista.

## Fuera de esta sesión

Poscosecha y QR, reserva real de stock, lista de disponibilidad para WhatsApp, empaque,
despachos, documentos de exportación, facturación SRI y portal del importador. No los
adelantes: deja solo las interfaces mencionadas (`AvailabilityQuery`, `CustomerExposure`).

## Entregables

1. El reporte del paso 0.
2. Plan con migraciones, modelos, Actions, comandos, rutas, pantallas y la lista de pruebas,
   más tu respuesta a las cuatro decisiones de arriba. Espera mi aprobación.
3. Implementación en commits pequeños, en este orden: modelos y cálculos → estados y
   crédito → pantallas de pedido → semana de vuelos → PDF y correo → standing orders →
   pre-ventas y reporte.
4. Al cerrar:
   - Las cuatro verificaciones en verde.
   - Resumen de lo hecho.
   - Cómo probarlo a mano: con qué usuario entrar y qué clic dar.
   - Las dudas de negocio que te hayan surgido, para que yo las consulte con la finca.

## Dudas de negocio que ya quedaron abiertas en el prompt 1

Si tu plan depende de alguna, pregúntamela antes de codear; si puedes avanzar con un valor
por defecto, déjalo documentado:

- Capacidad real de bonches por tipo de caja y largo (QB/HB/FB × 40–90 cm).
- Si el correlativo de pedidos reinicia cada año o es continuo.
- Qué día arranca la semana de vuelos para la finca (asumo lunes).
- Si un pedido confirmado puede cambiar de fecha de vuelo sin volver a `draft`.
- Si el límite de crédito se controla en USD del pedido o incluye flete en CIF.
