<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The Value model represents a value associated with a record and a field.
 * It optionally links to an option.
 */
class Value extends Model
{
    /** @use HasFactory<\Database\Factories\ValueFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'record_id', // Foreign key referencing the records table
        'field_id',  // Foreign key referencing the fields table
        'value',     // The actual value stored
        'option_id', // Foreign key referencing the options table (optional)
    ];

    /**
     * Get the record associated with this value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Get the field associated with this value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    /**
     * Get the option associated with this value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class, 'option_id');
    }
}
