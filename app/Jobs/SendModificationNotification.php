<?php

namespace App\Jobs;

use App\Models\Modification;
use App\Mail\ModificationCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendModificationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $modification;
    protected $visadorEmail;
    protected $emailContent;

    /**
     * Create a new job instance.
     */
    public function __construct(Modification $modification, string $visadorEmail = null, ?string $emailContent = null)
    {
        $this->modification = $modification;
        $this->visadorEmail = $visadorEmail ?? 'oscar.apata@municipalidadarica.cl';
        $this->emailContent = $emailContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Cargar las relaciones necesarias para el correo
            $modification = $this->modification->load([
                'modificationType',
                'purchasePlan.direction',
                'createdBy'
            ]);

            // Enviar correo
            Mail::to($this->visadorEmail)->send(new ModificationCreated($modification, $this->emailContent));

            Log::info('Correo de notificación de modificación enviado exitosamente', [
                'modification_id' => $this->modification->id,
                'visador_email' => $this->visadorEmail
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando correo de notificación de modificación', [
                'modification_id' => $this->modification->id,
                'visador_email' => $this->visadorEmail,
                'error' => $e->getMessage()
            ]);

            // Re-lanzar la excepción para que el job falle y pueda ser reintentado
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de notificación de modificación falló', [
            'modification_id' => $this->modification->id,
            'visador_email' => $this->visadorEmail,
            'error' => $exception->getMessage()
        ]);
    }
}
