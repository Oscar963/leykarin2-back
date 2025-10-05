<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para validar que la IP de la sesión sea consistente.
 * 
 * Previene session hijacking al detectar cambios de IP durante la sesión.
 * Incluye whitelist de IPs confiables (proxies, VPN corporativa).
 */
class ValidateSessionIp
{
    /**
     * IPs confiables que pueden cambiar (proxies, VPN corporativa)
     * 
     * @var array
     */
    protected $trustedIps = [];

    /**
     * Permitir cambio de IP si está en la misma subred
     * 
     * @var bool
     */
    protected $allowSameSubnet = true;

    public function __construct()
    {
        $this->trustedIps = array_filter(explode(',', env('TRUSTED_IPS', '')));
        $this->allowSameSubnet = env('ALLOW_SAME_SUBNET', true);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $sessionIp = $request->session()->get('session_ip');
            $currentIp = $request->ip();

            // Si ya existe una IP registrada en la sesión
            if ($sessionIp) {
                // Verificar si la IP cambió
                if ($sessionIp !== $currentIp) {
                    // Verificar si es una IP confiable
                    if ($this->isTrustedIp($currentIp)) {
                        // Actualizar IP de sesión
                        $request->session()->put('session_ip', $currentIp);
                        return $next($request);
                    }

                    // Verificar si está en la misma subred (opcional)
                    if ($this->allowSameSubnet && $this->isSameSubnet($sessionIp, $currentIp)) {
                        // Actualizar IP y continuar
                        $request->session()->put('session_ip', $currentIp);
                        
                        Log::info('IP cambió dentro de la misma subred', [
                            'user_id' => Auth::id(),
                            'old_ip' => $sessionIp,
                            'new_ip' => $currentIp
                        ]);
                        
                        return $next($request);
                    }

                    // IP cambió de forma sospechosa
                    $user = Auth::user();

                    Log::warning('IP mismatch detectado - Posible session hijacking', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'session_ip' => $sessionIp,
                        'current_ip' => $currentIp,
                        'url' => $request->fullUrl(),
                        'timestamp' => now()->toDateTimeString()
                    ]);

                    // Cerrar sesión por seguridad
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return response()->json([
                        'message' => 'Tu sesión ha sido invalidada por razones de seguridad. Por favor, inicia sesión nuevamente.',
                        'error' => 'SESSION_INVALID',
                        'reason' => 'IP_MISMATCH'
                    ], 401);
                }
            } else {
                // Primera vez - registrar la IP en la sesión
                $request->session()->put('session_ip', $currentIp);
                
                Log::info('IP de sesión registrada', [
                    'user_id' => Auth::id(),
                    'ip' => $currentIp,
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Verifica si una IP está en la lista de confianza.
     *
     * @param string $ip
     * @return bool
     */
    protected function isTrustedIp(string $ip): bool
    {
        return in_array($ip, $this->trustedIps);
    }

    /**
     * Verifica si dos IPs están en la misma subred /24.
     *
     * @param string $ip1
     * @param string $ip2
     * @return bool
     */
    protected function isSameSubnet(string $ip1, string $ip2): bool
    {
        // Validar que sean IPs IPv4 válidas
        if (!filter_var($ip1, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || 
            !filter_var($ip2, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $subnet1 = substr($ip1, 0, strrpos($ip1, '.'));
        $subnet2 = substr($ip2, 0, strrpos($ip2, '.'));
        
        return $subnet1 === $subnet2;
    }
}
