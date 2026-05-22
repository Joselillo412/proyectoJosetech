<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Registra un nuevo cliente en el sistema y devuelve su Token de acceso.
    public function register(Request $request)
    {
        // Validación estricta de los datos entrantes
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telefono' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed', // Espera un campo password_confirmation
        ]);

        // Creación del usuario con contraseña encriptada en Bcrypt
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password), // Encriptación segura
            'rol' => 'cliente', // Rol por defecto para nuevos registros
        ]);

        // Generación del token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // Autentica a un usuario existente.
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Verificamos si el usuario existe y la contraseña coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Generamos un nuevo token para esta sesión
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
    public function logout(Request $request)
    {
        // Revoca el token que se usó para autenticar la petición actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
    public function solicitarReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return response()->json(['message' => 'Solicitud enviada. El administrador se pondrá en contacto contigo.']);
    }
}
