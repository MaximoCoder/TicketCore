<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqStep extends Model
{
    use HasFactory;

    protected $table = 'faq_steps';

    protected $fillable = [
        'faq_id',
        'title',
        'content',
        'step_order',
        'image_path',
        'deleted',
    ];
}
