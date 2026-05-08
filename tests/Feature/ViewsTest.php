<?php

namespace Tests\Feature;

use App\Models\User;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Infrastructure\Persistence\Models\Activity;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use App\Src\Infrastructure\Persistence\Models\Invoice;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba funcional de renderizado de vistas.
 * Verifica que cada vista del sistema se renderice correctamente (código 200)
 * y que contenga los elementos esperados según el contexto.
 * Cubre dashboard, microservicios, planes, actividades, prospectos,
 * cotizaciones, contratos, facturas, auditoría, administración, revisiones,
 * anexos y login.
 */
class ViewsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Prospect $prospect;

    /**
     * Configuración inicial: seed de permisos, creación de usuario admin
     * y un prospecto base para las vistas que lo requieren.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->admin = User::factory()->create(['name' => 'Admin Test']);
        $this->admin->assignRole('admin');

        $this->prospect = Prospect::create([
            'company_name' => 'View Corp',
            'contact_name' => 'View',
            'email' => 'view@test.com',
            'created_by' => $this->admin->id,
        ]);
    }

    /**
     * Verifica que la vista del dashboard se renderice mostrando
     * el nombre del sistema y enlaces a módulos principales.
     */
    public function test_dashboard_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Kontbox')
            ->assertSee('Microservicios');
    }

    /**
     * Verifica que la vista de índice de microservicios muestre
     * los registros existentes.
     */
    public function test_microservices_index_view(): void
    {
        Microservice::create(['name' => 'Test MS', 'base_cost' => 100, 'type' => 'recurring']);
        $this->actingAs($this->admin)
            ->get(route('microservices.index'))
            ->assertStatus(200)
            ->assertSee('Test MS');
    }

    /**
     * Verifica que la vista de creación de microservicios se renderice.
     */
    public function test_microservices_create_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('microservices.create'))
            ->assertStatus(200)
            ->assertSee('Costo Base');
    }

    /**
     * Verifica que la vista de edición de microservicios muestre
     * los datos del registro a editar.
     */
    public function test_microservices_edit_view(): void
    {
        $ms = Microservice::create(['name' => 'Edit MS', 'base_cost' => 150, 'type' => 'recurring']);
        $this->actingAs($this->admin)
            ->get(route('microservices.edit', $ms))
            ->assertStatus(200)
            ->assertSee('Edit MS');
    }

    /**
     * Verifica que la vista de índice de planes muestre los planes
     * con sus servicios asociados.
     */
    public function test_plans_index_view(): void
    {
        $ms = Microservice::create(['name' => 'PlanSrv', 'base_cost' => 50, 'type' => 'recurring']);
        $plan = Plan::create(['name' => 'Plan Premium']);
        $plan->services()->create(['microservice_id' => $ms->id, 'quantity' => 2]);
        $this->actingAs($this->admin)
            ->get(route('plans.index'))
            ->assertStatus(200)
            ->assertSee('Plan Premium');
    }

    /**
     * Verifica que la vista de creación de planes se renderice.
     */
    public function test_plans_create_view(): void
    {
        Microservice::create(['name' => 'NewSrv', 'base_cost' => 50, 'type' => 'recurring']);
        $this->actingAs($this->admin)
            ->get(route('plans.create'))
            ->assertStatus(200)
            ->assertSee('Nuevo Plan');
    }

    /**
     * Verifica que la vista de índice de actividades se renderice.
     */
    public function test_activities_index_view(): void
    {
        $ms = Microservice::create(['name' => 'ActSrv', 'base_cost' => 50, 'type' => 'recurring']);
        Activity::create(['name' => 'Monitoreo', 'is_active' => true]);
        $ms->update(['activity_id' => Activity::first()->id]);
        $this->actingAs($this->admin)
            ->get(route('activities.index'))
            ->assertStatus(200);
    }

    /**
     * Verifica que la vista de creación de actividades se renderice.
     */
    public function test_activities_create_view(): void
    {
        Microservice::create(['name' => 'ActSrv2', 'base_cost' => 50, 'type' => 'recurring']);
        $this->actingAs($this->admin)
            ->get(route('activities.create'))
            ->assertStatus(200)
            ->assertSee('Nombre de la actividad');
    }

    /**
     * Verifica que la vista de índice de prospectos se renderice.
     */
    public function test_prospects_index_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('prospects.index'))
            ->assertStatus(200)
            ->assertSee('Prospectos');
    }

    /**
     * Verifica que la vista de creación de prospectos se renderice.
     */
    public function test_prospects_create_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('prospects.create'))
            ->assertStatus(200);
    }

    /**
     * Verifica que la vista de índice de cotizaciones muestre
     * las cotizaciones existentes.
     */
    public function test_quotations_index_view(): void
    {
        Quotation::create([
            'quote_number' => 'COT-VIEW-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::DRAFT->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);
        $this->actingAs($this->admin)
            ->get(route('quotations.index'))
            ->assertStatus(200)
            ->assertSee('COT-VIEW-001');
    }

    /**
     * Verifica que la vista de detalle de cotización muestre
     * la información completa.
     */
    public function test_quotations_show_view(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-VIEW-002', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 200, 'tax' => 38, 'total' => 238, 'version' => 1,
        ]);
        $this->actingAs($this->admin)
            ->get(route('quotations.show', $q))
            ->assertStatus(200)
            ->assertSee('COT-VIEW-002');
    }

    /**
     * Verifica que la vista de creación de cotizaciones se renderice.
     */
    public function test_quotations_create_view(): void
    {
        $ms = Microservice::create(['name' => 'QViewSrv', 'base_cost' => 100, 'type' => 'recurring']);
        $this->actingAs($this->admin)
            ->get(route('quotations.create'))
            ->assertStatus(200)
            ->assertSee('Nueva Cotización');
    }

    /**
     * Verifica que la vista de índice de contratos muestre
     * los contratos existentes.
     */
    public function test_contracts_index_view(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-CVW-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 500, 'tax' => 95, 'total' => 595, 'version' => 1,
        ]);
        Contract::create([
            'contract_number' => 'CTR-VIEW-001', 'quotation_id' => $q->id,
            'approved_by' => $this->admin->id, 'status' => ContractStatus::ACTIVE->value,
            'total_amount' => 595,
        ]);
        $this->actingAs($this->admin)
            ->get(route('contracts.index'))
            ->assertStatus(200)
            ->assertSee('CTR-VIEW-001');
    }

    /**
     * Verifica que la vista de detalle de contrato muestre
     * la información completa del contrato.
     */
    public function test_contracts_show_view(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-CSVW-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 500, 'tax' => 95, 'total' => 595, 'version' => 1,
        ]);
        $c = Contract::create([
            'contract_number' => 'CTR-VIEW-002', 'quotation_id' => $q->id,
            'approved_by' => $this->admin->id, 'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => 595,
        ]);
        $this->actingAs($this->admin)
            ->get(route('contracts.show', $c))
            ->assertStatus(200)
            ->assertSee('CTR-VIEW-002');
    }

    /**
     * Verifica que la vista de índice de facturas muestre
     * las facturas existentes.
     */
    public function test_invoices_index_view(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-IVW-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 500, 'tax' => 95, 'total' => 595, 'version' => 1,
        ]);
        $c = Contract::create([
            'contract_number' => 'CTR-IVW-001', 'quotation_id' => $q->id,
            'approved_by' => $this->admin->id, 'status' => ContractStatus::ACTIVE->value,
            'total_amount' => 595,
        ]);
        Invoice::create([
            'invoice_number' => 'INV-VIEW-001', 'contract_id' => $c->id,
            'amount' => 595, 'issued_date' => now(), 'status' => 'issued',
            'created_by' => $this->admin->id,
        ]);
        $this->actingAs($this->admin)
            ->get(route('invoices.index'))
            ->assertStatus(200)
            ->assertSee('INV-VIEW-001');
    }

    /**
     * Verifica que la vista de auditoría se renderice correctamente.
     */
    public function test_audit_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('audit.index'))
            ->assertStatus(200);
    }

    /**
     * Verifica que la vista de administración de usuarios se renderice.
     */
    public function test_admin_users_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users'))
            ->assertStatus(200)
            ->assertSee('Admin Test');
    }

    /**
     * Verifica que la vista de administración de roles se renderice.
     */
    public function test_admin_roles_view(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.roles'))
            ->assertStatus(200);
    }

    /**
     * Verifica que el panel de revisión (gerencia comercial)
     * se renderice correctamente.
     */
    public function test_review_panel_view(): void
    {
        $manager = User::factory()->create(['name' => 'Manager']);
        $manager->assignRole('commercial_manager');
        $this->actingAs($manager)
            ->get(route('reviews.index'))
            ->assertStatus(200);
    }

    /**
     * Verifica que la vista de índice de anexos se renderice
     * mostrando los anexos existentes.
     */
    public function test_amendments_index_view(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-AMVW-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->admin->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);
        $c = Contract::create([
            'contract_number' => 'CTR-AMVW-001', 'quotation_id' => $q->id,
            'approved_by' => $this->admin->id, 'status' => ContractStatus::ACTIVE->value,
            'total_amount' => 119,
        ]);
        ContractAmendment::create([
            'contract_id' => $c->id, 'amendment_number' => 'OTR-001',
            'description' => 'Cambio en servicio', 'created_by' => $this->admin->id,
        ]);
        $this->actingAs($this->admin)
            ->get(route('amendments.index'))
            ->assertStatus(200)
            ->assertSee('OTR-001');
    }

    /**
     * Verifica que la página de inicio de sesión se renderice
     * mostrando el formulario de login.
     */
    public function test_login_view(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('Iniciar Sesión');
    }
}
