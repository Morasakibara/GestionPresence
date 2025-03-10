<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Superviseur;
use App\Models\Employer;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = null;
        
        if ($user->role === 'administrateur') {
            // Les administrateurs voient toutes leurs notifications
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else if ($user->role === 'Superviseur') {
            // Les superviseurs ne voient que les notifications des membres de leur équipe
            // et celles où ils sont explicitement notifiés
            $superviseurInfo = Superviseur::where('id', $user->id)->first();
            
            if ($superviseurInfo) {
                $equipe = $superviseurInfo->equipe;
                
                $notifications = $user->notifications()
                    ->where(function($query) use ($equipe) {
                        // Notifications pour les membres de son équipe
                        $query->where('data->is_team_member', true)
                              // Ou notifications spécifiquement pour ce superviseur
                              ->orWhereNull('data->is_team_member');
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
            } else {
                $notifications = $user->notifications()->paginate(10);
            }
        } else {
            // Les employés ne voient que leurs propres notifications
            $notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('notifications.index', compact('notifications'));
    }
    
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        return redirect()->back();
    }
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return redirect()->back();
    }
}