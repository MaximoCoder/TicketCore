<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketPriority extends Model
{
    use HasFactory;

    protected $table = 'ticket_priorities';

    protected $fillable = [
        'name',
        'color_code',
        'response_time',
    ];
    /**
     * Obtener los tickets con esta prioridad.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
