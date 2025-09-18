<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReCaptchaService;

/**
 * Comando para probar la configuración de reCAPTCHA
 */
class TestReCaptcha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recaptcha:test {token?} {--ip=127.0.0.1} {--action=test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la configuración y validación de reCAPTCHA';

    private ReCaptchaService $recaptchaService;

    public function __construct(ReCaptchaService $recaptchaService)
    {
        parent::__construct();
        $this->recaptchaService = $recaptchaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Prueba de Configuración reCAPTCHA ===');
        $this->newLine();

        // Mostrar configuración
        $this->info('Configuración actual:');
        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Habilitado', $this->recaptchaService->isEnabled() ? 'Sí' : 'No'],
                ['Site Key', $this->recaptchaService->getSiteKey() ?: 'No configurada'],
                ['Score Mínimo', $this->recaptchaService->getMinScore()],
                ['Secret Key', config('services.recaptcha.secret_key') ? 'Configurada' : 'No configurada'],
            ]
        );

        $this->newLine();

        // Si no está habilitado, mostrar advertencia
        if (!$this->recaptchaService->isEnabled()) {
            $this->warn('⚠️  reCAPTCHA está deshabilitado. Las validaciones se saltarán.');
            return 0;
        }

        // Verificar configuración básica
        if (!config('services.recaptcha.secret_key')) {
            $this->error('❌ RECAPTCHA_SECRET_KEY no está configurada');
            return 1;
        }

        if (!$this->recaptchaService->getSiteKey()) {
            $this->warn('⚠️  RECAPTCHA_SITE_KEY no está configurada');
        }

        // Si se proporciona un token, probarlo
        $token = $this->argument('token');
        if ($token) {
            $this->info('Probando token: ' . substr($token, 0, 20) . '...');
            
            $result = $this->recaptchaService->verify(
                $token,
                $this->option('ip'),
                $this->option('action')
            );

            $this->newLine();
            $this->info('Resultado de la verificación:');
            
            if ($result['success']) {
                $this->info('✅ Validación exitosa');
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['Score', $result['score'] ?? 'N/A'],
                        ['Acción', $result['action'] ?? 'N/A'],
                        ['Hostname', $result['hostname'] ?? 'N/A'],
                        ['Timestamp', $result['challenge_ts'] ?? 'N/A'],
                    ]
                );
            } else {
                $this->error('❌ Validación fallida');
                $this->error('Error: ' . ($result['error'] ?? 'Desconocido'));
                $this->error('Mensaje: ' . ($result['message'] ?? 'Sin mensaje'));
                
                if (isset($result['error_codes'])) {
                    $this->error('Códigos de error: ' . implode(', ', $result['error_codes']));
                }
            }
        } else {
            $this->info('💡 Para probar un token específico, usa:');
            $this->info('php artisan recaptcha:test "tu-token-aqui"');
        }

        $this->newLine();
        $this->info('✅ Configuración verificada');
        
        return 0;
    }
}
