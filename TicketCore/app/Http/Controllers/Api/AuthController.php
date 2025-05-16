<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     *  (REGISTER)
     */
    public function register(RegisterRequest $request)
    {
        // Validar el request 
        $data = $request->validated(); // Valida con las reglas de el request

        // Crear el usuario
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
            'is_active' => true,
            'department_id' => $data['department_id']
        ]);

        // Devolver la respuesta
        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ];
    }

    /**
     * Display the specified resource.
     */
    public function login(LoginRequest $request)
    {
        // Validar el request 
        $data = $request->validated();
        // Revisar el password
        if (!auth()->attempt($data)) {
            return response(['error' => 'El email o password es incorrecto'], 422);
        }
        // Autenticar el usuario

        $user = Auth::user();

        // Devolver la respuesta
        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ];
    }

    // Funcion para cerraar session
    public function logout(Request $request)
    {
        $user = $request->user();
        // Borrar el token
        $user->currentAccessToken()->delete();

        // Devolver la respuesta
        return [
            'user' => null
        ];
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
