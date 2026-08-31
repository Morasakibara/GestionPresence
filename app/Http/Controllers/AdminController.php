<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Commande;
use App\Models\Employer;
use App\Models\Presence;
use App\Models\Retrait;
use App\Models\ServiceFourni;
use App\Models\StockPapier;
use App\Models\StockTshirt;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = Auth::user();

        $totalEmployees = Utilisateur::where('role', 'Employer')->count();
        $totalSupervisors = Utilisateur::where('role', 'Superviseur')->count();

        $today = now()->toDateString();
        $presentToday = Presence::whereDate('date', $today)
            ->where('status', 'présent')
            ->count();

        $absentToday = Presence::whereDate('date', $today)
            ->where('status', 'Absent')
            ->count();

        // Listes détaillées pour les modals au survol
        $presentsAujourdhui = Presence::whereDate('date', $today)
            ->where('status', 'present')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureArrivee', 'presence.heureDepart')
            ->orderBy('presence.heureArrivee')
            ->get();

        $absentsAujourdhui = Utilisateur::where('role', 'Employer')
            ->whereNotIn('id', Presence::whereDate('date', $today)->pluck('employerID'))
            ->select('nom')
            ->orderBy('nom')
            ->get();

        $arrivesAujourdhui = Presence::whereDate('date', $today)
            ->whereNotNull('heureArrivee')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureArrivee')
            ->orderBy('presence.heureArrivee')
            ->get();

        $partisAujourdhui = Presence::whereDate('date', $today)
            ->whereNotNull('heureDepart')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureDepart')
            ->orderBy('presence.heureDepart')
            ->get();

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyPresences = Presence::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'présent')
            ->count();

        $monthlyAbsences = Presence::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'Absent')
            ->count();

        $monthlyLates = Presence::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereRaw('(HOUR(heureArrivee) > 8 OR (HOUR(heureArrivee) = 8 AND MINUTE(heureArrivee) > 0))')
            ->count();

        $equipes = Superviseur::whereNotNull('equipe')->where('equipe', '!=', '')->distinct()->pluck('equipe')->sort()->values();
        $equipeSelectionnee = request()->query('equipe');

        $employerIdsFiltre = null;
        if ($equipeSelectionnee) {
            $employerIdsFiltre = Employer::where('equipe', $equipeSelectionnee)->pluck('id')->toArray();
        }
        $evolutionEvaluations = \App\Services\EvaluationService::evolutionMensuelle($employerIdsFiltre, 6);
        $statsEvolution = \App\Services\EvaluationService::statsEvolution($evolutionEvaluations);

        $caisseChart = Cache::remember('admin_caisse_chart_' . now()->format('Y-m-d-H'), 120, function () {
            $labels = [];
            $entrees = [];
            $sorties = [];
            for ($i = 6; $i >= 0; $i--) {
                $jour = now()->subDays($i)->toDateString();
                $labels[] = now()->subDays($i)->format('d/m');
                $e = 0;
                $s = 0;
                foreach (['directrice', 'secretaire'] as $type) {
                    $sup = Superviseur::where('type_superviseur', $type)->first();
                    if (!$sup) continue;
                    $e += (float) Commande::where('superviseur_id', $sup->id)->whereDate('date', $jour)->sum('montant')
                        + (float) ServiceFourni::where('superviseur_id', $sup->id)->whereDate('date', $jour)->sum('montant');
                    $s += (float) Retrait::where('superviseur_id', $sup->id)->whereDate('date', $jour)->sum('montant');
                }
                $entrees[] = $e;
                $sorties[] = $s;
            }
            return ['labels' => $labels, 'entrees' => $entrees, 'sorties' => $sorties];
        });

        $caisses = [];
        foreach (['directrice' => 'directrice', 'secretaire' => 'secretaire'] as $label => $type) {
            $sup = Superviseur::where('type_superviseur', $type)->first();
            if (!$sup) continue;
            $entrees = Commande::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant')
                + ServiceFourni::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $sorties = Retrait::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $caisses[$label] = [
                'nom' => Utilisateur::find($sup->id)->nom ?? $label,
                'entrees' => $entrees,
                'sorties' => $sorties,
                'net' => $entrees - $sorties,
            ];
        }

        $gestionnaireStock = Superviseur::where('type_superviseur', 'gestionnaire_stock')->first();
        $stockTshirts = $stockPapiers = collect();
        $totalStockTshirts = 0;
        $alertesStock = 0;
        if ($gestionnaireStock) {
            $stockTshirts = StockTshirt::where('superviseur_id', $gestionnaireStock->id)->get();
            $stockPapiers = StockPapier::where('superviseur_id', $gestionnaireStock->id)->get();
            $totalStockTshirts = $stockTshirts->sum('quantite');
            $alertesStock = $stockTshirts->filter(fn($t) => $t->quantite <= $t->seuil_alerte)->count()
                + $stockPapiers->filter(fn($p) => $p->metres_restants <= $p->seuil_alerte)->count();
        }

        // Historique des annulations (30 derniers jours)
        $annulations = Commande::where('statut_paiement', 'annule')
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->join('utilisateur', 'commandes.superviseur_id', '=', 'utilisateur.id')
            ->select('commandes.*', 'utilisateur.nom as superviseur_nom')
            ->orderByDesc('commandes.date')
            ->limit(15)
            ->get();
        $totalAnnulationsMontant = Commande::where('statut_paiement', 'annule')
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->sum('montant');
        $totalRemboursements = Commande::where('statut_paiement', 'annule')
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->sum('montant_paye');

        return view('admin.dashboard', compact(
            'totalEmployees', 'totalSupervisors',
            'presentToday', 'absentToday',
            'monthlyPresences', 'monthlyAbsences', 'monthlyLates',
            'evolutionEvaluations', 'statsEvolution',
            'equipes', 'equipeSelectionnee',
            'caisses', 'caisseChart',
            'stockTshirts', 'stockPapiers',
            'totalStockTshirts', 'alertesStock',
            'presentsAujourdhui', 'absentsAujourdhui',
            'arrivesAujourdhui', 'partisAujourdhui',
            'annulations', 'totalAnnulationsMontant', 'totalRemboursements'
        ));
    }

    /**
     * Dashboard métriques système — temps réponse, erreurs, logs.
     */
    public function metrics()
    {
        $today = now()->toDateString();

        // ── Logs Laravel : erreurs et warns d'aujourd'hui ──
        $logPath = storage_path('logs/laravel.log');
        $erreurs = 0;
        $warnings = 0;
        $recentErrors = [];

        if (file_exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $recentLines = array_reverse($lines);
            foreach ($recentLines as $line) {
                if (!str_contains($line, $today)) continue;
                if (str_contains($line, 'local.ERROR')) {
                    $erreurs++;
                    if (count($recentErrors) < 10) {
                        $recentErrors[] = substr($line, 0, 200);
                    }
                }
                if (str_contains($line, 'local.WARNING')) {
                    $warnings++;
                }
            }
        }

        // ── Requêtes totales aujourd'hui (table audit_logs) ──
        try {
            $totalRequetes = AuditLog::whereDate('created_at', $today)->count();
            $requeteParModule = AuditLog::whereDate('created_at', $today)
                ->select('module', DB::raw('count(*) as total'))
                ->groupBy('module')
                ->orderByDesc('total')
                ->get();
        } catch (\Exception $e) {
            $totalRequetes = 0;
            $requeteParModule = collect();
        }

        // ── Rate limiting : nombre de tentatives de login ──
        $rateLimitCount = 0;
        try {
            $rateLimits = Cache::get('login_attempts_' . now()->format('Y-m-d'), []);
            $rateLimitCount = is_array($rateLimits) ? count($rateLimits) : 0;
        } catch (\Exception $e) {
            // Ignorer
        }

        // ── Sessions actives (utilisateurs connectés aujourd'hui) ──
        try {
            $usersToday = AuditLog::whereDate('created_at', $today)
                ->where('action', 'like', '%login%')
                ->distinct('utilisateur_id')
                ->count('utilisateur_id');
        } catch (\Exception $e) {
            $usersToday = 0;
        }

        // ── uptime du serveur ──
        $uptime = null;
        if (PHP_OS_FAMILY !== 'Windows') {
            $uptimeRaw = @file_get_contents('/proc/uptime');
            if ($uptimeRaw) {
                $uptimeSeconds = (float) explode(' ', $uptimeRaw)[0];
                $days = floor($uptimeSeconds / 86400);
                $hours = floor(($uptimeSeconds % 86400) / 3600);
                $minutes = floor(($uptimeSeconds % 3600) / 60);
                $uptime = "{$days}j {$hours}h {$minutes}m";
            }
        }

        return view('admin.metrics', compact(
            'erreurs', 'warnings', 'recentErrors',
            'totalRequetes', 'requeteParModule',
            'rateLimitCount', 'usersToday', 'uptime'
        ));
    }

    public function showProfileForm()
    {
        /** @var Utilisateur|null $user */
        $user = Auth::user();
        return view('admin.profile', ['user' => $user]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            /** @var Utilisateur $user */
            $user = Auth::user();

            $user->nom = $request->nom;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }
                $avatarName = time() . '.' . $request->avatar->extension();
                $request->avatar->storeAs('avatars', $avatarName, 'public');
                $user->avatar = $avatarName;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil: ' . $e->getMessage(),
            ], 500);
        }
    }
}
