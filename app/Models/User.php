<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The User model represents an authenticated user in the system.
 * It includes attributes such as name, email, and password, and provides
 * methods to manage user permissions and roles.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',     // The name of the user
        'email',    // The email address of the user
        'password', // The hashed password of the user
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',        // The hashed password of the user
        'remember_token',  // The token used to remember the user
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Casts the email_verified_at attribute to a datetime
        'password' => 'hashed',           // Casts the password attribute to a hashed value
    ];

    /**
     * Check if the user has permission to access a specific template.
     *
     * @param int $templateId The ID of the template to check
     * @return bool True if the user has permission, false otherwise
     */
    public function hasPermissionTo($templateId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('template_id', $templateId)
            ->exists();
    }

    /**
     * Check if the user has an admin role.
     *
     * @return bool True if the user is an admin, false otherwise
     */
    public function isAdmin(): bool
    {
        return $this->admin()->exists();
    }

    /**
     * Get the admin role associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the permissions associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
