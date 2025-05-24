<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
