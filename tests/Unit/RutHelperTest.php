<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\RutHelper;

class RutHelperTest extends TestCase
{
    /**
     * Test de validación de RUTs válidos
     */
    public function test_validates_valid_ruts()
    {
        $validRuts = [
            '12345678-5',
            '12.345.678-5',
            '11111111-1',
            '22222222-2',
            '7775577-2',
            '7.775.577-2',
            '24965885-5'
        ];

        foreach ($validRuts as $rut) {
            $this->assertTrue(RutHelper::validate($rut), "RUT {$rut} debería ser válido");
        }
    }

    /**
     * Test de validación de RUTs inválidos
     */
    public function test_rejects_invalid_ruts()
    {
        $invalidRuts = [
            '12345678-9', // DV incorrecto
            '123456789', // Sin DV
            '1234567X', // DV inválido
            '', // Vacío
            '12.345.678-9', // DV incorrecto con formato
            'abcdefgh-k', // Letras en número
            '1', // Muy corto
        ];

        foreach ($invalidRuts as $rut) {
            $this->assertFalse(RutHelper::validate($rut), "RUT {$rut} debería ser inválido");
        }
    }

    /**
     * Test de cálculo de dígito verificador
     */
    public function test_calculates_correct_dv()
    {
        $testCases = [
            '12345678' => '5',
            '11111111' => '1',
            '22222222' => '2',
            '7775577' => '2',
            '24965885' => '5',
        ];

        foreach ($testCases as $numero => $expectedDv) {
            $this->assertEquals($expectedDv, RutHelper::calculateDv($numero));
        }
    }

    /**
     * Test de formateo de RUT
     */
    public function test_formats_rut_correctly()
    {
        $testCases = [
            '123456785' => '12.345.678-5',
            '11111111-1' => '11.111.111-1',
            '77755772' => '7.775.577-2',
            '249658855' => '24.965.885-5',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, RutHelper::format($input));
        }
    }

    /**
     * Test de limpieza de RUT
     */
    public function test_cleans_rut_correctly()
    {
        $testCases = [
            '12.345.678-K' => '12345678K',
            '12 345 678 K' => '12345678K',
            '12-345-678-K' => '12345678K',
            '12345678k' => '12345678k',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, RutHelper::clean($input));
        }
    }

    /**
     * Test de normalización de RUT
     */
    public function test_normalizes_rut_correctly()
    {
        $testCases = [
            '12.345.678-5' => '123456785',
            '12345678-5' => '123456785',
            '11.111.111-1' => '111111111',
            '7.775.577-2' => '77755772',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, RutHelper::normalize($input));
        }
    }

    /**
     * Test de normalización con RUTs inválidos
     */
    public function test_normalize_returns_null_for_invalid_ruts()
    {
        $invalidRuts = [
            '12345678-9', // DV incorrecto
            '', // Vacío
            'invalid', // No es RUT
        ];

        foreach ($invalidRuts as $rut) {
            $this->assertNull(RutHelper::normalize($rut));
        }
    }

    /**
     * Test con RUTs de casos especiales
     */
    public function test_handles_edge_cases()
    {
        // RUT con DV K
        $this->assertTrue(RutHelper::validate('12345670-K'));
        $this->assertEquals('K', RutHelper::calculateDv('12345670'));
        
        // RUT con DV 0
        $this->assertTrue(RutHelper::validate('12345675-0'));
        $this->assertEquals('0', RutHelper::calculateDv('12345675'));
    }
}
