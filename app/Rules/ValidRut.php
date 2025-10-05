<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRut implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return false;
        }

        // Limpiar el RUT de puntos y guiones
        $rut = preg_replace('/[^0-9kK]/', '', $value);

        if (strlen($rut) < 2) {
            return false;
        }

        // Separar número y dígito verificador
        $numero = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));

        // Validar que el número sea numérico
        if (!is_numeric($numero)) {
            return false;
        }

        // Calcular dígito verificador
        $dvCalculado = $this->calcularDV($numero);

        return $dv === $dvCalculado;
    }

    /**
     * Calcula el dígito verificador de un RUT.
     *
     * @param string $numero
     * @return string
     */
    private function calcularDV(string $numero): string
    {
        $suma = 0;
        $multiplo = 2;

        // Recorrer el número de derecha a izquierda
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += intval($numero[$i]) * $multiplo;
            $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
        }

        $resto = $suma % 11;
        $dv = 11 - $resto;

        if ($dv === 11) {
            return '0';
        } elseif ($dv === 10) {
            return 'K';
        } else {
            return (string) $dv;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El :attribute no es un RUT chileno válido.';
    }
}
