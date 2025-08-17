<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Enmascara una dirección de email para mostrar parcialmente la información.
     * 
     * Ejemplos:
     * - usuario@gmail.com → us*****@gm***.com
     * - test.email@domain.co.uk → te*******@do***.co.uk
     * - a@b.com → a@b.com (emails muy cortos se muestran completos)
     * 
     * @param string $email
     * @return string
     */
    public static function maskEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email; // Si no es un email válido, devolver tal como está
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $localPart = $parts[0];
        $domainPart = $parts[1];

        // Enmascarar la parte local (antes del @)
        $maskedLocal = self::maskString($localPart, 2, 0);

        // Enmascarar la parte del dominio
        $maskedDomain = self::maskDomain($domainPart);

        return $maskedLocal . '@' . $maskedDomain;
    }

    /**
     * Enmascara una cadena mostrando algunos caracteres al inicio y al final.
     * 
     * @param string $string
     * @param int $showStart Caracteres a mostrar al inicio
     * @param int $showEnd Caracteres a mostrar al final
     * @return string
     */
    private static function maskString(string $string, int $showStart = 2, int $showEnd = 0): string
    {
        $length = strlen($string);
        
        // Si la cadena es muy corta, mostrarla completa
        if ($length <= 3) {
            return $string;
        }

        $start = substr($string, 0, $showStart);
        $end = $showEnd > 0 ? substr($string, -$showEnd) : '';
        $maskLength = max(1, $length - $showStart - $showEnd);
        $mask = str_repeat('*', min($maskLength, 5)); // Máximo 5 asteriscos

        return $start . $mask . $end;
    }

    /**
     * Enmascara la parte del dominio de un email.
     * 
     * @param string $domain
     * @return string
     */
    private static function maskDomain(string $domain): string
    {
        // Separar el dominio de las extensiones (.com, .co.uk, etc.)
        $parts = explode('.', $domain);
        
        if (count($parts) < 2) {
            return $domain;
        }

        // Enmascarar solo la primera parte del dominio
        $mainDomain = $parts[0];
        $extensions = array_slice($parts, 1);
        
        $maskedMain = self::maskString($mainDomain, 2, 0);
        
        return $maskedMain . '.' . implode('.', $extensions);
    }

    /**
     * Genera un mensaje informativo sobre el envío del código 2FA.
     * 
     * @param string $email
     * @return string
     */
    public static function getTwoFactorMessage(string $email): string
    {
        $maskedEmail = self::maskEmail($email);
        
        return "Se ha enviado un código de verificación de 6 dígitos a tu correo electrónico {$maskedEmail}. " .
               "<br> El código expira en 10 minutos por tu seguridad.";
    }
}
