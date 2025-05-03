<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultipleSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'value_id',
        'option_id',
    ];

    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}