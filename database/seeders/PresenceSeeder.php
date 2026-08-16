<?php

namespace Database\Seeders;

use App\Models\Employer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresenceSeeder extends Seeder
{
    /**
     * Génère des présences réalistes sur les ~12 derniers jours ouvrés :
     * ~70 % présent, ~15 % absent, ~15 % arrivée sans départ (en attente).
     */
    public function run(): void
    {
        $lieu = DB::table('workplace_locations')->where('actif', true)->first();
        $lieuId = $lieu->id ?? null;
        $lat = $lieu->latitude ?? 48.856613;
        $lng = $lieu->longitude ?? 2.352222;

        $employers = Employer::all();
        $rows = [];

        foreach ($employers as $emp) {
            // Récupérer les 12 derniers jours ouvrés (hors week-end)
            $jours = [];
            $date = Carbon::now()->subDay();
            while (count($jours) < 12) {
                if (!$date->isWeekend()) {
                    $jours[] = $date->copy();
                }
                $date->subDay();
            }

            foreach ($jours as $jour) {
                $rand = mt_rand(1, 100);
                $heureArrivee = null;
                $heureDepart = null;
                $status = 'Absent';

                if ($rand <= 70) {
                    // Présent : arrivée entre 7h00 et 8h50, départ entre 17h00 et 18h30
                    $heureArrivee = $jour->copy()->setTime(mt_rand(7, 8), mt_rand(0, 50));
                    $heureDepart = $jour->copy()->setTime(mt_rand(17, 18), mt_rand(0, 30));
                    $status = 'présent';
                } elseif ($rand <= 85) {
                    // Absent : aucune heure
                    $status = 'Absent';
                } else {
                    // Arrivée en retard sans départ marqué (en attente)
                    $heureArrivee = $jour->copy()->setTime(9, mt_rand(0, 30));
                    $status = 'en attente';
                }

                $rows[] = [
                    'employerID' => $emp->id,
                    'Sup_id' => $emp->Sup_id,
                    'date' => $jour->toDateString(),
                    'heureArrivee' => $heureArrivee,
                    'heureDepart' => $heureDepart,
                    'status' => $status,
                    'latitude_arrivee' => $heureArrivee ? $lat : null,
                    'longitude_arrivee' => $heureArrivee ? $lng : null,
                    'latitude_depart' => $heureDepart ? $lat : null,
                    'longitude_depart' => $heureDepart ? $lng : null,
                    'localisation_validee_arrivee' => $heureArrivee ? 1 : 0,
                    'localisation_validee_depart' => $heureDepart ? 1 : 0,
                    'workplace_location_id' => $lieuId,
                    'created_at' => $jour,
                    'updated_at' => $jour,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('presence')->insert($chunk);
        }

        $this->command->info(count($rows) . ' présences générées pour ' . $employers->count() . ' employés.');
    }
}
