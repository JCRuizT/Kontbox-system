# Kontbox — Sistema de Gestión Operativa y Contractual

## Especificación de Requerimientos

---

## 1. Descripción General

**Kontbox** es un sistema web integral para la gestión comercial, contractual y operativa de una empresa de servicios. Proporciona trazabilidad completa de cada operación mediante auditoría detallada, control de acceso basado en roles y permisos (RBAC), y un flujo de trabajo seguro que garantiza la inmutabilidad de los documentos contractuales.

El sistema está construido con **Laravel 11**, sigue una **Arquitectura Hexagonal** (Domain-Driven Design), y cuenta con **i18n completo** (español/inglés), **122 pruebas automatizadas** y una **interfaz responsive** con Tailwind CSS v4.

---

## 2. Roles del Sistema

| Rol | Descripción |
|---|---|
| **Admin** | Acceso total al sistema. Gestiona configuración (microservicios, planes, actividades), usuarios, roles y permisos. |
| **Vendedor** | Gestión comercial: crea y edita prospectos y cotizaciones propias, las envía a aprobación, consulta contratos. |
| **Gerente Comercial** | Supervisión: revisa, aprueba o rechaza cotizaciones, crea y gestiona contratos, anexos, activaciones y anulaciones. |
| **Administrativo** | Consulta contratos y gestiona facturación. |

### 2.1 Permisos del Sistema (27 total)

| Módulo | Permisos |
|---|---|
| Admin | `admin.access` |
| Microservicios | `create`, `read`, `update`, `deactivate` |
| Planes | `create`, `read`, `update`, `deactivate` |
| Actividades | `create`, `read`, `update` |
| Prospectos | `create`, `read`, `update` |
| Cotizaciones | `create`, `read`, `update_own`, `send_for_approval`, `approve`, `reject` |
| Contratos | `create`, `read`, `upload_document`, `activate`, `anulate` |
| Anexos | `create`, `read` |
| Facturas | `create`, `read` |
| Auditoría | `read` |

---

## 3. Módulos Funcionales

### 3.1 Catálogo de Servicios

#### Microservicios
- **Descripción**: Catálogo de servicios técnicos que pueden incluirse en planes y contratos.
- **Tipo**: `recurring` (mensual) o `one_time` (único)
- **Campos**: nombre, descripción, costo base, tipo, activo/inactivo
- **CRUD completo** con baja lógica (is_active)
- **Permiso requerido**: `microservices.*`

#### Actividades
- **Descripción**: Actividades asociadas a cada microservicio (relación N:1).
- **Campos**: nombre, descripción, microservicio padre, activo/inactivo
- **CRUD completo** con baja lógica
- **Permiso requerido**: `activities.*`

#### Planes
- **Descripción**: Paquetes de microservicios con cantidades y precios personalizados.
- **Campos**: nombre, descripción, servicios (microservicio + cantidad + precio personalizado), activo/inactivo
- **CRUD completo**: al actualizar, se reemplazan todos los servicios (borra y recrea)
- **Permiso requerido**: `plans.*`

### 3.2 Gestión Comercial

#### Prospectos
- **Descripción**: Clientes potenciales en el pipeline de ventas.
- **Pipeline**: `new` → `contacted` → `negotiation` → `won` | `lost`
- **Campos**: empresa, contacto, email, teléfono, estado, notas
- **Creador**: se asigna automáticamente el usuario autenticado
- **Validación**: email único en el sistema
- **Permiso requerido**: `prospects.*`

#### Cotizaciones
- **Descripción**: Propuestas comerciales inmutables con control de versiones.
- **Ciclo de vida**: `DRAFT` → `UNDER_REVIEW` → `APPROVED` | `REJECTED`
- **Inmutabilidad**: una vez enviada a revisión, no puede modificarse
- **Versionado**: al rechazar, puede crearse una nueva versión (incrementa versión, reinicia a borrador)
- **Campos**: número único, prospecto, plan (opcional), items con snapshot del servicio, subtotal, IVA, total, validez, versión, parent_id
- **Items**: captura snapshot del nombre y descripción del microservicio al momento de crear la cotización
- **Cálculos**: subtotal = Σ(cantidad × precio_unitario), IVA = subtotal × tasa, total = subtotal + IVA
- **Permisos**:
  - `quotations.create`: crear cotizaciones
  - `quotations.read`: listar/ver cotizaciones
  - `quotations.update_own`: editar cotizaciones propias (solo en borrador)
  - `quotations.send_for_approval`: enviar a revisión
  - `quotations.approve`: aprobar (solo gerencia)
  - `quotations.reject`: rechazar con motivo obligatorio (solo gerencia)

### 3.3 Panel de Revisión
- **Descripción**: Interfaz exclusiva para gerencia comercial.
- **Muestra**: cotizaciones en estado `under_review` con info del remitente y su rol
- **Acciones**: aprobar o rechazar con motivo obligatorio (mín. 10 caracteres)
- **Permiso requerido**: `quotations.approve`

### 3.4 Gestión Contractual

#### Contratos
- **Descripción**: Documento legal que formaliza una relación comercial.
- **Ciclo de vida**: `PENDING_DOCUMENT` → `DOCUMENT_LOADED` → `ACTIVE` → `CANCELLED`
- **Creación**: solo desde cotizaciones aprobadas (valida estado y evita duplicados)
- **Seguridad PDF**: 
  - No se puede activar sin PDF firmado cargado en plataforma
  - El PDF debe existir físicamente en disco para activar
  - El PDF no puede sobrescribirse una vez cargado (solo desde PENDING_DOCUMENT)
- **Anulación**: solo contratos activos, con motivo obligatorio
- **Campos**: número único, cotización origen, aprobador, fechas (inicio, fin, activación, anulación), monto total, metadatos PDF (ruta, nombre original, tamaño, fecha de carga)
- **Validaciones críticas**:
  - La cotización debe estar en estado `approved`
  - No puede haber otro contrato para la misma cotización
  - Solo contratos activos pueden tener anexos
  - Solo contratos activos pueden anularse
- **Permisos**: `contracts.*`, incluyendo `upload_document`, `activate`, `anulate`

#### Anexos / Modificaciones (Otrosí)
- **Descripción**: Modificaciones a contratos activos con respaldo legal en PDF.
- **Regla de seguridad**: PDF firmado obligatorio para procesar cualquier modificación
- **Validación**: solo contratos en estado `ACTIVE` pueden tener anexos
- **Campos**: número único, contrato, descripción (mín. 10 caracteres), ruta PDF, servicios modificados (JSON opcional), creador
- **Permisos**: `amendments.create`, `amendments.read`

### 3.5 Facturación
- **Descripción**: Facturas representativas (sin validez fiscal electrónica / no CFDI).
- **Campos**: número único, contrato activo, monto, fecha de emisión, estado (`issued` | `paid` | `cancelled`), notas
- **Validación**: solo contratos en estado `ACTIVE` pueden facturarse
- **PDF**: descarga de representación gráfica de la factura
- **Permiso requerido**: `invoices.*`

### 3.6 Dashboard
- **Descripción**: Panel principal con resumen del sistema.
- **Indicadores**: cards con conteo de cada entidad (microservicios, planes, actividades, prospectos, cotizaciones, contratos, facturas, modificaciones)
- **Actividad reciente**: últimos 10 eventos de auditoría con descripción, fecha y usuario
- **Visibilidad por permisos**: cada card se muestra solo si el usuario tiene el permiso de lectura correspondiente

### 3.7 Auditoría y Trazabilidad
- **Descripción**: Historial inalterable de todas las operaciones del sistema.
- **Categorías**:
  - `app`: eventos del sistema (inicio/cierre de sesión)
  - `crud`: operaciones de datos (crear, actualizar, desactivar)
  - `business`: flujos de negocio (cambios de estado, aprobaciones)
  - `error`: operaciones fallidas
- **Filtros**: por categoría con paginación
- **Detalle**: modal con diff de cambios (valores anteriores → nuevos), metadatos (IP, user-agent, método HTTP, URL)
- **Campos sensibles**: redactados automáticamente (password, tokens, secret keys)
- **Permiso requerido**: `audit.read`

### 3.8 Administración de Usuarios
- **Descripción**: Gestión de usuarios del sistema y roles/permisos.
- **Características**:
  - CRUD de usuarios con asignación de rol
  - Edición de permisos por rol (interfaz visual con checkboxes agrupados por módulo)
  - Soft delete con restauración
  - Bloqueo de seguridad: no permite auto-eliminación
  - Validación: no eliminar usuarios con interacciones en el sistema
- **Permiso requerido**: `admin.access`

### 3.9 Búsqueda Dinámica
- **Descripción**: Endpoints AJAX para autocompletado en formularios.
- **Entidades**: prospectos, planes, microservicios, contratos, usuarios
- **Comportamiento**: 5 resultados iniciales (SSR), hasta 20 con término de búsqueda (AJAX con debounce de 400ms)

### 3.10 Visualización de PDF
- **Descripción**: Rutas protegidas para visualización inline de PDF firmados.
- **Documentos**: PDF de contratos y anexos
- **Auditoría**: cada visualización se registra en el log de auditoría
- **Permiso requerido**: `contracts.read` o `amendments.read`

---

## 4. Historias de Usuario

### Módulo de Catálogo

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-001 | Como administrador, quiero crear microservicios con nombre, descripción, costo base y tipo, para definir el catálogo de servicios. | 1. Formulario con todos los campos. 2. Validación de campos requeridos. 3. Costo base debe ser ≥ 0. 4. Tipo debe ser recurring o one_time. 5. Se registra en auditoría. | Alta |
| US-002 | Como administrador, quiero editar y desactivar microservicios, para mantener actualizado el catálogo. | 1. Edición con mismos campos y validaciones que creación. 2. Desactivación es baja lógica (is_active=false). 3. Cambios se registran en auditoría con diff. | Alta |
| US-003 | Como administrador, quiero crear actividades asociadas a un microservicio, para detallar los servicios. | 1. Selección de microservicio activo. 2. Validación de campos requeridos. 3. Se registra en auditoría. 4. Microservicio debe estar activo. | Alta |
| US-004 | Como administrador, quiero crear planes con microservicios, cantidades y precios personalizados, para ofrecer paquetes prediseñados. | 1. Selección de microservicios activos. 2. Cantidad mínima 1. 3. Precio personalizado opcional. 4. Al menos un servicio por plan. 5. Se registra en auditoría. | Alta |

### Módulo Comercial

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-005 | Como vendedor, quiero registrar prospectos con datos de contacto, para gestionar clientes potenciales. | 1. Formulario con empresa, contacto, email, teléfono opcional. 2. Email único en sistema. 3. Se asigna creador automáticamente. 4. Se registra en auditoría. | Alta |
| US-006 | Como vendedor, quiero crear cotizaciones seleccionando un plan o microservicios manualmente, para enviar propuestas a prospectos. | 1. Selección de prospecto (requerido). 2. Selección de plan (opcional). 3. Agregar microservicios con cantidad y precio. 4. Cálculo automático de subtotal, IVA y total. 5. Estado inicial borrador. 6. Captura snapshot del servicio. | Alta |
| US-007 | Como vendedor, quiero enviar cotizaciones a aprobación, para que la gerencia las revise. | 1. Solo desde estado borrador. 2. Cambia a "en revisión". 3. Cotización se vuelve inmutable. 4. Se registra en auditoría. | Alta |
| US-008 | Como gerente comercial, quiero revisar cotizaciones pendientes en un panel dedicado, para aprobarlas o rechazarlas. | 1. Panel exclusivo con cotizaciones en revisión. 2. Muestra remitente y su rol. 3. Paginación. 4. Se registra acceso en auditoría. | Alta |
| US-009 | Como gerente comercial, quiero aprobar cotizaciones, para autorizar la creación de contratos. | 1. Solo desde estado "en revisión". 2. Cambia a "aprobada". 3. Se registra en auditoría. | Alta |
| US-010 | Como gerente comercial, quiero rechazar cotizaciones con un motivo, para comunicar la decisión al vendedor. | 1. Solo desde estado "en revisión". 2. Motivo obligatorio (mín. 10 caracteres). 3. Cambia a "rechazada". 4. Se registra en auditoría. | Alta |
| US-011 | Como vendedor, quiero crear una nueva versión a partir de una cotización rechazada, para corregir y reenviar. | 1. Solo desde estado "rechazada". 2. Nueva versión incrementa número de versión. 3. Estado vuelve a borrador. 4. Rejection_reason se limpia. | Media |

### Módulo Contractual

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-012 | Como gerente comercial, quiero crear contratos desde cotizaciones aprobadas, para formalizar la relación comercial. | 1. Solo desde cotización aprobada. 2. No duplicar contratos por misma cotización. 3. Se replican items de cotización como servicios. 4. Estado inicial "pendiente de documento". 5. Se registra en auditoría. | Alta |
| US-013 | Como gerente comercial, quiero cargar el PDF firmado de un contrato, para cumplir el requisito legal. | 1. Solo desde estado "pendiente de documento". 2. Archivo PDF obligatorio. 3. Límite de tamaño configurable. 4. Cambia a "documento cargado". 5. Se registra en auditoría. | Alta |
| US-014 | Como gerente comercial, quiero activar un contrato, para iniciar su vigencia. | 1. Solo desde estado "documento cargado". 2. Requiere PDF firmado cargado y existente en disco. 3. Cambia a "activo". 4. Se registra fecha de activación. 5. Se registra en auditoría. | Alta |
| US-015 | Como gerente comercial, quiero anular contratos activos con un motivo, para terminar la relación contractual. | 1. Solo desde estado "activo". 2. Motivo obligatorio (mín. 10 caracteres). 3. Cambia a "cancelado". 4. Se registra fecha de cancelación. 5. Se registra en auditoría. | Alta |
| US-016 | Como gerente comercial, quiero registrar anexos (otrosí) a contratos activos, para modificar términos contractuales. | 1. Solo contratos activos. 2. Descripción obligatoria (mín. 10 caracteres). 3. PDF firmado obligatorio. 4. Servicios modificados (JSON opcional). 5. Se registra en auditoría. | Alta |
| US-017 | Como usuario autorizado, quiero visualizar el PDF firmado de contratos y anexos, para consultar documentos legales. | 1. Visualización inline en navegador. 2. Registro de visualización en auditoría. 3. Mensaje claro si PDF no está disponible. | Media |

### Módulo Financiero

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-018 | Como usuario administrativo, quiero crear facturas sobre contratos activos, para generar documentos de cobro. | 1. Solo contratos activos. 2. Monto mínimo 0.01. 3. Fecha de emisión obligatoria. 4. Se registra en auditoría. 5. Número único generado automáticamente. | Alta |
| US-019 | Como usuario administrativo, quiero descargar facturas en PDF, para enviarlas a clientes. | 1. Representación gráfica con datos del contrato. 2. Disclaimer de validez no fiscal electrónica. 3. Formato listo para impresión. | Media |

### Módulo de Administración

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-020 | Como administrador, quiero gestionar usuarios (crear, editar, eliminar, restaurar), para controlar el acceso al sistema. | 1. Asignación de rol. 2. Contraseña opcional en edición. 3. Soft delete con restauración. 4. No auto-eliminación. 5. Validación de interacciones antes de eliminar. 6. Se registra en auditoría. | Alta |
| US-021 | Como administrador, quiero gestionar permisos por rol mediante interfaz visual, para definir accesos del sistema. | 1. Permisos agrupados por módulo. 2. Selección/deselección por módulo. 3. Feedback visual de selección. 4. Cambios inmediatos. 5. Se registra en auditoría. | Alta |

### Módulo de Auditoría

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-022 | Como usuario autorizado, quiero consultar el historial completo de auditoría con filtros, para trazabilidad. | 1. Tabla con categoría, fecha, usuario, acción, entidad. 2. Filtros por categoría (app, crud, business, error). 3. Paginación. 4. Modal con detalle completo incluyendo diff de cambios y metadatos técnicos. | Alta |

### Dashboard

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-023 | Como usuario, quiero ver un dashboard con resumen de datos, para tener visibilidad rápida del estado del sistema. | 1. Cards con conteos por módulo. 2. Visibilidad según permisos. 3. Actividad reciente (últimos 10 eventos). 4. Diseño responsive. | Media |

### Internacionalización

| ID | Historia | Criterios de Aceptación | Prioridad |
|---|---|---|---|
| US-024 | Como usuario, quiero que la interfaz esté completamente en español, para usar el sistema en mi idioma nativo. | 1. Todos los textos visibles usan `__()`. 2. Sin textos hardcodeados en vistas. 3. Mensajes de error, éxito y validación traducidos. | Alta |

---

## 5. Requerimientos Funcionales Detallados

### RF-01: Autenticación y Control de Acceso
- Login con email y contraseña
- Sesión con regeneración de token
- Logout con invalidación de sesión
- Middleware de permisos en rutas (`CheckPermission`)
- Middleware de roles en rutas (`CheckRole`)
- Permisos almacenados en DB (Spatie laravel-permission)
- Sección admin protegida por permiso `admin.access`, no por rol

### RF-02: Máquina de Estados de Cotización
```
DRAFT ──sendForApproval()──→ UNDER_REVIEW ──approve()──→ APPROVED
                                │
                                └──reject()──→ REJECTED ──newVersion()──→ DRAFT (v+n)
```
- Solo DRAFT es editable
- UNDER_REVIEW permite approve/reject
- APPROVED y REJECTED son terminales
- REJECTED permite crear nueva versión

### RF-03: Máquina de Estados de Contrato
```
PENDING_DOCUMENT ──uploadDocument()──→ DOCUMENT_LOADED ──activate()──→ ACTIVE ──anulate()──→ CANCELLED
```
- Bloqueo de seguridad: activate() verifica SignedPdf existe físicamente
- uploadDocument() solo desde PENDING_DOCUMENT
- anulate() solo desde ACTIVE

### RF-04: Inmutabilidad de Cotizaciones
- Una vez en UNDER_REVIEW, los datos no pueden modificarse
- Los items guardan snapshot del nombre y descripción del microservicio
- El versionado permite corregir cotizaciones rechazadas sin perder el historial

### RF-05: Seguridad de PDF en Contratos
- El contrato no puede activarse sin que `signed_pdf_path` esté en DB
- La activación verifica que el archivo existe físicamente en disco
- El PDF no puede sobrescribirse (solo se carga desde PENDING_DOCUMENT)
- Cada visualización de PDF se registra en auditoría

### RF-06: Validaciones de Integridad
- Email único en prospectos
- Cotización aprobada requerida para crear contrato
- Una cotización no puede tener múltiples contratos
- Contrato activo requerido para facturar y crear anexos
- Solo microservicios activos pueden ser referenciados en actividades y planes

### RF-07: Auditoría
- Toda operación CRUD se registra con categoría `crud`
- Los cambios de estado se registran con categoría `business`
- Los eventos de autenticación se registran con categoría `app`
- Los errores se registran con categoría `error`
- Los campos sensibles se redactan automáticamente
- El diff de cambios muestra valores anteriores y nuevos

### RF-08: Versionado de Cotizaciones
- `version` field se incrementa en cada nueva versión
- `parent_id` vincula la nueva versión con la original
- `rejection_reason` se limpia en la nueva versión
- Los items se replican de la versión anterior

---

## 6. Requerimientos No Funcionales

| ID | Categoría | Requerimiento |
|---|---|---|
| RNF-01 | **Rendimiento** | Tiempo de respuesta < 500ms para operaciones CRUD estándar. |
| RNF-02 | **Rendimiento** | Búsquedas AJAX con debounce de 400ms para evitar llamadas excesivas. |
| RNF-03 | **Escalabilidad** | Arquitectura hexagonal que permite cambiar implementaciones de infraestructura sin afectar el dominio. |
| RNF-04 | **Seguridad** | Todas las rutas protegidas por middleware de permisos. |
| RNF-05 | **Seguridad** | Contraseñas almacenadas con bcrypt. |
| RNF-06 | **Seguridad** | Campos sensibles redactados en logs de auditoría. |
| RNF-07 | **Seguridad** | PDF firmado requerido para activación de contratos (bloqueo de seguridad). |
| RNF-08 | **Seguridad** | Validación de integridad referencial: no eliminar usuarios con interacciones. |
| RNF-09 | **Seguridad** | No auto-eliminación de usuarios. |
| RNF-10 | **Mantenibilidad** | Código en inglés siguiendo PSR-12; reglas de negocio documentadas en español en comentarios. |
| RNF-11 | **Mantenibilidad** | 122 pruebas automatizadas con 255 aserciones (unitarias, funcionales, integración). |
| RNF-12 | **Mantenibilidad** | Zero hardcoded strings en vistas — todo el texto visible usa `__()` i18n. |
| RNF-13 | **Mantenibilidad** | Principio DRY: lógica compartida en servicios de dominio (AuditService, Money, SignedPdf). |
| RNF-14 | **Usabilidad** | Interfaz responsive con Tailwind CSS v4. Tablas con overflow-x-auto para móviles. |
| RNF-15 | **Usabilidad** | Diseño consistente: sistema de diseño unificado en app.css (badges, cards, alerts, botones). |
| RNF-16 | **Usabilidad** | Selectores con búsqueda AJAX y 5 opciones iniciales. |
| RNF-17 | **Fiabilidad** | Migraciones con integridad referencial (foreign keys con constrained/cascadeOnDelete). |
| RNF-18 | **Fiabilidad** | Transacciones de base de datos garantizadas por Eloquent ORM. |
| RNF-19 | **Portabilidad** | SQLite para desarrollo, configurable a MySQL/PostgreSQL en producción vía .env. |
| RNF-20 | **Portabilidad** | Internacionalización completa español/inglés con archivos lang separados (ui.php + domain.php). |

---

## 7. Stack Tecnológico

| Componente | Tecnología |
|---|---|
| Backend | PHP 8.2.12, Laravel 11.51.0 |
| Frontend | Tailwind CSS 4.2.4, Vite 5.4.21 |
| Base de datos | SQLite (desarrollo), configurable MySQL/PostgreSQL |
| Arquitectura | Hexagonal (Domain → Application → Infrastructure) |
| Autenticación | Laravel Auth + Spatie laravel-permission ^6 |
| Auditoría | Spatie activitylog ^4 |
| PDF | Barryvdh DomPDF ^3 |
| Archivos | Spatie medialibrary ^11 (no implementado completamente) |
| Testing | PHPUnit 10.5.63 — 122 tests, 255 assertions |

---

## 8. Estructura del Proyecto

```
app/
├── Http/Controllers/Auth/         # Controlador de autenticación
├── Http/Middleware/                # CheckPermission, CheckRole
├── Models/                         # User (Eloquent)
└── Src/
    ├── Domain/
    │   ├── Contracts/              # AuditServiceInterface
    │   ├── Entities/               # Contract, Quotation (business logic)
    │   ├── Enums/                  # QuotationStatus, ContractStatus, etc.
    │   ├── Events/                 # Domain Events (ContractActivated, QuotationApproved...)
    │   ├── Exceptions/             # ContractNotFoundException, QuotationNotFoundException
    │   ├── Repositories/           # ContractRepositoryInterface, QuotationRepositoryInterface
    │   ├── Services/               # AuditService (facade estática)
    │   └── ValueObjects/           # Money, SignedPdf
    ├── Application/
    │   ├── Services/               # QuotationPricingService, ActivityInstanceService
    │   └── UseCases/               # ActivateContract, ApproveQuotation, etc.
    └── Infrastructure/
        ├── Http/Controllers/       # Web y API controllers
        ├── Listeners/              # LogContractActivated, CreateActivityInstances...
        └── Persistence/
            ├── Models/             # Eloquent models
            └── Repositories/       # Implementaciones de repositorios
database/
├── migrations/                     # 21 migraciones
└── seeders/                        # RolePermissionSeeder, AccountingSeedDataSeeder
lang/
├── es/                             # ui.php (455+ entradas), domain.php
└── en/                             # ui.php, domain.php
resources/
└── views/                          # 34+ vistas Blade
routes/
├── web.php                         # 141 líneas — rutas web con permisos
└── api.php                         # Rutas API con Sanctum
tests/                              # 122 tests en 13 archivos
```

---

## 9. Modelo de Datos

### Diagrama de Entidades

```
Microservice (1) ──── (N) Activity
Microservice (1) ──── (N) PlanService ──── (N) Plan
Microservice (1) ──── (N) QuotationItem ──── (N) Quotation
Microservice (1) ──── (N) ContractService ──── (N) Contract
Quotation (1) ──── (1) Contract (validación)
Prospect (1) ──── (N) Quotation
Contract (1) ──── (N) ContractAmendment
Contract (1) ──── (N) Invoice
User (1) ──── (N) Prospect (created_by)
User (1) ──── (N) Quotation (created_by)
User (1) ──── (N) Contract (approved_by)
```

### Convenciones de Base de Datos
- `is_active` para baja lógica (microservicios, planes, actividades)
- `softDeletes` para usuarios
- Snapshots en quotation_items (service_name_snapshot, description_snapshot)
- Timestamps en contratos para eventos clave (signed_pdf_uploaded_at, activated_at, cancelled_at)
- `parent_id` para versionado de cotizaciones
- JSON para campos flexibles (modified_services en anexos, properties en activity_log)

---

## 10. Reglas de Negocio Clave

1. **BN-001**: Una cotización aprobada genera exactamente UN contrato.
2. **BN-002**: Un contrato no puede activarse sin PDF firmado cargado y verificado en disco.
3. **BN-003**: Una cotización en revisión es inmutable; solo aprobación o rechazo.
4. **BN-004**: Solo el gerente comercial puede aprobar/rechazar cotizaciones.
5. **BN-005**: El vendedor puede crear y editar cotizaciones propias, pero no aprobarlas.
6. **BN-006**: Un contrato activo puede tener múltiples anexos, pero no al revés.
7. **BN-007**: Solo contratos activos generan facturas.
8. **BN-008**: Los microservicios desactivados no pueden incluirse en nuevas actividades ni planes.
9. **BN-009**: Email único por prospecto.
10. **BN-010**: Los cambios en permisos de roles afectan inmediatamente a los usuarios.
11. **BN-011**: El PDF firmado de un contrato no puede sobrescribirse.
12. **BN-012**: Una cotización rechazada puede versionarse; una aprobada no.

---

## 11. Glosario

| Término | Definición |
|---|---|
| **Microservicio** | Servicio técnico unitario del catálogo (ej: Facturación Electrónica). |
| **Plan** | Paquete de microservicios con cantidades y precios para oferta comercial. |
| **Actividad** | Tarea o proceso asociado a un microservicio (ej: Liquidación de nómina). |
| **Prospecto** | Cliente potencial en seguimiento comercial. |
| **Cotización** | Propuesta económica formal a un prospecto. |
| **Contrato** | Acuerdo legal formalizado a partir de una cotización aprobada. |
| **Anexo / Otrosí** | Modificación a un contrato activo con respaldo PDF. |
| **Otrosí** | Término jurídico para modificación contractual (sinónimo de anexo). |
| **Factura** | Documento de cobro representativo (sin validez fiscal electrónica). |
| **SignedPdf** | Value Object que encapsula un PDF firmado con ruta, nombre y tamaño. |
| **Snapshot** | Captura del estado de un microservicio al momento de crear una cotización. |
| **Inmutabilidad** | Propiedad de las cotizaciones en revisión: no pueden modificarse. |
| **Bloqueo de Seguridad** | Restricción que impide activar un contrato sin PDF firmado. |
| **Domain Event** | Evento de dominio que notifica cambios de estado (ContractActivated, QuotationApproved). |
| **Use Case** | Caso de uso orquestador que coordina entidades, repositorios y eventos para una operación. |
| **Repository** | Interfaz en Domain, implementación en Infrastructure, desacopla persistencia del negocio. |

---

## 12. Plan de Corrección Arquitectónica (SOLID + Hexagonal)

### 12.1 Estado Actual Post-Corrección

| Principio/Violación | Estado |
|---|---|
| **SRP** — Lógica de negocio en controllers | ✅ Corregido: `QuotationPricingService`, `ActivityInstanceService` |
| **DIP** — Domain con dependencias de framework | ✅ Corregido: AuditService en Infrastructure, SignedPdf sin Storage |
| **DIP** — `__()` en Domain Entities | ✅ Corregido: usa translation keys, controllers traducen |
| **OCP** — Tax rate hardcodeado en API | ✅ Corregido: usa `config()` |
| **DRY** — Lógica duplicada Web/API | ✅ Corregido: servicios compartidos |
| **Auditoría** — En controllers en vez de use cases | ✅ Corregido: 6 use cases con `AuditServiceInterface` |
| **Excepciones** — Genéricas sin tipo | ✅ Corregido: `ContractNotFoundException`, `QuotationNotFoundException` |
| **SignedPdf** — Value Object con I/O | ✅ Corregido: ValueObject puro sin dependencias |

### 12.2 Pendientes — Fase 2

#### Fase 2.1: Domain Events (V20)

| Evento | Disparado por | Listeners |
|---|---|---|
| `ContractActivated` | `ActivateContractUseCase` | `LogContractActivated`, `CreateActivityInstances` |
| `QuotationApproved` | `ApproveQuotationUseCase` | `LogQuotationStatusChanged` |
| `QuotationRejected` | `RejectQuotationUseCase` | `LogQuotationStatusChanged` |

**Impacto**: Elimina la lógica de post-procesamiento de `ContractController::activate()` y carga la creación de ActivityInstances en un listener dedicado.

#### Fase 2.2: Entidades de Dominio + Repositorios (V5, V6, V8, V9, V18)

Para cada concepto del negocio que actualmente solo existe como modelo Eloquent:

| Concepto | Entidad Domain | Repository Interface | Impl. Infrastructure | Use Cases |
|---|---|---|---|---|
| **Microservice** | `Domain/Entities/Microservice.php` | `MicroserviceRepositoryInterface` | `EloquentMicroserviceRepository` | `CreateMicroservice`, `UpdateMicroservice`, `ToggleActive` |
| **Activity** | `Domain/Entities/Activity.php` | `ActivityRepositoryInterface` | `EloquentActivityRepository` | `CreateActivity`, `UpdateActivity`, `ToggleActive` |
| **Plan** | `Domain/Entities/Plan.php` | `PlanRepositoryInterface` | `EloquentPlanRepository` | `CreatePlan`, `UpdatePlan`, `ToggleActive`, `ToggleActivity` |
| **Prospect** | `Domain/Entities/Prospect.php` | `ProspectRepositoryInterface` | `EloquentProspectRepository` | `CreateProspect`, `UpdateProspect` |
| **Invoice** | `Domain/Entities/Invoice.php` | `InvoiceRepositoryInterface` | `EloquentInvoiceRepository` | `CreateInvoice`, `DownloadPdf` |
| **Amendment** | `Domain/Entities/ContractAmendment.php` | `AmendmentRepositoryInterface` | `EloquentAmendmentRepository` | `CreateAmendment` |

**Patrón de implementación** (seguir el ejemplo de Contract/Quotation):
1. Entidad con constructor posicional, getters, y métodos de negocio
2. Interfaz de repositorio con `findById()` y `save()`
3. Implementación Eloquent con mapeo `toEntity()` y persistencia condicional
4. Use Case con inyección de `AuditServiceInterface` + repositorio
5. Controller simplificado que solo valida y llama al use case

#### Fase 2.3: Refactor de Controllers

Controllers actuales que serán refactorizados para usar repositorios y use cases:

| Controller | Método | Reemplazo |
|---|---|---|
| `PlanController::store()` | Creación directa Eloquent | `CreatePlanUseCase` |
| `PlanController::update()` | Creación directa Eloquent | `UpdatePlanUseCase` |
| `MicroserviceController::store()` | Creación directa Eloquent | `CreateMicroserviceUseCase` |
| `ActivityController::store()` | Creación directa Eloquent | `CreateActivityUseCase` |
| `QuotationController::store()` | Lógica de ~100 líneas | `CreateQuotationUseCase` |
| `ContractController::store()` | Creación directa Eloquent | `CreateContractUseCase` |

### 12.3 Orden de Implementación

| Paso | Módulo | Días | Dependencias |
|---|---|---|---|
| 1 | Domain Events | 1 | Ninguna |
| 2 | Repositorios: Microservice, Activity, Plan | 2 | Domain Events |
| 3 | Use Cases: Microservice, Activity, Plan | 1.5 | Paso 2 |
| 4 | Repositorios: Prospect, Invoice, Amendment | 1.5 | Domain Events |
| 5 | Use Cases + Refactor controllers | 2 | Pasos 3-4 |
| 6 | Domain Events wiring + cleanup | 0.5 | Pasos 1-5 |

**Total estimado**: ~8.5 días

### 12.4 Riesgos y Mitigaciones

| Riesgo | Probabilidad | Mitigación |
|---|---|---|
| Romper funcionalidad existente | Media | Ejecutar 120 tests después de cada cambio |
| Mapeo complejo Eloquent → Entity | Baja | Seguir patrón exacto de `EloquentContractRepository::toEntity()` |
| Perder lazy loading de Eloquent | Media | Los repositorios deben eager-load relaciones necesarias |
| Controllers muy acoplados a Eloquent | Alta | Refactor incremental: use case → repositorio → migrar controller |
