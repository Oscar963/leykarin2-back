<?php

namespace App\Helpers;

class RutHelper
{
    /**
     * Valida un RUT chileno
     *
     * @param string $rut
     * @return bool
     */
    public static function validate(string $rut): bool
    {
        // Limpiar el RUT (remover puntos, guiones y espacios)
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        
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
        $dvCalculado = self::calculateDv($numero);
        
        return $dv === $dvCalculado;
    }
    
    /**
     * Calcula el dígito verificador de un RUT
     *
     * @param string $numero
     * @return string
     */
    public static function calculateDv(string $numero): string
    {
        $suma = 0;
        $multiplicador = 2;
        
        // Recorrer el número de derecha a izquierda
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += intval($numero[$i]) * $multiplicador;
            $multiplicador++;
            if ($multiplicador > 7) {
                $multiplicador = 2;
            }
        }
        
        $resto = $suma % 11;
        $dv = 11 - $resto;
        
        if ($dv === 11) {
            return '0';
        } elseif ($dv === 10) {
            return 'K';
        } else {
            return strval($dv);
        }
    }
    
    /**
     * Formatea un RUT con puntos y guión
     *
     * @param string $rut
     * @return string
     */
    public static function format(string $rut): string
    {
        // Limpiar el RUT
        $rut = preg_replace('/[^0-9kK]/', '', $rut);
        
        if (strlen($rut) < 2) {
            return $rut;
        }
        
        $numero = substr($rut, 0, -1);
        $dv = strtoupper(substr($rut, -1));
        
        // Agregar puntos cada 3 dígitos desde la derecha
        $numeroFormateado = number_format(intval($numero), 0, '', '.');
        
        return $numeroFormateado . '-' . $dv;
    }
    
    /**
     * Limpia un RUT removiendo formato
     *
     * @param string $rut
     * @return string
     */
    public static function clean(string $rut): string
    {
        return preg_replace('/[^0-9kK]/', '', $rut);
    }
    
    /**
     * Normaliza un RUT para almacenamiento en base de datos
     * Retorna el RUT sin formato y con DV en mayúscula
     *
     * @param string $rut
     * @return string|null
     */
    public static function normalize(string $rut): ?string
    {
        $rutLimpio = self::clean($rut);
        
        if (!self::validate($rutLimpio)) {
            return null;
        }
        
        if (strlen($rutLimpio) < 2) {
            return null;
        }
        
        $numero = substr($rutLimpio, 0, -1);
        $dv = strtoupper(substr($rutLimpio, -1));
        
        return $numero . $dv;
    }
}
