<?php

namespace Tests\Feature;

use App\Models\User;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba funcional de todas las rutas del sistema.
 * Verifica que cada ruta responda correctamente según el rol del usuario
 * y que los permisos de acceso (middleware permission) funcionen adecuadamente.
 * Cubre dashboard, microservicios, planes, actividades, prospectos,
 * cotizaciones, contratos, administración, búsqueda, facturas y logout.
 */
class RoutesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $vendor;
    private User $commercialManager;

    /**
     * Configuración inicial: seed de permisos y creación de usuarios
     * con roles de admin, vendedor y gerente comercial.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->admin->assignRole('admin');

        $this->vendor = User::factory()->create(['name' => 'Vendedor']);
        $this->vendor->assignRole('vendor');

        $this->commercialManager = User::factory()->create(['name' => 'Gerente']);
        $this->commercialManager->assignRole('commercial_manager');
    }

    // ========== DASHBOARD ==========

    /**
     * Verifica que un invitado sea redirigido al login.
     */
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Verifica que el administrador pueda acceder al dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)->get('/')->assertStatus(200);
    }

    // ========== MICROSERVICIOS ==========

    /**
     * Verifica que el administrador pueda listar y ver el formulario
     * de creación de microservicios.
     */
    public function test_microservices_list(): void
    {
        $this->actingAs($this->admin)->get('/microservices')->assertStatus(200);
        $this->actingAs($this->admin)->get('/microservices/create')->assertStatus(200);
    }

    /**
     * Verifica que el administrador pueda crear un microservicio.
     */
    public function test_microservices_store(): void
    {
        $data = ['name' => 'Test MS', 'base_cost' => 100, 'type' => 'recurring'];
        $this->actingAs($this->admin)->post('/microservices', $data)->assertStatus(302);
        $this->assertDatabaseHas('microservices', ['name' => 'Test MS']);
    }

    /**
     * Verifica que el administrador pueda actualizar un microservicio.
     */
    public function test_microservices_update(): void
    {
        $ms = Microservice::create(['name' => 'Old', 'base_cost' => 50, 'type' => 'one_time']);
        $this->actingAs($this->admin)->put("/microservices/{$ms->id}", [
            'name' => 'Updated', 'base_cost' => 100, 'type' => 'recurring',
        ])->assertStatus(302);
        $this->assertDatabaseHas('microservices', ['name' => 'Updated']);
    }

    /**
     * Verifica que un vendedor no pueda acceder a la creación de microservicios
     * (permiso denegado, código 403).
     */
    public function test_vendor_cannot_manage_microservices(): void
    {
        $this->actingAs($this->vendor)->get('/microservices/create')->assertStatus(403);
    }

    // ========== PLANES ==========

    /**
     * Verifica el CRUD de planes: listar y crear con servicios asociados.
     */
    public function test_plans_crud(): void
    {
        $ms = Microservice::create(['name' => 'Srv', 'base_cost' => 50, 'type' => 'recurring']);
        $this->actingAs($this->admin)->get('/plans')->assertStatus(200);
        $this->actingAs($this->admin)->post('/plans', [
            'name' => 'Plan Test',
            'services_data' => json_encode([
                ['microservice_id' => $ms->id, 'unit_price' => 50, 'excluded_activities' => []],
            ]),
        ])->assertStatus(302);
        $this->assertDatabaseHas('plans', ['name' => 'Plan Test']);
    }

    /**
     * Verifica que un vendedor no pueda gestionar planes.
     */
    public function test_vendor_cannot_manage_plans(): void
    {
        $this->actingAs($this->vendor)->get('/plans/create')->assertStatus(403);
    }

    // ========== ACTIVIDADES ==========

    /**
     * Verifica el CRUD de actividades: listar y crear.
     */
    public function test_activities_crud(): void
    {
        $ms = Microservice::create(['name' => 'ActSrv', 'base_cost' => 50, 'type' => 'recurring']);
        $this->actingAs($this->admin)->get('/activities')->assertStatus(200);
        $this->actingAs($this->admin)->post('/activities', [
            'name' => 'Test Act', 'microservice_id' => $ms->id,
        ])->assertStatus(302);
        $this->assertDatabaseHas('activities', ['name' => 'Test Act']);
    }

    // ========== PROSPECTOS ==========

    /**
     * Verifica el CRUD de prospectos: listar, crear y actualizar.
     */
    public function test_prospects_crud(): void
    {
        $this->actingAs($this->admin)->get('/prospects')->assertStatus(200);
        $this->actingAs($this->admin)->post('/prospects', [
            'company_name' => 'Corp', 'contact_name' => 'John', 'email' => 'john@test.com',
        ])->assertStatus(302);
        $this->assertDatabaseHas('prospects', ['company_name' => 'Corp']);

        $p = Prospect::first();
        $this->actingAs($this->vendor)->put("/prospects/{$p->id}", [
            'company_name' => 'CorpUpd', 'contact_name' => 'John', 'email' => 'john@test.com',
            'status' => 'contacted',
        ])->assertStatus(302);
        $this->assertDatabaseHas('prospects', ['company_name' => 'CorpUpd']);
    }

    // ========== COTIZACIONES ==========

    /**
     * Verifica el flujo completo de cotizaciones: listar, crear,
     * ver detalle y enviar a aprobación.
     */
    public function test_quotations_flow(): void
    {
        $prospect = Prospect::create(['company_name' => 'QCorp', 'contact_name' => 'C', 'email' => 'c@t.com', 'created_by' => $this->vendor->id]);
        $ms = Microservice::create(['name' => 'QSrv', 'base_cost' => 100, 'type' => 'recurring']);
        $plan = \App\Src\Infrastructure\Persistence\Models\Plan::create(['name' => 'Test Plan']);
        $plan->services()->create(['microservice_id' => $ms->id]);

        $this->actingAs($this->vendor)->get('/quotations')->assertStatus(200);
        $this->actingAs($this->vendor)->post('/quotations', [
            'prospect_id' => $prospect->id,
            'plan_id' => $plan->id,
            'selected_items' => json_encode([['microservice_id' => $ms->id, 'unit_price' => 100, 'excluded_activities' => []]]),
        ])->assertStatus(302);

        $q = Quotation::first();
        $this->assertNotNull($q);

        $this->actingAs($this->vendor)->get("/quotations/{$q->id}")->assertStatus(200);
        $this->actingAs($this->vendor)->get("/quotations/{$q->id}/send-for-approval")->assertStatus(302);

        $q->refresh();
        $this->assertEquals('under_review', $q->status);
    }

    /**
     * Verifica el flujo de aprobación de cotizaciones: el gerente comercial
     * puede aprobar pero el vendedor no.
     */
    public function test_quotation_approval_flow(): void
    {
        $prospect = Prospect::create(['company_name' => 'ACorp', 'contact_name' => 'A', 'email' => 'a@t.com', 'created_by' => $this->vendor->id]);
        $ms = Microservice::create(['name' => 'ASrv', 'base_cost' => 100, 'type' => 'recurring']);
        $q = Quotation::create(['quote_number' => 'COT-T-001', 'prospect_id' => $prospect->id, 'created_by' => $this->vendor->id, 'status' => 'under_review', 'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1]);

        $this->actingAs($this->commercialManager)->post("/quotations/{$q->id}/approve", ['rejection_reason' => 'ok'])->assertStatus(302);
        $q->refresh();
        $this->assertEquals('approved', $q->status);

        $this->actingAs($this->vendor)->post("/quotations/{$q->id}/approve")->assertStatus(403);
    }

    // ========== CONTRATOS ==========

    /**
     * Verifica el flujo de contratos: crear desde cotización aprobada
     * y mostrar el detalle.
     */
    public function test_contracts_flow(): void
    {
        $prospect = Prospect::create(['company_name' => 'CCorp', 'contact_name' => 'C', 'email' => 'c@c.com', 'created_by' => $this->vendor->id]);
        $ms = Microservice::create(['name' => 'CSrv', 'base_cost' => 100, 'type' => 'recurring']);
        $q = Quotation::create(['quote_number' => 'COT-C-001', 'prospect_id' => $prospect->id, 'created_by' => $this->vendor->id, 'status' => 'approved', 'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1]);

        $this->actingAs($this->commercialManager)->get("/contracts/create/{$q->id}")->assertStatus(200);
        $this->actingAs($this->commercialManager)->post('/contracts', [
            'quotation_id' => $q->id, 'total_amount' => 119,
        ])->assertStatus(302);
    }

    // ========== ADMINISTRACIÓN ==========

    /**
     * Verifica que el administrador pueda acceder a usuarios y roles.
     */
    public function test_admin_routes(): void
    {
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($this->admin)->get('/admin/roles')->assertStatus(200);
    }

    /**
     * Verifica que un vendedor no pueda acceder a rutas de administración.
     */
    public function test_vendor_cannot_access_admin(): void
    {
        $this->actingAs($this->vendor)->get('/admin/users')->assertStatus(403);
        $this->actingAs($this->vendor)->get('/admin/roles')->assertStatus(403);
    }

    /**
     * Verifica que el administrador pueda crear usuarios con rol asignado.
     */
    public function test_admin_can_manage_users(): void
    {
        $this->actingAs($this->admin)->get('/admin/users/create')->assertStatus(200);
        $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'New User', 'email' => 'new@test.com', 'password' => 'Test12345!', 'role' => 'admin',
        ])->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'new@test.com']);
    }

    /**
     * Verifica que el administrador pueda editar los permisos de un rol.
     */
    public function test_admin_can_edit_roles(): void
    {
        $role = \Spatie\Permission\Models\Role::first();
        $this->actingAs($this->admin)->get("/admin/roles/{$role->id}/edit")->assertStatus(200);
        $this->actingAs($this->admin)->put("/admin/roles/{$role->id}", [
            'permissions' => ['microservices.read', 'plans.read'],
        ])->assertStatus(302);
    }

    // ========== BÚSQUEDA ==========

    /**
     * Verifica que los endpoints de búsqueda AJAX respondan correctamente.
     */
    public function test_search_endpoints(): void
    {
        $this->actingAs($this->admin)->get('/search/prospects?q=test')->assertStatus(200);
        $this->actingAs($this->admin)->get('/search/plans?q=test')->assertStatus(200);
        $this->actingAs($this->admin)->get('/search/microservices?q=test')->assertStatus(200);
        $this->actingAs($this->admin)->get('/search/contracts?q=test')->assertStatus(200);
        $this->actingAs($this->admin)->get('/search/users?q=test')->assertStatus(200);
    }

    /**
     * Verifica que un usuario no autenticado no pueda usar la búsqueda.
     */
    public function test_unauthenticated_cannot_search(): void
    {
        $this->get('/search/prospects?q=test')->assertStatus(302);
    }

    // ========== FACTURAS ==========

    /**
     * Verifica el flujo de facturación: listar y crear facturas
     * sobre contratos activos.
     */
    public function test_invoices_flow(): void
    {
        $prospect = Prospect::create(['company_name' => 'ICorp', 'contact_name' => 'I', 'email' => 'i@t.com', 'created_by' => $this->admin->id]);
        $ms = Microservice::create(['name' => 'ISrv', 'base_cost' => 100, 'type' => 'recurring']);
        $q = Quotation::create(['quote_number' => 'COT-I-001', 'prospect_id' => $prospect->id, 'created_by' => $this->admin->id, 'status' => 'approved', 'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1]);
        $contract = \App\Src\Infrastructure\Persistence\Models\Contract::create([
            'contract_number' => 'CTR-I-001', 'quotation_id' => $q->id, 'approved_by' => $this->admin->id,
            'status' => ContractStatus::ACTIVE->value, 'total_amount' => 119,
        ]);

        $adminUser = User::factory()->create(['name' => 'Admin User'])->assignRole('administrative');
        $this->actingAs($adminUser)->get('/invoices')->assertStatus(200);
        $this->actingAs($adminUser)->post('/invoices', [
            'contract_id' => $contract->id, 'amount' => 119, 'issued_date' => date('Y-m-d'),
        ])->assertStatus(302);
    }

    // ========== CIERRE DE SESIÓN ==========

    /**
     * Verifica que el logout funcione correctamente.
     */
    public function test_logout(): void
    {
        $this->actingAs($this->admin)->post('/logout')->assertStatus(302);
    }

    /**
     * Verifica que la página de login sea accesible para invitados.
     */
    public function test_guest_can_see_login(): void
    {
        $this->get('/login')->assertStatus(200);
    }
}
