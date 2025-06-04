<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllUsers()
    {
        // Traer todos los users que son admin (para asignar los tickets)
        $departamentos = User::where('role', 'admin')->get();

        return response()->json(
            [
                'users' => $departamentos,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Get paginated users.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaginatedUsers(Request $request)
    {
        // Obtener parámetros del cuerpo de la solicitud
        $data = $request->json()->all();

        $perPage = $data['pageSize'];
        $page = $data['page'];
        $searchTerm = $data['searchTerm'] ?? '';
        $user_Ids = array_filter($data['department_ids'] ?? [], function ($value) {
            return $value !== '' && is_numeric($value);
        });
        $sortBy = 'name';
        $sortDirection = 'asc';
        // Iniciar la consulta
        $query = User::with('department');

        // Filtrar por user_Ids si está presente 
        if (!empty($user_Ids)) {
            $query->whereIn('department_id', $user_Ids);
        }

        // Aplicar filtro de búsqueda si existe
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Aplicar orden
        $query->orderBy($sortBy, $sortDirection);
        // Condicion deleted == 0
        $query->where('deleted', 0);

        // Obtener resultados paginados
        $users = $query->paginate($perPage, ['*'], 'page', $page);

        // Mapear usuarios para incluir el nombre del departamento
        $usersData = $users->getCollection()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'department' => $user->department->name ?? null,
                'role' => $user->role,
                'is_active' => $user->is_active
            ];
        })->values();

        return response()->json([
            'status' => 'ok',
            'users' => $usersData,
            'totalCount' => $users->total(),
            'page' => $users->currentPage(),
            'pageSize' => $users->perPage()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createUser(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role' => 'required',
        ]);
        // Creamos un nuevo departamento
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return response()->json(
            [
                'user' => $user,
                'status' => 'ok'
            ]
        );
    }

    /*
    * Method to obtain a user by id
    */
    public function getUserById(Request $request)
    {
        $user = User::find($request->id);
        return response()->json(
            [
                'user' => $user,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUserById(Request $request)
    {
        // Validar el request
        $data = $request->validate([
            'name' => 'required',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($request->id),
            ],
            'role' => 'required',
        ]);
        // Actualizar departamento
        $user = User::findOrFail($request->id);
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ]);
        return response()->json(
            [
                'user' => $user,
                'status' => 'ok'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteUserById(Request $request)
    {
        $ids = $request->ids;

        // Convertir a array si viene como string separado por comas
        if (is_string($ids)) {
            $ids = explode(',', $ids);
            $ids = array_map('trim', $ids);
        }
        // En lugar de eliminar pasamos el deleted a 1 para marcarlo como borrado
        User::whereIn('id', $ids)->update(['deleted' => 1]);

        return response()->json([
            'status' => 'ok'
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
