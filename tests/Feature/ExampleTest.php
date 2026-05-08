<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba funcional básica de autenticación y acceso al dashboard.
 * Verifica que los usuarios no autenticados sean redirigidos al login
 * y que los usuarios autenticados puedan acceder al sistema.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que un usuario invitado (no autenticado) sea redirigido
     * a la página de inicio de sesión al intentar acceder al dashboard.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Verifica que un usuario autenticado pueda acceder al dashboard
     * correctamente (código 200).
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->seed();

        $user = User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
