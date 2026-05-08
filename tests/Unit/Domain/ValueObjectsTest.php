<?php

namespace Tests\Unit\Domain;

use App\Src\Domain\ValueObjects\Money;
use App\Src\Domain\ValueObjects\SignedPdf;
use Tests\TestCase;

/**
 * Prueba unitaria de los objetos de valor (Value Objects) del dominio.
 * Verifica las reglas de creación, aritmética y validación de Money (moneda)
 * y SignedPdf (documento PDF firmado).
 */
class ValueObjectsTest extends TestCase
{
    // ========== MONEY (DINERO) ==========

    /**
     * Verifica que se pueda crear un objeto Money con monto y moneda por defecto (COP).
     */
    public function test_money_creation(): void
    {
        $money = new Money(100.50);
        $this->assertEquals(100.50, $money->amount());
        $this->assertEquals('COP', $money->currency());
    }

    /**
     * Verifica la creación de Money con una moneda personalizada (USD).
     */
    public function test_money_with_custom_currency(): void
    {
        $money = new Money(200, 'USD');
        $this->assertEquals(200, $money->amount());
        $this->assertEquals('USD', $money->currency());
    }

    /**
     * Verifica que no se permita crear Money con montos negativos.
     */
    public function test_money_negative_amount_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money(-10);
    }

    /**
     * Verifica la suma aritmética de dos montos en la misma moneda.
     */
    public function test_money_add(): void
    {
        $a = new Money(100);
        $b = new Money(50);
        $result = $a->add($b);
        $this->assertEquals(150, $result->amount());
    }

    /**
     * Verifica que no se permita sumar montos en monedas diferentes.
     */
    public function test_money_add_different_currency_throws(): void
    {
        $a = new Money(100, 'COP');
        $b = new Money(50, 'USD');
        $this->expectException(\InvalidArgumentException::class);
        $a->add($b);
    }

    /**
     * Verifica la multiplicación de un monto por un factor.
     */
    public function test_money_multiply(): void
    {
        $money = new Money(100);
        $result = $money->multiply(3);
        $this->assertEquals(300, $result->amount());
    }

    /**
     * Verifica el formato de presentación del monto (con separadores de miles).
     */
    public function test_money_format(): void
    {
        $money = new Money(1234567.89);
        $this->assertIsString($money->format());
        $this->assertStringContainsString('1.234.567', $money->format());
    }

    /**
     * Verifica que se pueda crear Money con valor cero (válido para descuentos).
     */
    public function test_money_zero(): void
    {
        $money = new Money(0);
        $this->assertEquals(0, $money->amount());
    }

    // ========== SIGNED PDF (PDF FIRMADO) ==========

    /**
     * Verifica la creación de un objeto SignedPdf con ruta, nombre y tamaño.
     */
    public function test_signed_pdf_creation(): void
    {
        $pdf = new SignedPdf('contracts/1/document.pdf', 'contrato_firmado.pdf', 1024);
        $this->assertEquals('contracts/1/document.pdf', $pdf->path());
        $this->assertEquals('contrato_firmado.pdf', $pdf->originalName());
        $this->assertEquals(1024, $pdf->sizeInBytes());
    }

    /**
     * Verifica que una ruta vacía se reporte como inexistente.
     */
    public function test_signed_pdf_empty_path_does_not_exist(): void
    {
        $pdf = new SignedPdf('', 'test.pdf', 100);
        $this->assertFalse($pdf->exists());
    }

    /**
     * Verifica que un archivo inexistente en disco se reporte como tal.
     */
    public function test_signed_pdf_non_existent_file(): void
    {
        $pdf = new SignedPdf('nonexistent/path.pdf', 'test.pdf', 100);
        $this->assertFalse($pdf->exists());
    }

    /**
     * Verifica que se permita crear SignedPdf con tamaño cero
     * (el archivo físico se validará al momento de la activación).
     */
    public function test_signed_pdf_with_zero_size(): void
    {
        $pdf = new SignedPdf('path.pdf', 'doc.pdf', 0);
        $this->assertEquals(0, $pdf->sizeInBytes());
    }
}
