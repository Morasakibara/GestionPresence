<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminFinanceController;
use App\Http\Controllers\SuperviseurController;
use App\Http\Controllers\SuperviseurEvaluationController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Administrateur;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Http\Controllers\PreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GeoLocationController;
use App\Http\Controllers\WorkplaceLocationController;
use App\Http\Controllers\DirectriceController;
use App\Http\Controllers\SecretaireController;
use App\Http\Controllers\GestionnaireStockController;
use App\Http\Controllers\CongeController;

//use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [TestController::class,'index'])->name('index');
Route::post('/verify-registration-access', [App\Http\Controllers\TestController::class, 'verifyRegistrationAccess'])->name('verify.registration.access');


Route::middleware('registration.access')->group(function () {
    Route::get('/register', [TestController::class, 'showRegistrationForm'])->name('register');
});

//Enregistrement de l'Admin (rate-limité : 3/heure)
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->middleware('throttle:register');

//Authentification de l'admin et du superviseur (rate-limité : 5/minute)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/select-role', [LoginController::class, 'selectRole'])->name('select-role');
Route::get('/Auth/role_selection',[LoginController::class,'showRoleSelectionModal'])->name('auth.role_selection');

//Deconnexion de l'admin et superviseur
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('logout', [LoginController::class, 'logout'])->name('logouts');


//ici on a les route permettant de gerer les fonctionalités de l'Admin
Route::middleware(['isAdmin', 'role:Administrateur'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('add-employee', [AdminEmployeeController::class, 'showAddEmployeeForm'])->name('addEmployee');
    Route::post('/store-employee', [AdminEmployeeController::class, 'storeEmployee'])->name('storeEmployee');
    Route::get('/delete-employee', [AdminEmployeeController::class, 'showDeleteEmployeeForm'])->name('deleteEmployee');
    Route::get('/generate-report', [AdminReportController::class, 'showGenerateReportForm'])->name('generateReport');
    Route::post('/generate-report', [AdminReportController::class, 'generateReport']);
    // Rediriger l'ancien exportReport vers generateReport avec format PDF
    Route::post('/report', function (\Illuminate\Http\Request $request) {
        $request->merge(['export_format' => 'pdf']);
        return app(AdminReportController::class)->generateReport($request);
    })->name('exportReport');
    Route::post('/evaluations', [AdminReportController::class, 'storeEvaluation'])->name('storeEvaluation');
    Route::get('/evaluations/export', [AdminReportController::class, 'exportEvaluationsCsv'])->name('evaluations.export');
    Route::get('/evaluations/export/pdf', [AdminReportController::class, 'exportEvaluationsPdf'])->name('evaluations.export.pdf');
    Route::get('/evaluations/evolution/export', [AdminReportController::class, 'exportEvolutionEvaluationsCsv'])->name('evaluations.evolution.export');
    Route::get('/evaluations', function () {
        return redirect()->route('admin.generateReport');
    })->name('admin.evaluations.get');
    Route::get('/employe/{id}/bulletin', [AdminReportController::class, 'evaluationBulletin'])->name('evaluation.bulletin');
    Route::get('/calculate-presence', [AdminEmployeeController::class, 'showEmployeeList'])->name('showEmployeeList');
    Route::post('/delete-employee', [AdminEmployeeController::class, 'deleteEmployee']);
    Route::post('/update-profile', [AdminController::class, 'updateProfile'])->name('updateProfile');
    Route::get('/update-profile', [AdminController::class, 'showProfileForm'])->name('showProfile');
    Route::post('delete-employee-from-list', [AdminEmployeeController::class, 'deleteEmployeeFromList'])->name('deleteEmployee.fromList');
    Route::resource('workplace-locations', WorkplaceLocationController::class);
  });



Route::middleware(['auth', \App\Http\Middleware\CheckUserRoleAndNetwork::class])->group(function () {
    // Utilisateur routes
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', [UtilisateurController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/profile', [UtilisateurController::class, 'profile'])->name('user.profile');
        Route::put('/update', [UtilisateurController::class, 'update'])->name('user.update');
        Route::get('/presence-report', [UtilisateurController::class, 'presenceReport'])->name('user.presence.report');
        Route::get('/rendement', [UtilisateurController::class, 'rendementReport'])->name('user.rendement');
        Route::post('/check-location', [GeoLocationController::class, 'checkLocation'])->name('check.location');

        // Congés & Absences
        Route::get('/conges', function () {
            return view('user.conges');
        })->name('user.conges');
        Route::post('/conges', [CongeController::class, 'store'])->name('user.conges.store');
    });

    // Superviseur routes
    Route::prefix('superviseur')->group(function () {
        Route::get('/supdashboard', [SuperviseurController::class, 'Supdashboard'])->name('superviseur.supdashboard');
        Route::get('/followPresence', [SuperviseurController::class, 'showFollowPresence'])->name('superviseur.showFollowPresence');
        Route::get('/generateReport2', [SuperviseurEvaluationController::class, 'generateReport'])->name('superviseur.generateReport2');
        Route::post('/evaluations', [SuperviseurEvaluationController::class, 'storeEvaluation'])->name('superviseur.storeEvaluation');
        Route::get('/evaluations/evolution/export', [SuperviseurEvaluationController::class, 'exportEvolutionEvaluationsCsv'])->name('superviseur.evaluations.evolution.export');
        Route::get('/evaluations/export/pdf', [SuperviseurEvaluationController::class, 'exportEvaluationsPdf'])->name('superviseur.evaluations.export.pdf');
        Route::get('/comparatif/export', [SuperviseurEvaluationController::class, 'exportComparatifPdf'])->name('superviseur.comparatif.export');
        // Redirection propre si un GET arrive sur /superviseur/evaluations (ex. lien direct)
        Route::get('/evaluations', function () {
            return redirect()->route('superviseur.generateReport2');
        })->name('superviseur.evaluations.get');
        Route::get('/rendements', [SuperviseurEvaluationController::class, 'teamRendements'])->name('superviseur.rendements');
        Route::get('/rendements/export', [SuperviseurEvaluationController::class, 'exportTeamRendementsCsv'])->name('superviseur.rendements.export');
        Route::get('/employe/{id}/bulletin', [SuperviseurEvaluationController::class, 'evaluationBulletin'])->name('superviseur.evaluation.bulletin');
        Route::get('/getUserDetails/{id}', [SuperviseurController::class, 'getUserDetails'])->name('superviseur.getUserDetails');
        Route::get('/viewUser/{id}', [SuperviseurController::class, 'viewUser'])->name('viewUser');
        Route::get('/exportPDF', [SuperviseurEvaluationController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/showAddMember', [SuperviseurController::class, 'showAddMember'])->name('superviseur.showAddMember');
        Route::post('/addMemberToTeam/{id}', [SuperviseurController::class, 'addMemberToTeam'])->name('superviseur.addMemberToTeam');
        Route::get('/add-member', [SuperviseurController::class, 'showAddMemberForm'])->name('superviseur.showAddMemberForm');
        Route::post('remove-member/{id}', [SuperviseurController::class, 'removeMemberFromTeam'])->name('superviseur.removeMemberFromTeam');

        // Congés & Absences
        Route::get('/conges', [CongeController::class, 'index'])->name('superviseur.conges.index');
        Route::patch('/conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('superviseur.conges.approuver');
        Route::patch('/conges/{conge}/refuser', [CongeController::class, 'refuser'])->name('superviseur.conges.refuser');
    });

    // ===================== DIRECTRICE (Superviseur1) =====================
    Route::prefix('directrice')->name('directrice.')->group(function () {
        Route::get('/dashboard', [DirectriceController::class, 'dashboard'])->name('dashboard');
        Route::get('/commandes', [DirectriceController::class, 'showCommandes'])->name('commandes');
        Route::post('/commandes', [DirectriceController::class, 'storeCommande'])->name('commandes.store');
        Route::get('/commandes/{id}/edit', [DirectriceController::class, 'editCommande'])->name('commandes.edit');
        Route::put('/commandes/{id}', [DirectriceController::class, 'updateCommande'])->name('commandes.update');
        Route::delete('/commandes/{id}', [DirectriceController::class, 'destroyCommande'])->name('commandes.destroy');
        Route::get('/services', [DirectriceController::class, 'showServices'])->name('services');
        Route::post('/services', [DirectriceController::class, 'storeService'])->name('services.store');
        Route::get('/services/{id}/edit', [DirectriceController::class, 'editService'])->name('services.edit');
        Route::put('/services/{id}', [DirectriceController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{id}', [DirectriceController::class, 'destroyService'])->name('services.destroy');
        Route::get('/retraits', [DirectriceController::class, 'showRetraits'])->name('retraits');
        Route::post('/retraits', [DirectriceController::class, 'storeRetrait'])->name('retraits.store');
        Route::get('/rapport', [DirectriceController::class, 'rapport'])->name('rapport');
        Route::get('/rapport/export', [DirectriceController::class, 'exportCsv'])->name('rapport.export');
        Route::get('/rapport/export/html', [DirectriceController::class, 'exportHtml'])->name('rapport.exportHtml');
        Route::get('/rapport/export/pdf', [DirectriceController::class, 'exportPdf'])->name('rapport.exportPdf');
        Route::post('/encaisser-reste', [DirectriceController::class, 'encaisserReste'])->name('encaisserReste');
    });

    // ===================== SECRETAIRE (SuperviseurS) =====================
    Route::prefix('secretaire')->name('secretaire.')->group(function () {
        Route::get('/dashboard', [SecretaireController::class, 'dashboard'])->name('dashboard');
        Route::get('/commandes', [SecretaireController::class, 'showCommandes'])->name('commandes');
        Route::post('/commandes', [SecretaireController::class, 'storeCommande'])->name('commandes.store');
        Route::get('/commandes/{id}/edit', [SecretaireController::class, 'editCommande'])->name('commandes.edit');
        Route::put('/commandes/{id}', [SecretaireController::class, 'updateCommande'])->name('commandes.update');
        Route::delete('/commandes/{id}', [SecretaireController::class, 'destroyCommande'])->name('commandes.destroy');
        Route::get('/services', [SecretaireController::class, 'showServices'])->name('services');
        Route::post('/services', [SecretaireController::class, 'storeService'])->name('services.store');
        Route::get('/services/{id}/edit', [SecretaireController::class, 'editService'])->name('services.edit');
        Route::put('/services/{id}', [SecretaireController::class, 'updateService'])->name('services.update');
        Route::delete('/services/{id}', [SecretaireController::class, 'destroyService'])->name('services.destroy');
        Route::get('/retraits', [SecretaireController::class, 'showRetraits'])->name('retraits');
        Route::post('/retraits', [SecretaireController::class, 'storeRetrait'])->name('retraits.store');
        Route::get('/rapport', [SecretaireController::class, 'rapport'])->name('rapport');
        Route::get('/rapport/export', [SecretaireController::class, 'exportCsv'])->name('rapport.export');
        Route::get('/rapport/export/html', [SecretaireController::class, 'exportHtml'])->name('rapport.exportHtml');
        Route::get('/rapport/export/pdf', [SecretaireController::class, 'exportPdf'])->name('rapport.exportPdf');
        Route::post('/encaisser-reste', [SecretaireController::class, 'encaisserReste'])->name('encaisserReste');
    });

    // ===================== GESTIONNAIRE DE STOCK (Superviseur3) =====================
    Route::prefix('gestionnaire')->name('gestionnaire.')->group(function () {
        Route::get('/dashboard', [GestionnaireStockController::class, 'dashboard'])->name('dashboard');
        Route::get('/tshirts', [GestionnaireStockController::class, 'showTshirts'])->name('tshirts');
        Route::post('/tshirts', [GestionnaireStockController::class, 'storeTshirt'])->name('tshirts.store');
        Route::put('/tshirts/{id}', [GestionnaireStockController::class, 'updateTshirt'])->name('tshirts.update');
        Route::delete('/tshirts/{id}', [GestionnaireStockController::class, 'destroyTshirt'])->name('tshirts.destroy');
        Route::get('/papier', [GestionnaireStockController::class, 'showPapier'])->name('papier');
        Route::post('/papier', [GestionnaireStockController::class, 'storePapier'])->name('papier.store');
        Route::put('/papier/{id}', [GestionnaireStockController::class, 'updatePapier'])->name('papier.update');
        Route::delete('/papier/{id}', [GestionnaireStockController::class, 'destroyPapier'])->name('papier.destroy');
    });

    // Shared routes for both user types
    Route::get('/presence', [PreController::class, 'index'])->name('presence.index');
    Route::post('/mark-arrival', [PreController::class, 'markArrival'])->name('presence.arrival');
    Route::post('/mark-departure', [PreController::class, 'markDeparture'])->name('presence.departure');
});

//route pour permettre au superviseur de pouvoir changer de rôle

Route::get('/role-switch', function() {
    session()->forget('current_role');
    return redirect('/Auth/role_selection');
})->name('role.switch');

// Routes de notification
// ===================== ADMIN DASHBOARD EXTENSIONS =====================
Route::middleware(['isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/caisse', [AdminFinanceController::class, 'etatCaisse'])->name('caisse');
    Route::get('/stock', [AdminFinanceController::class, 'etatStock'])->name('stock');
    Route::get('/paiements', [AdminFinanceController::class, 'paiementStats'])->name('paiements');
    Route::get('/audit-logs', [AdminFinanceController::class, 'auditLogs'])->name('auditLogs');
    Route::get('/metrics', [AdminController::class, 'metrics'])->name('metrics');
    Route::get('/conges', [App\Http\Controllers\AdminCongeController::class, 'index'])->name('conges');
    Route::get('/conges/planning/pdf', function (\Illuminate\Http\Request $request) {
        $mois = $request->input('mois', now()->format('Y-m'));
        return \App\Services\CongePdfService::planning($mois)->download('planning_conges_' . $mois . '.pdf');
    })->name('conges.planning.pdf');
    Route::get('/conges/resume/pdf', function () {
        return \App\Services\CongePdfService::resume()->download('resume_conges.pdf');
    })->name('conges.resume.pdf');
    Route::get('/conges/calendar/events', [App\Http\Controllers\AdminCongeController::class, 'calendarEvents'])->name('conges.calendar.events');
    Route::get('/conges/export/pivot', function (\Illuminate\Http\Request $request) {
        return \App\Services\CongeExportService::pivot($request->input('mois'));
    })->name('conges.export.pivot');
    Route::get('/conges/export/liste', function (\Illuminate\Http\Request $request) {
        return \App\Services\CongeExportService::liste($request->input('statut'), $request->input('equipe'));
    })->name('conges.export.liste');
});

Route::middleware('auth')->group(function () {
    Route::get('/api/presence-alerts', [App\Http\Controllers\PresenceAlertController::class, 'check'])->name('api.presence.alerts');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::get('/api/notifications/check', [NotificationController::class, 'check'])->name('notifications.check');
});
