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

        $today = now()->toDateString();
        $today6 = now()->subDays(6)->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // ═══ 1 seule requête pour tous les compteurs présence du jour ═══
        $presenceStats = Presence::whereDate('date', $today)
            ->selectRaw('SUM(status = ?) as presents, SUM(status = ?) as absents', ['présent', 'Absent'])
            ->first();
        $totalEmployees = Utilisateur::where('role', 'Employer')->count();
        $totalSupervisors = Utilisateur::where('role', 'Superviseur')->count();
        $presentToday = (int) ($presenceStats->presents ?? 0);
        $absentToday = (int) ($presenceStats->absents ?? 0);

        // ═══ Listes modals en 2 requêtes max ═══
        $presenceJour = Presence::whereDate('date', $today)
            ->whereNotNull('heureArrivee')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureArrivee', 'presence.heureDepart', 'presence.status')
            ->orderBy('presence.heureArrivee')
            ->get();
        $presentsAujourdhui = $presenceJour->filter(fn($p) => in_array($p->status, ['présent', 'present']))->values();
        $arrivesAujourdhui = $presenceJour->filter(fn($p) => $p->heureArrivee !== null)->values();
        $partisAujourdhui = $presenceJour->filter(fn($p) => $p->heureDepart !== null)->values();

        $absentsAujourdhui = Utilisateur::where('role', 'Employer')
            ->whereNotIn('id', Presence::whereDate('date', $today)->pluck('employerID'))
            ->select('nom')->orderBy('nom')->get();

        // ═══ Stats mensuelles en 2 requêtes max ═══
        $monthlyPresenceStats = Presence::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->selectRaw('SUM(status = ?) as presences, SUM(status = ?) as absences, SUM(HOUR(heureArrivee) > 8 OR (HOUR(heureArrivee) = 8 AND MINUTE(heureArrivee) > 0)) as lates', ['présent', 'Absent'])
            ->first();
        $monthlyPresences = (int) ($monthlyPresenceStats->presences ?? 0);
        $monthlyAbsences = (int) ($monthlyPresenceStats->absences ?? 0);
        $monthlyLates = (int) ($monthlyPresenceStats->lates ?? 0);

        // ═══ Équipes ═══
        $equipes = Superviseur::whereNotNull('equipe')->where('equipe', '!=', '')->distinct()->pluck('equipe')->sort()->values();
        $equipeSelectionnee = request()->query('equipe');
        $employerIdsFiltre = null;
        if ($equipeSelectionnee) {
            $employerIdsFiltre = Employer::where('equipe', $equipeSelectionnee)->pluck('id')->toArray();
        }
        $evolutionEvaluations = \App\Services\EvaluationService::evolutionMensuelle($employerIdsFiltre, 6);
        $statsEvolution = \App\Services\EvaluationService::statsEvolution($evolutionEvaluations);

        // ═══ IDs des superviseurs caisse ═══
        $supCaisseIds = Superviseur::whereIn('type_superviseur', ['directrice', 'secretaire'])
            ->pluck('id', 'type_superviseur');

        // ═══ Graphique CA 7 jours en 2 requêtes (au lieu de 56) ═══
        $caisseChart = Cache::remember('admin_caisse_chart_' . now()->format('Y-m-d-H'), 120, function () use ($supCaisseIds) {
            $labels = [];
            $entrees = [];
            $sorties = [];
            $supIds = $supCaisseIds->values()->toArray();

            if (empty($supIds)) {
                for ($i = 6; $i >= 0; $i--) {
                    $labels[] = now()->subDays($i)->format('d/m');
                    $entrees[] = 0;
                    $sorties[] = 0;
                }
                return ['labels' => $labels, 'entrees' => $entrees, 'sorties' => $sorties];
            }

            // 1 requête pour les entrées (commandes + services par jour)
            $entreesParJour = DB::table('commandes')
                ->selectRaw('date, SUM(montant) as total')
                ->whereIn('superviseur_id', $supIds)
                ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
            $entreesSvc = DB::table('services_fournis')
                ->selectRaw('date, SUM(montant) as total')
                ->whereIn('superviseur_id', $supIds)
                ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();
            // 1 requête pour les sorties (retraits par jour)
            $sortiesParJour = DB::table('retraits')
                ->selectRaw('date, SUM(montant) as total')
                ->whereIn('superviseur_id', $supIds)
                ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray();

            for ($i = 6; $i >= 0; $i--) {
                $jour = now()->subDays($i)->toDateString();
                $labels[] = now()->subDays($i)->format('d/m');
                $e = (float) ($entreesParJour[$jour] ?? 0) + (float) ($entreesSvc[$jour] ?? 0);
                $s = (float) ($sortiesParJour[$jour] ?? 0);
                $entrees[] = $e;
                $sorties[] = $s;
            }
            return ['labels' => $labels, 'entrees' => $entrees, 'sorties' => $sorties];
        });

        // ═══ Caisses du jour en 1 seule requête groupée ═══
        $caisses = [];
        $supLabels = Superviseur::whereIn('type_superviseur', ['directrice', 'secretaire'])
            ->join('utilisateur', 'superviseur.id', '=', 'utilisateur.id')
            ->select('superviseur.id', 'superviseur.type_superviseur', 'utilisateur.nom')
            ->get();
        foreach ($supLabels as $sup) {
            $type = $sup->type_superviseur;
            $cmd = (float) Commande::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $svc = (float) ServiceFourni::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $ret = (float) Retrait::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $caisses[$type] = [
                'nom' => $sup->nom,
                'entrees' => $cmd + $svc,
                'sorties' => $ret,
                'net' => $cmd + $svc - $ret,
            ];
        }

        // ═══ Stock ═══
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

        // ═══ Annulations (30j) — 1 seule requête ═══
        $annulationQuery = Commande::where('statut_paiement', 'annule')
            ->where('date', '>=', now()->subDays(30)->toDateString());
        $annulations = (clone $annulationQuery)
            ->join('utilisateur', 'commandes.superviseur_id', '=', 'utilisateur.id')
            ->select('commandes.*', 'utilisateur.nom as superviseur_nom')
            ->orderByDesc('commandes.date')
            ->limit(15)
            ->get();
        $totalAnnulationsMontant = (clone $annulationQuery)->sum('montant');
        $totalRemboursements = (clone $annulationQuery)->sum('montant_paye');

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
