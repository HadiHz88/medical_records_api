<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Record extends Model
{
    /** @use HasFactory<\Database\Factories\RecordFactory> */
    use HasFactory;

    protected $fillable = [
        'template_id',
        'field_id',
        'value',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    public function getFieldValue($fieldId)
    {
        $value = $this->values()->whereHas('field', function ($query) use ($fieldId) {
            $query->where('id', $fieldId);
        })->first();

        return $value ? $value->value : null;
    }
}
