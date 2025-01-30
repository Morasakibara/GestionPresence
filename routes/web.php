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
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PreController;

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
/*Route::get('/test-db', function () {
    return User::all();
});*/

//Enregistrement de l'Admin
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

//Authentification de l'admin et du superviseur
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('/select-role', [LoginController::class, 'selectRole'])->name('select-role');
Route::get('/Auth/role_section',[LoginController::class,'showRoleSelectionModal'])->name('auth.role_selection');

//Deconnexion de l'admin et superviseur
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('logout', [LoginController::class, 'logout'])->name('logouts');


//ici on a les route permettant de gerer les fonctioanalite de l'Admin
Route::middleware(['isAdmin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/add-employee', [AdminController::class, 'showAddEmployeeForm'])->name('admin.addEmployee');
    Route::post('/admin/store-employee', [AdminController::class, 'storeEmployee'])->name('admin.storeEmployee');
    Route::get('/admin/delete-employee', [AdminController::class, 'showDeleteEmployeeForm'])->name('admin.deleteEmployee');
    Route::get('/admin/generate-report', [AdminController::class, 'showGenerateReportForm'])->name('admin.generateReport');
    Route::post('/admin/generate-report', [AdminController::class, 'generateReport'])->name('admin.generateReport');
    Route::post('/admin/report',[AdminController::class, 'exportReport'])->name('admin.exportReport');
    Route::get('/admin/calculate-presence', [AdminController::class, 'showEmployeeList'])->name('admin.showEmployeeList');
    Route::post('/admin/delete-employee', [App\Http\Controllers\AdminController::class, 'deleteEmployee'])->name('admin.deleteEmployee');
    Route::get('/admin/showEmployee',[AdminController::class,'showEmployee'])->name('admin.showEmployee');
});

//route de deconnexion
//Auth::routes();

//Route permettant de gerer les fonctionnalite du superviseur
/*Route::middleware(['isSuperviseur'])->group(function (){
   Route::get('/superviseur/supdashboard',[SuperviseurController::class,'Supdashboard'])->name('superviseur.supdashboard');
        Route::get('/superviseur/followPresence',[SuperviseurController::class,'showFollowPresence'])->name('superviseur.showFollowPresence');
        Route::get('/sperviseur/generateReport',[SuperviseurController::class,'generateReport'])->name('superviseur.generateReport2');
        Route::get('/superviseur/followPresence',[SuperviseurController::class,'followPresence'])->name('superviseur.followPresence');
        Route::get('/getUserDetails/{id}',[SuperviseurController::class,'getUserDetails'])->name('superviseur.getUserDetails');
        Route::get('/viewUser/{id}', [SuperviseurController::class,'viewUser'])->name('viewUser');
        Route::get('/showUser/{id}', [SuperviseurController::class, 'showUser'])->name('user.show');
        Route::get('/superviseur/generateReport',[SuperviseurController::class,'generateReport'])->name('generateReport');
        Route::get('/superviseur/exportPDF',[SuperviseurController::class,'exportPDF'])->name('export.pdf');
        Route::get('/superviseur/showAddMember',[SuperviseurController::class,'showAddMember'])->name('superviseur.showAddMember');
        Route::post('/superviseur/addMemberToTeam/{id}',[SuperviseurController::class,'addMemberToTeam'])->name('superviseur.addMemberToTeam');
        Route::get('/superviseur/add-member', [SuperviseurController::class, 'showAddMemberForm'])->name('superviseur.showAddMemberForm');
}); */


//Route pour les fonctionalite de l'utilisateur
/*Route::middleware(['auth', 'employee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::post('/employee/mark-arrival', [PresenceController::class, 'markArrival'])->name('employee.mark-arrival');
    Route::post('/employee/mark-departure', [PresenceController::class, 'markDeparture'])->name('employee.mark-departure');
    Route::get('/employee/profile', [EmployeeController::class, 'profile'])->name('employee.profile');
    Route::put('/employee/profile', [EmployeeController::class, 'updateProfile'])->name('employee.update-profile');
    Route::get('employee/presence-report', [EmployeeController::class, 'presenceReport'])->name('employee.presence-report');
});*/


//test

/*Route::middleware(['auth','check.role.network'])->group(function () {
    Route::get('/user/dashboard', [UtilisateurController::class,'dashboard'])->name('user.dashboard');
    Route::get('/presence/index', [PreController::class,'index'])->name('presence.index');
    Route::post('/mark-arrival', [PreController::class,'markArrival'])->name('presence.arrival');
    Route::post('/mark-departure',[PreController::class,'markDeparture'])->name('presence.departure');
    Route::put('/user/update', ['UtilisateurController@update'])->name('user.update');
    Route::get('/user/profile', [UtilisateurController::class,'profile'])->name('user.profile');
    Route::get('/user/presence-report', [UtilisateurController::class,'presenceReport'])->name('user.presence.report');
    Route::prefix('Employer')->group(function(){
        Route::get('/user/dashboard', [UtilisateurController::class,'dashboard'])->name('user.dashboard');
        Route::get('/presence/index', [PreController::class,'index'])->name('presence.index');
        Route::post('/mark-arrival', [PreController::class,'markArrival'])->name('presence.arrival');
        Route::post('/mark-departure',[PreController::class,'markDeparture'])->name('presence.departure');
        Route::put('/user/update', ['UtilisateurController@update'])->name('user.update');
        Route::get('/user/profile', [UtilisateurController::class,'profile'])->name('user.profile');
        Route::get('/user/presence-report', [UtilisateurController::class,'presenceReport'])->name('user.presence.report');
    });
    Route::prefix('superviseur')->group(function(){
        Route::get('/superviseur/supdashboard',[SuperviseurController::class,'Supdashboard'])->name('superviseur.supdashboard');
        Route::get('/superviseur/followPresence',[SuperviseurController::class,'showFollowPresence'])->name('superviseur.showFollowPresence');
        Route::get('/sperviseur/generateReport',[SuperviseurController::class,'generateReport'])->name('superviseur.generateReport2');
        Route::get('/superviseur/followPresence',[SuperviseurController::class,'followPresence'])->name('superviseur.followPresence');
        Route::get('/getUserDetails/{id}',[SuperviseurController::class,'getUserDetails'])->name('superviseur.getUserDetails');
        Route::get('/viewUser/{id}', [SuperviseurController::class,'viewUser'])->name('viewUser');
        Route::get('/showUser/{id}', [SuperviseurController::class, 'showUser'])->name('user.show');
        Route::get('/superviseur/generateReport',[SuperviseurController::class,'generateReport'])->name('generateReport');
        Route::get('/superviseur/exportPDF',[SuperviseurController::class,'exportPDF'])->name('export.pdf');
        Route::get('/superviseur/showAddMember',[SuperviseurController::class,'showAddMember'])->name('superviseur.showAddMember');
        Route::post('/superviseur/addMemberToTeam/{id}',[SuperviseurController::class,'addMemberToTeam'])->name('superviseur.addMemberToTeam');
        Route::get('/superviseur/add-member', [SuperviseurController::class, 'showAddMemberForm'])->name('superviseur.showAddMemberForm');
    });
}); */

Route::middleware(['auth', 'check.role.and.network'])->group(function () {
    // Utilisateur routes
    Route::prefix('user')->group(function () {
        Route::get('/user/dashboard', [UtilisateurController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/user/profile', [UtilisateurController::class, 'profile'])->name('user.profile');
        Route::put('/user/update', [UtilisateurController::class, 'update'])->name('user.update');
        Route::get('/user/presence-report', [UtilisateurController::class, 'presenceReport'])->name('user.presence.report');
    });

    // Superviseur routes
    Route::prefix('superviseur')->group(function () {
        Route::get('/superviseur/supdashboard', [SuperviseurController::class, 'Supdashboard'])->name('superviseur.supdashboard');
        Route::get('/superviseur/followPresence', [SuperviseurController::class, 'showFollowPresence'])->name('superviseur.showFollowPresence');
        Route::get('/superviseur/generateReport', [SuperviseurController::class, 'generateReport'])->name('superviseur.generateReport');
        Route::get('/getUserDetails/{id}', [SuperviseurController::class, 'getUserDetails'])->name('superviseur.getUserDetails');
        Route::get('/viewUser/{id}', [SuperviseurController::class, 'viewUser'])->name('viewUser');
        Route::get('/showUser/{id}', [SuperviseurController::class, 'showUser'])->name('user.show');
        Route::get('/superviseur/exportPDF', [SuperviseurController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/superviseur/showAddMember', [SuperviseurController::class, 'showAddMember'])->name('superviseur.showAddMember');
        Route::post('/superviseur/addMemberToTeam/{id}', [SuperviseurController::class, 'addMemberToTeam'])->name('superviseur.addMemberToTeam');
        Route::get('/superviseur/add-member', [SuperviseurController::class, 'showAddMemberForm'])->name('superviseur.showAddMemberForm');
    });

    // Shared routes for both user types
    Route::get('/presence', [PreController::class, 'index'])->name('presence.index');
    Route::post('/mark-arrival', [PreController::class, 'markArrival'])->name('presence.arrival');
    Route::post('/mark-departure', [PreController::class, 'markDeparture'])->name('presence.departure');
});
