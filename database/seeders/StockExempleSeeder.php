<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Superviseur;
use App\Models\StockTshirt;
use App\Models\StockPapier;

class StockExempleSeeder extends Seeder
{
    public function run(): void
    {
        $gestionnaire = Superviseur::where('type_superviseur', 'gestionnaire_stock')->first();
        if (!$gestionnaire) {
            $this->command->warn('  ⚠ Aucun gestionnaire de stock trouvé. Lancez d\'abord SuperviseursSpecialisesSeeder.');
            return;
        }

        // T-Shirts
        $tshirts = [
            ['couleur' => 'Noir', 'taille' => 'S', 'quantite' => 15, 'seuil_alerte' => 5],
            ['couleur' => 'Noir', 'taille' => 'M', 'quantite' => 22, 'seuil_alerte' => 5],
            ['couleur' => 'Noir', 'taille' => 'L', 'quantite' => 18, 'seuil_alerte' => 5],
            ['couleur' => 'Noir', 'taille' => 'XL', 'quantite' => 10, 'seuil_alerte' => 5],
            ['couleur' => 'Blanc', 'taille' => 'S', 'quantite' => 8, 'seuil_alerte' => 5],
            ['couleur' => 'Blanc', 'taille' => 'M', 'quantite' => 12, 'seuil_alerte' => 5],
            ['couleur' => 'Blanc', 'taille' => 'L', 'quantite' => 3, 'seuil_alerte' => 5],
            ['couleur' => 'Gris', 'taille' => 'M', 'quantite' => 6, 'seuil_alerte' => 5],
            ['couleur' => 'Gris', 'taille' => 'L', 'quantite' => 2, 'seuil_alerte' => 5],
        ];

        foreach ($tshirts as $t) {
            StockTshirt::create(array_merge($t, ['superviseur_id' => $gestionnaire->id]));
        }
        $this->command->info('  ✓ ' . count($tshirts) . ' T-shirts créés');

        // Papier
        $papiers = [
            ['imprimante' => 'HP LaserJet Pro M404', 'metres_restants' => 120.5, 'metres_total' => 305, 'seuil_alerte' => 50],
            ['imprimante' => 'Canon PIXMA G3420', 'metres_restants' => 35.2, 'metres_total' => 200, 'seuil_alerte' => 50],
            ['imprimante' => 'Epson EcoTank L3250', 'metres_restants' => 80.0, 'metres_total' => 200, 'seuil_alerte' => 50],
        ];

        foreach ($papiers as $p) {
            StockPapier::create(array_merge($p, ['superviseur_id' => $gestionnaire->id]));
        }
        $this->command->info('  ✓ ' . count($papiers) . ' stocks papier créés');
    }
}
