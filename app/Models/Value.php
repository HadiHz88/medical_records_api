<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Value extends Model
{
    /** @use HasFactory<\Database\Factories\ValueFactory> */
    use HasFactory;

    protected $fillable = [
        'record_id',
        'field_id',
        'value',
        'option_id',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }

    public function multipleSelections(): HasMany
    {
        return $this->hasMany(MultipleSelection::class);
    }
}