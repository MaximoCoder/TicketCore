<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'question',
        'summary',
        'order',
        'is_published',
    ];

    /**
     * Obtener la categoría asociada a esta FAQ.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    // Obtener los steps asociados a esta FAQ.
    public function steps(): HasMany
    {
        return $this->hasMany(FaqStep::class, 'faq_id');
    }

    /**
     * Scope para FAQs publicadas.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope para ordenar por el campo order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
