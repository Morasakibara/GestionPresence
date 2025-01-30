<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use App\Models\Administrateur;
use App\Models\Superviseur;
use App\Models\Employer;
use Illuminate\Database\Seeder;

class UtilisateurSeeder extends Seeder
{
    
    public function run(): void
    {
        $utilisateurs=Utilisateur::factory()->count(40)->create();
        foreach($utilisateurs as $user){
            if($user->role == 'Administrateur'){
                Administrateur::factory()->create(['id' => $user->id]);
            }elseif($user->role == 'Superviseur'){
                Superviseur::factory()->create(['id'=> $user->id]);
            }elseif($user->role == 'Employer'){
                $superviseur = Superviseur::inRandomOrder()->first();
                if($superviseur){
                    Employer::factory()->create([
                        'id' =>$user->id,
                        'Sup_id' =>$superviseur->id,
                    ]);
                }else{
                    $superviseur = Superviseur::factory()->create();
                    Employer::factory()->create([
                        'id' =>$user->id,
                        'Sup_id' => $superviseur->id,
                    ]);
                }
            }
        }

        echo '50 utilisateur ajourter';
    }
}
