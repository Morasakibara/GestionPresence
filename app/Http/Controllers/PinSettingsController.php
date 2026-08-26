<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PinSettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('settings.pin', compact('user'));
    }
}
