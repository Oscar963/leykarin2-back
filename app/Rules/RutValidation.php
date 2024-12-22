<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class RutValidation implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validateRut($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El RUT no es válido.';
    }

    private function validateRut(string $rut): bool
    {
        // Verificar el formato
        if (!preg_match('/^[0-9]+-[0-9Kk]$/', $rut)) {
            return false;
        }

        // Normalizar el RUT
        $rut = strtoupper($rut);
        $digits = substr($rut, 0, -2);
        $dv = substr($rut, -1);

        // Calcular el dígito verificador
        $sum = 0;
        $factor = 2;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $sum += $digits[$i] * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $remainder = $sum % 11;
        $computedDv = 11 - $remainder;

        if ($computedDv == 11) {
            $computedDv = '0';
        } elseif ($computedDv == 10) {
            $computedDv = 'K';
        } else {
            $computedDv = (string) $computedDv;
        }

        return $dv === $computedDv;
    }
}
