<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Presence;
use App\Models\Utilisateur;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    public function dashboard()
    {
        return view('employee.dashboard');
    }

    public function profile()
    {
        return view('employee.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->Utilisateur::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:utilisateur,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->nom = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->motDePasse = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return redirect()->route('employee.profile')->with('success', 'Profil mis à jour avec succès.');
    }

    public function presenceReport()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $presences = Presence::where('employerID', $user->id)
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get();

        $totalPresences = $presences->where('status', 'present')->count();
        $totalAbsences = $presences->where('status', 'absent')->count();

        $presenceData = [
            'labels' => ['Présences', 'Absences'],
            'datasets' => [
                [
                    'data' => [$totalPresences, $totalAbsences],
                    'backgroundColor' => ['#36A2EB', '#FF6384'],
                ]
            ]
        ];

        return view('employee.presence-report', compact('presenceData', 'totalPresences', 'totalAbsences'));
    }
}