<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    /** @use HasFactory<\Database\Factories\FieldFactory> */
    use HasFactory;

    protected $fillable = [
        'field_name',
        'field_type',
        'display_order',
        'is_required',
        'template_id'
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }
}
