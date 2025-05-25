<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $table = 'ticket_attachments';

    protected $fillable = [
        'ticket_id',
        'comment_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'uploaded_by'
    ];
}
