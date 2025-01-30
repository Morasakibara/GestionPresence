<?php

namespace App\Http\Controllers;
use App\Models\Presence;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;






class UtilisateurController extends Controller
{
    public function dashboard(){
        return view('user.dashboard');
    }

    public function presenceReport()
    {
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;

        $presences = Presence::where('employerID', $user->id)
                             ->whereMonth('date', $currentMonth)
                             ->get();

        // Calculer le total des présences et les absences
        $totalPresences = $presences->where('status', 'présent')->count();
        $totalAbsences = $presences->where('status', 'absent')->count();

        return view('user.presence-report', compact('presences', 'totalPresences', 'totalAbsences'));
    }

   /* public function update(Request $request)
{
    $request->validate([
        'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'password' => 'nullable|min:8|confirmed',
    ]);

    $user = auth()->Utilisateur::user();

    if ($request->hasFile('avatar')) {
        $avatarName = $user->id.'_avatar'.time().'.'.$request->avatar->getClientOriginalExtension();
        $request->avatar->storeAs('avatars', $avatarName);
        $user->avatar = $avatarName;
    }

    if ($request->password) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    return redirect()->back()->with('success', 'Profil mis à jour avec succès.');
}

public function profile(){
    return view('user.profile');

}
    */

    public function profile()
    {
        return view('user.profile', ['user' => Auth::user()]);
    }

    public function update(Request $request)
{
    $user = Auth::user();
    
    if (!$user) {
        return redirect()->back()->with('error', 'Utilisateur non trouvé.');
    }

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:utilisateur,email,' . $user->id,
        'password' => 'nullable|string|min:8|confirmed',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $updateData = [
        'nom' => $validatedData['name'],
        'email' => $validatedData['email'],
    ];

    if (!empty($validatedData['password'])) {
        $updateData['motDePasse'] = Hash::make($validatedData['password']);
    }

    if ($request->hasFile('avatar')) {
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $updateData['avatar'] = $avatarPath;
    }

    $updated = DB::table('utilisateur')
        ->where('id', $user->id)
        ->update($updateData);

    if ($updated) {
        return redirect()->route('user.profile')->with('success', 'Profil mis à jour avec succès.');
    } else {
        return redirect()->route('user.profile')->with('info', 'Aucune modification n\'a été apportée au profil.');
    }
}
    
}
