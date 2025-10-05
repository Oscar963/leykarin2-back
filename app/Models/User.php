<?php

namespace App\Models;

use App\Mail\TwoFactorCodeMail;
use App\Notifications\ResetPasswordNotification;
use App\Traits\FiresRoleEvents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles, FiresRoleEvents {
        HasRoles::assignRole as protected assignRoleOriginal;
        HasRoles::removeRole as protected removeRoleOriginal;
        HasRoles::syncRoles as protected syncRolesOriginal;
        FiresRoleEvents::assignRole insteadof HasRoles;
        FiresRoleEvents::removeRole insteadof HasRoles;
        FiresRoleEvents::syncRoles insteadof HasRoles;
    }

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
        'two_factor_confirmed_at',
        'google_id',
        'google_email',
        'google_name',
        'google_avatar',
        'google_verified_at',
        'google_domain',
        'auth_provider',
        'type_dependency_id'
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
        'google_verified_at' => 'datetime',
        'status' => 'boolean',
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

    /**
     * Verifica si el usuario está autenticado con Google OAuth.
     */
    public function isGoogleUser(): bool
    {
        return $this->auth_provider === 'google' && !empty($this->google_id);
    }

    /**
     * Verifica si el usuario puede usar autenticación tradicional.
     */
    public function canUseTraditionalAuth(): bool
    {
        return !empty($this->password) && ($this->auth_provider === 'local' || $this->auth_provider === 'google');
    }

    /**
     * Actualiza la información de Google OAuth del usuario.
     */
    public function updateGoogleInfo(array $googleData): void
    {
        $this->update([
            'google_id' => $googleData['sub'] ?? $googleData['id'],
            'google_email' => $googleData['email'],
            'google_name' => $googleData['name'],
            'google_avatar' => $googleData['picture'] ?? null,
            'google_verified_at' => Carbon::now(),
            'google_domain' => $googleData['hd'] ?? null,
            'auth_provider' => 'google',
            'status' => true, // Activar usuario al vincular con Google
        ]);
    }

    /**
     * Busca un usuario por Google ID.
     */
    public static function findByGoogleId(string $googleId): ?self
    {
        return static::where('google_id', $googleId)->first();
    }

    /**
     * Busca un usuario por email de Google.
     */
    public static function findByGoogleEmail(string $email): ?self
    {
        return static::where('google_email', $email)
                    ->orWhere('email', $email)
                    ->first();
    }

    /**
     * Verifica si el dominio de Google está permitido.
     */
    public function isGoogleDomainAllowed(): bool
    {
        $allowedDomain = config('services.google.allowed_domain');
        
        if (!$allowedDomain) {
            return true; // Si no hay restricción de dominio
        }

        return $this->google_domain === $allowedDomain;
    }

    /**
     * Relación con el tipo de dependencia organizacional.
     */
    public function typeDependency()
    {
        return $this->belongsTo(\App\Models\TypeDependency::class, 'type_dependency_id');
    }

    /**
     * Verifica si el usuario debe ser filtrado por dependencia.
     */
    public function shouldFilterByDependency(): bool
    {
        $organizationalRoles = ['IMA', 'DISAM', 'DEMUCE'];
        
        return $this->hasAnyRole($organizationalRoles) && !empty($this->type_dependency_id);
    }

    /**
     * Obtiene el ID de dependencia para filtrado.
     */
    public function getDependencyIdForFilter(): ?int
    {
        return $this->shouldFilterByDependency() ? $this->type_dependency_id : null;
    }
}
