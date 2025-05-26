<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketComment extends Model
{
    use HasFactory;

    protected $table = 'ticket_comments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'is_private',
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Obtener el ticket al que pertenece este comentario.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Obtener el usuario que realizó este comentario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener los archivos adjuntos asociados a este comentario.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class, 'comment_id');
    }

    /**
     * Scope para comentarios públicos.
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope para comentarios privados.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }
}
