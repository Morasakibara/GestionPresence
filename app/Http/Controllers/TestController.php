<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    // Mot de passe pour accéder au formulaire d'inscription
    private $registrationAccessCode = "3hcig2023";

    public function index()
    {
        return view('index');
    }

    public function verifyRegistrationAccess(Request $request)
{
    $request->validate([
        'access_code' => 'required|string'
    ]);

    if ($request->access_code === $this->registrationAccessCode) {
        // Créer un cookie qui expire dans 30 minutes
        $cookie = cookie('registration_access', 'granted', 30);

        return redirect()->route('register')
            ->with('success', 'Accès autorisé')
            ->withCookie($cookie);
    }

    return redirect()->route('index')->with('error', 'Code d\'accès incorrect');
}

    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}
