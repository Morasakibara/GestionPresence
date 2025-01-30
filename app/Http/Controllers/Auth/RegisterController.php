<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use App\Models\Administrateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        
        auth()->login($user);

        return redirect('/admin/dashboard');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:Utilisateur'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'poste' => ['required', 'string', 'max:255'],
        ]);
    }

    protected function create(array $data)
    {
        $user = Utilisateur::create([
            'nom' => $data['name'],
            'email' => $data['email'],
            'motDePasse' => Hash::make($data['password']),
            'role' => 'administrateur',
        ]);

        Administrateur::create([
            'id' => $user->id,
            'poste' => $data['poste'],
        ]);

        return $user;
    }
}
