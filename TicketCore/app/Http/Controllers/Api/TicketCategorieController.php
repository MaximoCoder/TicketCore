<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketCategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllCategories()
    {
        // Traer todos los users que son admin (para asignar los tickets)
        $categories = TicketCategory::where('is_active', 1)->get();

        return response()->json(
            [
                'categories' => $categories,
                'status' => 'ok'
            ]
        );
    }
}
