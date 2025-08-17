<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'paternal_surname',
        'maternal_surname',
        'rut',
        'status',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_confirmed_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_expires_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Normaliza el RUT antes de guardar en la base de datos.
     */
    public function setRutAttribute($value)
    {
        $this->attributes['rut'] = \App\Helpers\RutHelper::normalize($value);
    }

    /**
     * Verifica si el usuario tiene 2FA habilitado.
     */
    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return $this->two_factor_enabled;
    }

    /**
     * Genera y envía un código 2FA por email.
     */
    public function generateTwoFactorCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($this->email)->send(new TwoFactorCodeMail($this, $code));

        return $code;
    }

    /**
     * Valida el código 2FA proporcionado.
     */
    public function validateTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_code || !$this->two_factor_expires_at) {
            return false;
        }

        if (Carbon::now()->isAfter($this->two_factor_expires_at)) {
            $this->clearTwoFactorCode();
            return false;
        }

        if ($this->two_factor_code === $code) {
            $this->update(['two_factor_confirmed_at' => Carbon::now()]);
            $this->clearTwoFactorCode();
            return true;
        }

        return false;
    }

    /**
     * Limpia el código 2FA temporal.
     */
    public function clearTwoFactorCode(): void
    {
        $this->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }

    /**
     * Habilita 2FA para el usuario.
     */
    public function enableTwoFactorAuthentication(): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => Carbon::now(),
        ]);
    }

    /**
     * Deshabilita 2FA para el usuario.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
