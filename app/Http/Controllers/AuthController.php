<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** POST /cliente/login  { username, password }  (username = email ou telemóvel) */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (! Auth::attempt([$field => $data['username'], 'password' => $data['password']], true)) {
            return response()->json(['error' => 'Credenciais inválidas.'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['success' => true]);
    }

    /** POST /cliente/registo  { nome, email, telemovel, password } */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nome'      => ['required', 'string', 'min:2', 'max:255'],
                'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
                'telemovel' => ['nullable', 'string', 'max:32'],
                'password'  => ['required', 'string', 'min:6'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        $user = User::create([
            'name'     => $data['nome'],
            'email'    => $data['email'],
            'phone'    => $data['telemovel'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json(['success' => 'Conta criada com sucesso!']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
