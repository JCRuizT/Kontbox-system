<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test de ejemplo para verificar que PHPUnit está configurado correctamente.
 */
class ExampleTest extends TestCase
{
    /**
     * Verifica que la aserción básica funciona (prueba de humo).
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
