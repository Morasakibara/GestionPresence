<?php

use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperviseurController;
use App\Http\Controllers\UtilisateurController;
use App\Models\Administrateur;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Http\Controllers\PreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GeoLocationController;
use App\Http\Controllers\WorkplaceLocationController;

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

//Enregistrement de l'Admin
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

//Authentification de l'admin et du superviseur
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('/select-role', [LoginController::class, 'selectRole'])->name('select-role');
Route::get('/Auth/role_selection',[LoginController::class,'showRoleSelectionModal'])->name('auth.role_selection');

//Deconnexion de l'admin et superviseur
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('logout', [LoginController::class, 'logout'])->name('logouts');


//ici on a les route permettant de gerer les fonctioanalite de l'Admin
Route::middleware(['isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('add-employee', [AdminController::class, 'showAddEmployeeForm'])->name('addEmployee');
    Route::post('/store-employee', [AdminController::class, 'storeEmployee'])->name('storeEmployee');
    Route::get('/delete-employee', [AdminController::class, 'showDeleteEmployeeForm'])->name('deleteEmployee');
    Route::get('/generate-report', [AdminController::class, 'showGenerateReportForm'])->name('generateReport');
    Route::post('/generate-report', [AdminController::class, 'generateReport']);
    Route::post('/report',[AdminController::class, 'exportReport'])->name('exportReport');
    Route::post('/evaluations', [AdminController::class, 'storeEvaluation'])->name('storeEvaluation');
    Route::get('/evaluations/export', [AdminController::class, 'exportEvaluationsCsv'])->name('evaluations.export');
    Route::get('/employe/{id}/bulletin', [AdminController::class, 'evaluationBulletin'])->name('evaluation.bulletin');
    Route::get('/calculate-presence', [AdminController::class, 'showEmployeeList'])->name('showEmployeeList');
    Route::post('/delete-employee', [App\Http\Controllers\AdminController::class, 'deleteEmployee']);
    Route::post('/update-profile', [App\Http\Controllers\AdminController::class, 'updateProfile'])->name('updateProfile');
    Route::get('/update-profile', [App\Http\Controllers\AdminController::class, 'showProfileForm'])->name('showProfile');
    Route::post('delete-employee-from-list', [AdminController::class, 'deleteEmployeeFromList'])->name('deleteEmployee.fromList');
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
    });

    // Superviseur routes
    Route::prefix('superviseur')->group(function () {
        Route::get('/supdashboard', [SuperviseurController::class, 'Supdashboard'])->name('superviseur.supdashboard');
        Route::get('/followPresence', [SuperviseurController::class, 'showFollowPresence'])->name('superviseur.showFollowPresence');
        Route::get('/generateReport2', [SuperviseurController::class, 'generateReport'])->name('superviseur.generateReport2');
        Route::post('/evaluations', [SuperviseurController::class, 'storeEvaluation'])->name('superviseur.storeEvaluation');
        Route::get('/rendements', [SuperviseurController::class, 'teamRendements'])->name('superviseur.rendements');
        Route::get('/rendements/export', [SuperviseurController::class, 'exportTeamRendementsCsv'])->name('superviseur.rendements.export');
        Route::get('/employe/{id}/bulletin', [SuperviseurController::class, 'evaluationBulletin'])->name('superviseur.evaluation.bulletin');
        Route::get('/getUserDetails/{id}', [SuperviseurController::class, 'getUserDetails'])->name('superviseur.getUserDetails');
        Route::get('/viewUser/{id}', [SuperviseurController::class, 'viewUser'])->name('viewUser');
        Route::get('/exportPDF', [SuperviseurController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/showAddMember', [SuperviseurController::class, 'showAddMember'])->name('superviseur.showAddMember');
        Route::post('/addMemberToTeam/{id}', [SuperviseurController::class, 'addMemberToTeam'])->name('superviseur.addMemberToTeam');
        Route::get('/add-member', [SuperviseurController::class, 'showAddMemberForm'])->name('superviseur.showAddMemberForm');
        Route::post('remove-member/{id}', [SuperviseurController::class, 'removeMemberFromTeam'])->name('superviseur.removeMemberFromTeam');
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
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});
