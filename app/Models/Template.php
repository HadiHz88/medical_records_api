<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    /** @use HasFactory<\Database\Factories\TemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}