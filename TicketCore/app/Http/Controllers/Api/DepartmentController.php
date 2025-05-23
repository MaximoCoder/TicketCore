<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllDepartments()
    {
        // Traer todos los departamentos activos
        $departamentos = Department::where('is_active', 1)->get();

        return response()->json(
            [
                'departments' => $departamentos,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Get paginated departments.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginatedDepartments(Request $request)
    {
        // Obtener parámetros del cuerpo de la solicitud
        $data = $request->json()->all();

        $perPage = $data['pageSize'];
        $page = $data['page'];
        $searchTerm = $data['searchTerm'] ?? '';
        $statusIds = array_filter($data['statusids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $sortBy = 'name';
        $sortDirection = 'asc';
        // Iniciar la consulta
        $query = Department::query();

        // Filtrar por statusids si está presente 
        if (!empty($statusIds)) {
            $query->whereIn('is_active', $statusIds);
        } else {
            // Si no se especifica, mostrar todos 

        }

        // Aplicar filtro de búsqueda si existe
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar orden
        $query->orderBy($sortBy, $sortDirection);
        // Condicion deleted == 0
        $query->where('deleted', 0);

        // Obtener resultados paginados
        $departments = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'ok',
            'departments' => $departments->items(),
            'totalCount' => $departments->total(),
            'page' => $departments->currentPage(),
            'pageSize' => $departments->perPage()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createDepartment(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'name' => 'required|unique:departments,name',
            'description' => '',
            'is_active' => 'required',
        ]);
        // Creamos un nuevo departamento
        $department = Department::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['is_active'],

        ]);

        return response()->json(
            [
                'department' => $department,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /*
    * Method to obtain a department by id
    */
    public function getDepartmentById(Request $request)
    {
        $department = Department::find($request->id);
        return response()->json(
            [
                'department' => $department,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateDepartmentById(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'name' => 'required',
            'description' => '',
            'is_active' => 'required',
        ]);
        // Actualizar departamento
        $department = Department::find($request->id);
        $department->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['is_active'],
        ]);
        return response()->json(
            [
                'department' => $department,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteDepartmentById(Request $request)
    {
        $ids = $request->ids;

        // Convertir a array si viene como string separado por comas
        if (is_string($ids)) {
            $ids = explode(',', $ids);
            $ids = array_map('trim', $ids);
        }
        // En lugar de eliminar pasamos el deleted a 1 para marcarlo como borrado
        Department::whereIn('id', $ids)->update(['deleted' => 1]);

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
