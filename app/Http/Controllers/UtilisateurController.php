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
        $user = Auth::user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Compter les présences du mois courant
        $presenceCount = Presence::where('employerID', $user->id)
                                ->whereMonth('date', $currentMonth)
                                ->whereYear('date', $currentYear)
                                ->where('status', 'présent')
                                ->count();

        // Récupérer la dernière arrivée
        $lastPresence = Presence::where('employerID', $user->id)
                               ->whereNotNull('heureArrivee')
                               ->orderBy('heureArrivee', 'desc')
                               ->first();

        $lastArrival = $lastPresence ? $lastPresence->heureArrivee : null;

        // Récupérer le dernier départ
        $lastDeparture = Presence::where('employerID', $user->id)
                               ->whereNotNull('heureDepart')
                               ->orderBy('heureDepart', 'desc')
                               ->value('heureDepart');

        return view('user.dashboard', compact('presenceCount', 'lastArrival', 'lastDeparture'));
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

        // Présences suspectes de l'employé (pour contestation)
        $suspectPresences = Presence::where('employerID', $user->id)
                             ->where('suspect', true)
                             ->orderByDesc('date')
                             ->get();

        return view('user.presence-report', compact('presences', 'totalPresences', 'totalAbsences', 'suspectPresences'));
    }

    /**
     * L'employé conteste une de ses présences marquées suspecte.
     */
    public function contesterPresence(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'commentaire' => 'required|string|max:1000',
        ]);

        $presence = Presence::where('employerID', $user->id)->find($id);

        if (!$presence) {
            return redirect()->back()->with('error', 'Présence introuvable.');
        }

        if (!$presence->suspect) {
            return redirect()->back()->with('error', 'Cette présence n\'est pas marquée suspecte.');
        }

        $presence->update([
            'commentaire_contestation' => $request->commentaire,
            'conteste_le' => now(),
        ]);

        // Informer l'administrateur principal
        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        if ($admin) {
            $admin->notify(new \App\Notifications\PresenceContesteeNotification($user, $presence));
        }

        return redirect()->back()->with('success', 'Votre contestation a été enregistrée. L\'administrateur en sera informé.');
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
