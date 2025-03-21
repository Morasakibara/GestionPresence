<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\WorkplaceLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PresenceSeeder extends Seeder
{
    public function run()
    {
        // Récupérer tous les employés
        $employers = Employer::all();

        // Récupérer tous les lieux de travail
        $workplaces = WorkplaceLocation::all();

        if ($employers->isEmpty()) {
            $this->command->error("Aucun employé trouvé. Veuillez exécuter UtilisateurSeeder d'abord.");
            return;
        }

        if ($workplaces->isEmpty()) {
            $this->command->error("Aucun lieu de travail trouvé. Veuillez exécuter WorkplaceLocationSeeder d'abord.");
            return;
        }

        // Pour chaque employé, créer des présences pour les 30 derniers jours
        foreach ($employers as $employer) {
            $supervisor = $employer->superviseur;
            $workplace = $workplaces->random();

            // Ajouter des présences aléatoires sur les 30 derniers jours
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);

                // Sauter les week-ends
                if ($date->isWeekend()) {
                    continue;
                }

                $isPresent = mt_rand(0, 100) < 80; // 80% de chance d'être présent

                // Créer la présence
                Presence::create([
                    'Sup_id' => $supervisor->id,
                    'employerID' => $employer->id,
                    'date' => $date,
                    'created_at' => $date,
                    'updated_at' => $date,
                    'heureArrivee' => $isPresent ? $date->copy()->setTime(mt_rand(7, 9), mt_rand(0, 59), 0) : null,
                    'heureDepart' => $isPresent ? $date->copy()->setTime(mt_rand(17, 18), mt_rand(0, 59), 0) : null,
                    'status' => $isPresent ? 'présent' : 'Absent',
                    'latitude_arrivee' => $isPresent ? $workplace->latitude + (mt_rand(-10, 10) / 1000) : null,
                    'longitude_arrivee' => $isPresent ? $workplace->longitude + (mt_rand(-10, 10) / 1000) : null,
                    'latitude_depart' => $isPresent ? $workplace->latitude + (mt_rand(-10, 10) / 1000) : null,
                    'longitude_depart' => $isPresent ? $workplace->longitude + (mt_rand(-10, 10) / 1000) : null,
                    'localisation_validee_arrivee' => $isPresent,
                    'localisation_validee_depart' => $isPresent,
                    'workplace_location_id' => $workplace->id,
                ]);
            }
        }

        $this->command->info('Présences générées avec succès pour tous les employés sur les 30 derniers jours (hors week-ends).');
    }
}
