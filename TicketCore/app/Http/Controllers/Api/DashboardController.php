<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\TicketCategory;
use App\Models\TicketStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardData()
    {
        // Gráfica 1: Tickets por estado
        $ticketsByStatus = TicketStatus::withCount('tickets')
            ->get()
            ->map(function ($status) {
                return [
                    'name' => $status->name,
                    'count' => $status->tickets_count
                ];
            });

        // Gráfica 2: Tickets por categoría
        $ticketsByCategory = TicketCategory::withCount('tickets')
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->tickets_count
                ];
            });

        // Gráfica 3: Tickets por departamento
        $ticketsByDepartment = Department::withCount('tickets')
            ->get()
            ->map(function ($department) {
                return [
                    'name' => $department->name,
                    'count' => $department->tickets_count
                ];
            });

        return response()->json([
            'status' => 'ok',
            'ticketsByStatus' => $ticketsByStatus,
            'ticketsByCategory' => $ticketsByCategory,
            'ticketsByDepartment' => $ticketsByDepartment,
        ]);
    }
}
