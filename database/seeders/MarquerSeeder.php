<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\Presence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarquerSeeder extends Seeder
{
    /**
     * Lie un échantillon d'employés à leurs présences récentes.
     */
    public function run(): void
    {
        $presences = Presence::where('status', 'présent')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        $rows = [];
        foreach ($presences as $presence) {
            $rows[] = [
                'Empl_id' => $presence->employerID,
                'id' => $presence->id,
                'created_at' => $presence->created_at,
                'updated_at' => $presence->updated_at,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('marquer')->insert($chunk);
        }

        $this->command->info(count($rows) . ' marquages créés.');
    }
}
