<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The Record model represents a record associated with a template.
 * It includes methods to retrieve related values, templates, and selected options.
 */
class Record extends Model
{
    /** @use HasFactory<\Database\Factories\RecordFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'template_id', // Foreign key referencing the templates table
        'field_id',    // Foreign key referencing the fields table
        'value',       // The value associated with the record
    ];

    /**
     * Get the template associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Retrieve the value of a specific field for this record.
     *
     * @param int $fieldId The ID of the field to retrieve the value for.
     * @return mixed|null The value of the field, or null if not found.
     */
    public function getFieldValue($fieldId)
    {
        $value = $this->values()->whereHas('field', function ($query) use ($fieldId) {
            $query->where('id', $fieldId);
        })->first();

        return $value ? $value->value : null;
    }

    /**
     * Get the values associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    /**
     * Get the selected options associated with this record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function selectedOptions()
    {
        return $this->belongsToMany(Option::class, 'record_option')->withTimestamps();
    }
}
