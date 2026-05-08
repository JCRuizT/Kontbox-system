# Arquitectura de Kontbox

## Stack Tecnológico

| Componente | Tecnología |
|---|---|
| Backend | PHP 8.2+ / Laravel 11 |
| Frontend | Tailwind CSS 4 + Blade |
| BD | SQLite (dev), MySQL/PostgreSQL (prod) |
| Arquitectura | Hexagonal (DDD: Domain → Application → Infrastructure) |
| Auditoría | Spatie laravel-activitylog |
| Auth | Laravel Auth + Spatie laravel-permission |
| Testing | PHPUnit 10.5 (120+ tests) |

---

## 1. Estructura del Proyecto (`app/Src/`)

```
Domain/                          # Capa de dominio (CERO dependencias externas)
├── Contracts/                   # Interfaces de servicios de infraestructura
│   └── AuditServiceInterface.php
├── Entities/                    # Entidades con lógica de negocio
│   ├── Contract.php
│   └── Quotation.php            # (y Microservice, Activity, Plan, etc.)
├── Enums/                       # Enums con métodos de validación (state machine)
├── Events/                      # Domain Events
├── Exceptions/                  # Excepciones de dominio personalizadas
├── Repositories/                # Interfaces de repositorios (ports)
├── Services/                    # AuditService (facade estática, NO implementación)
└── ValueObjects/                # Value Objects inmutables (datos sin I/O)

Application/                     # Capa de aplicación (orquestación)
├── Services/                    # Servicios de aplicación compartidos
│   ├── QuotationPricingService.php
│   └── ActivityInstanceService.php
└── UseCases/                    # Casos de uso (uno por operación de negocio)
    ├── Contracts/
    └── Quotations/

Infrastructure/                  # Implementaciones concretas
├── Http/Controllers/            # Web y API controllers
│   ├── Web/
│   └── Api/
├── Listeners/                   # Escuchadores de Domain Events
├── Persistence/
│   ├── Models/                  # Eloquent models (SOLO infraestructura)
│   └── Repositories/            # Implementaciones Eloquent de repositorios
└── Services/                    # Implementaciones de servicios
    └── SpatieAuditService.php
```

---

## 2. Reglas de Dependencia (¡CRÍTICAS!)

```
Domain  →  (ninguna dependencia externa)
   ↓
Application  →  Domain
   ↓
Infrastructure  →  Domain + Application
```

### ✅ Permitido

| Origen | Destino | Ejemplo |
|--------|---------|---------|
| Application | Domain | `use App\Src\Domain\Entities\Contract;` |
| Infrastructure | Application | `use App\Src\Application\UseCases\...;` |
| Infrastructure | Domain | `use App\Src\Domain\Entities\Contract;` |
| Web/API Controllers | Infrastructure | `use App\Src\Infrastructure\...;` |

### ❌ PROHIBIDO

| Origen | Destino | Por qué |
|--------|---------|---------|
| **Domain** | Laravel | NO usar `__()`, `request()`, `auth()`, `Storage`, `Config`, `DB` |
| **Domain** | Infrastructure | NO importar nada de `app\Src\Infrastructure\...` |
| **Domain** | Application | NO importar use cases desde entidades |
| **Application** | Infrastructure | NO usar Eloquent, requests, respuestas HTTP |
| **Application** | Laravel helpers | NO usar `__()`, `request()`, `auth()` |
| **Controllers** | Domain entities | Solo si es para crear/leer datos, no para lógica |

---

## 3. Capa de Dominio (`Domain/`)

### 3.1 Entidades (`Entities/`)

Las entidades contienen **lógica de negocio y reglas**. NO tienen getters/setters anémicos.

```php
// ✅ Bien: entidad con comportamiento
class Contract {
    public function __construct(
        private ?int $id,
        private string $contractNumber,
        private ContractStatus $status,
        // ...
    ) {}

    public function activate(): void {
        if (!$this->status->canActivate()) {
            throw new \DomainException('domain.contract.cannot_activate_without_pdf');
        }
        $this->status = ContractStatus::ACTIVE;
    }
}

// ❌ Mal: entidad anémica (solo propiedades, sin métodos de negocio)
class Contract {
    public string $status; // Propiedad pública, sin encapsulamiento
}
```

**Reglas:**
- Constructor con promoted properties `private readonly` o `private`
- Getters para acceso a propiedades: `public function id(): ?int`
- Métodos de negocio con validaciones: `public function activate(): void`
- Excepciones de dominio: `throw new \DomainException('translation.key.here')`
- **NO usar** `__()`, `request()`, `auth()`, `Storage`, Eloquent, etc.

### 3.2 Value Objects (`ValueObjects/`)

Inmutables, sin identidad, sin I/O.

```php
// ✅ Bien: VO puro
class Money {
    public function __construct(
        private float $amount,
        private string $currency = 'COP',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('domain.error.negative_amount');
        }
    }
}

// ❌ Mal: VO con I/O
class SignedPdf {
    public function exists(): bool {
        return Storage::disk('local')->exists($this->path); // PROHIBIDO
    }
}
```

**Reglas:**
- Sin métodos que hagan I/O (no `Storage::disk()`, no `file_exists()`, no HTTP calls)
- Inmutables (no modificar propiedades después de constructor)
- Pueden tener lógica de validación en constructor

### 3.3 Enums (`Enums/`)

Enums con state machine y métodos de validación.

```php
enum ContractStatus: string {
    case PENDING_DOCUMENT = 'pending_document';    
    case DOCUMENT_LOADED = 'document_loaded';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';

    public function canActivate(): bool {
        return $this === self::DOCUMENT_LOADED;
    }
}
```

### 3.4 Interfaces de Contratos (`Contracts/`)

Interfaces para servicios que se implementan en Infrastructure.

```php
interface AuditServiceInterface {
    public function log(string $description, $subject = null, array $properties = [], string $logName = 'app'): void;
    // ...
}
```

### 3.5 Excepciones (`Exceptions/`)

Excepciones de dominio personalizadas que extienden `\RuntimeException`.

```php
class ContractNotFoundException extends \RuntimeException {
    public function __construct(int $id) {
        parent::__construct('domain.contract.not_found');
    }
}
```

### 3.6 Domain Events (`Events/`)

Eventos de dominio para efectos secundarios.

```php
class ContractActivated {
    public function __construct(
        public readonly int $contractId,
        public readonly string $contractNumber,
    ) {}
}
```

Los listeners se registran en `App\Providers\EventServiceProvider`.

---

## 4. Capa de Aplicación (`Application/`)

### 4.1 Use Cases

Cada caso de uso es una clase con un único método `execute()`.

```php
class ActivateContractUseCase {
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private Dispatcher $events,     // Para Domain Events
    ) {}

    public function execute(int $contractId): Contract {
        $contract = $this->contractRepository->findById($contractId);
        if (!$contract) throw new ContractNotFoundException($contractId);

        $contract->activate();
        $this->contractRepository->save($contract);

        $this->events->dispatch(new ContractActivated(
            contractId: $contractId,
            contractNumber: $contract->contractNumber(),
        ));

        return $contract;
    }
}
```

**Reglas:**
- Inyectar repositorios (interfaces), no Eloquent models
- NO usar `request()`, `auth()`, `redirect()`, `view()`
- NO usar helpers de Laravel (`__()`, `config()`, `session()`)
- Disparar Domain Events en lugar de llamar directamente a audit/logging
- La auditoría se maneja en los listeners de eventos, no en el use case

### 4.2 Servicios de Aplicación (`Services/`)

Lógica compartida entre use cases o controllers.

```php
class QuotationPricingService {
    public function calculate(array $items): array {
        $subtotal = collect($items)->sum(fn ($i) => $i['unit_price']);
        $tax = $subtotal * config('kontbox.tax_rate');
        $total = $subtotal + $tax;
        return compact('subtotal', 'tax', 'total');
    }
}
```

---

## 5. Capa de Infraestructura (`Infrastructure/`)

### 5.1 Controllers

**Patrón:**
1. Validar request
2. Llamar Use Case o repositorio (vía interfaz)
3. Retornar respuesta

```php
// ✅ Bien
public function store(Request $request): RedirectResponse {
    $validated = $request->validate([...]);
    $entity = new MicroserviceEntity(null, $validated['name'], ...);
    $this->microserviceRepository->save($entity);
    $this->auditService->logCreate($entity, 'Microservicio', $validated);
    return to_route('microservices.index')->with('success', __('domain.microservice.created'));
}

// ❌ Mal: lógica de negocio en controller
public function store(Request $request): RedirectResponse {
    // Aquí NO debe ir lógica de negocio, cálculos, etc.
}
```

**Reglas:**
- Los controllers NO contienen lógica de negocio
- Para crear entidades: usar `new Entity(...)` con datos validados
- Para operaciones complejas: llamar Use Case
- Para persistencia: usar repositorio (interfaz), no Eloquent directo
- Para lectura/listados: se permite Eloquent directo (es presentación)
- Inyectar `AuditServiceInterface` en constructor para logging

### 5.2 Repositorios Eloquent

Mapean Eloquent models → Domain Entities y viceversa.

```php
class EloquentMicroserviceRepository implements MicroserviceRepositoryInterface {
    public function findById(int $id): ?MicroserviceEntity {
        $model = MicroserviceModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function save(MicroserviceEntity $microservice): void {
        MicroserviceModel::updateOrCreate(
            ['id' => $microservice->id()],
            ['name' => $microservice->name(), /* ... */]
        );
    }

    private function toEntity(MicroserviceModel $model): MicroserviceEntity {
        return new MicroserviceEntity(
            id: $model->id,
            name: $model->name,
            // ...
        );
    }
}
```

**Reglas:**
- `findById()` retorna `?Entity` (null si no existe)
- `save()` recibe Entity, persiste con `updateOrCreate()`
- `toEntity()` mapea Eloquent → Domain (privado)
- NO poner lógica de negocio aquí

### 5.3 Listeners

Escuchan Domain Events y ejecutan side effects (auditoría, creación de datos relacionados).

```php
class CreateActivityInstances {
    public function handle(ContractActivated $event): void {
        $contract = Contract::with('services.microservice.activities')->find($event->contractId);
        // crear activity instances...
    }
}
```

### 5.4 AuditService (`Infrastructure/Services/SpatieAuditService.php`)

Implementa `AuditServiceInterface`. Usa Spatie ActivityLog internamente.

**Importante:** El método `log()` usa `performedOn($subject)` solo si `$subject` es una instancia de `Illuminate\Database\Eloquent\Model`. Los Domain Entities se pasan como null para `performedOn` (la auditoría igual registra la descripción, usuario, IP, etc.).

---

## 6. Flujo Arquitectónico Típico

```
HTTP Request
  → Route (middleware: auth + permission)
    → Controller (valida input)
      → UseCase (orquesta lógica, dispara eventos)
        → RepositoryInterface::findById() / save()
          → EloquentRepository (mapea Entity ↔ Model)
            → Database
        → Dispatcher::dispatch(new DomainEvent)
          → Listener::handle(DomainEvent)
            → AuditService::log(...)  (auditoría)
            → Otros side effects
      ← UseCase retorna Entity
    ← Controller retorna Response
  ← HTTP Response
```

---

## 7. Registro de Dependencias (Service Container)

Todas las interfaces de dominio se vinculan en `RepositoryServiceProvider`:

```php
// app/Src/Infrastructure/Providers/RepositoryServiceProvider.php
$this->app->bind(MicroserviceRepositoryInterface::class, EloquentMicroserviceRepository::class);
$this->app->bind(ActivityRepositoryInterface::class, EloquentActivityRepository::class);
$this->app->bind(PlanRepositoryInterface::class, EloquentPlanRepository::class);
$this->app->singleton(AuditServiceInterface::class, SpatieAuditService::class);
```

Los eventos se registran en `EventServiceProvider`:

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    ContractActivated::class => [
        LogContractActivated::class,
        CreateActivityInstances::class,
    ],
    QuotationApproved::class => [LogQuotationStatusChanged::class],
    QuotationRejected::class => [LogQuotationStatusChanged::class],
];
```

---

## 8. Convenciones de Código

### Formato de Código
- **Getters**: SIEMPRE multi-línea, NUNCA en una sola línea
  ```php
  // ✅ Correcto
  public function id(): ?int
  {
      return $this->id;
  }
  
  // ❌ Incorrecto
  public function id(): ?int { return $this->id; }
  ```
- **Docblocks**: Todo método público DEBE tener `/** ... */` con descripción en español
- **Idioma de comentarios**: Español. El código (variables, funciones) en inglés por convención PSR, pero los comentarios y docblocks en español
- **Application layer**: Use cases y servicios deben tener docblock describiendo la operación de negocio
- **Clases sin lógica**: Las entidades con getters simples deben igual tener docblock con `@return` y descripción en español

### Nombrado
- **Entidades**: `Domain/Entities/Contract.php`, class `Contract`
- **Value Objects**: `Domain/ValueObjects/Money.php`, class `Money`
- **Repositorios (interfaz)**: `Domain/Repositories/ContractRepositoryInterface.php`
- **Repositorios (impl)**: `Infrastructure/.../EloquentContractRepository.php`
- **Use Cases**: `Application/UseCases/Contracts/ActivateContractUseCase.php`
- **Servicios**: `Application/Services/QuotationPricingService.php`
- **Eventos**: `Domain/Events/ContractActivated.php`
- **Listeners**: `Infrastructure/Listeners/CreateActivityInstances.php`

### Traducciones
- Las entidades de dominio usan **translation keys** como strings: `'domain.contract.not_found'`
- Los controllers traducen al capturar: `catch (\DomainException $e) { return back()->with('error', __($e->getMessage())); }`
- Las vistas usan `__('domain.X.Y')` o `__('ui.X.Y')`
- Archivos de traducción: `lang/es/domain.php` y `lang/es/ui.php`

### Excepciones
- Usar `\DomainException` para reglas de negocio violadas
- Usar excepciones personalizadas (`ContractNotFoundException`) para "not found"
- El mensaje de la excepción es siempre una **translation key** (sin `__()`)

### Tests
- PHPUnit con `RefreshDatabase` para tests funcionales
- No mockear lo que no es necesario
- Tests actualizados después de cada refactor

---

## 9. Anti-Patrones a Evitar

| Anti-patrón | Ejemplo | Alternativa |
|---|---|---|
| Lógica de negocio en Controller | Calcular total en `store()` | `QuotationPricingService` |
| `__()` en Domain | `throw new \DomainException(__('...'))` | Usar translation key, traducir en controller |
| Eloquent en Domain | `use App\Models\Contract` en Entity | Repository interface |
| Static calls en Domain | `Storage::disk()` en ValueObject | Mover a Infrastructure |
| `request()`/`auth()` en Domain/Aplication | `auth()->id()` en Use Case | Pasar como parámetro |
| Auditoría en Controller | `AuditService::log()` después de use case | Domain Events + Listeners |
| Código duplicado Web/API | Dos `store()` idénticos | Use Case compartido |

---

## 10. Cómo Agregar una Nueva Funcionalidad

1. **Crear Domain Entity** (con lógica de negocio y reglas)
2. **Crear Repository Interface** en `Domain/Repositories/`
3. **Crear Eloquent Implementation** en `Infrastructure/Persistence/Repositories/`
4. **Crear Domain Event** (opcional, si tiene side effects)
5. **Crear Listener** en `Infrastructure/Listeners/`
6. **Crear Use Case** en `Application/UseCases/` (si es una operación compleja)
7. **Crear Controller** que inyecte el repositorio/use case
8. **Registrar rutas** con middleware de permisos
9. **Registrar binding** en `RepositoryServiceProvider`
10. **Registrar evento** en `EventServiceProvider` (si aplica)
11. **Agregar tests**
12. **Agregar traducciones**
