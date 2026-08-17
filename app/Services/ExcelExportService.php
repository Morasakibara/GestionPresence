<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Génère des exports Excel (.xlsx) aux couleurs de la charte « Le Pharaon » :
 * logo or en en-tête, bandeau titre noir, en-têtes de colonnes noirs avec texte or,
 * zébrures alternées et couleurs sémantiques (vert / orange / rouge).
 */
class ExcelExportService
{
    /** Couleurs de la charte Pharaon. */
    public const NOIR = 'FF080808';
    public const OR = 'FFD39B23';
    public const OR_CLAIR = 'FFE9B533';
    public const OR_TRES_CLAIR = 'FFFACE4A';
    public const BRONZE = 'FFB77F1D';
    public const BRONZE_FONCE = 'FF885910';
    public const FOND = 'FFF8F8F8';
    public const SUCCES = 'FF2E8B57';
    public const INFO = 'FF3B82C4';
    public const ALERTE = 'FFD97706';
    public const DANGER = 'FFD64545';

    /** Chemin du logo Pharaon (stockage public). */
    private const LOGO_PATH = 'public/storage/avatars/logo-pharaon.png';

    private Spreadsheet $spreadsheet;
    private string $titre;
    private string $sousTitre;

    public function __construct(string $titre, string $sousTitre = '')
    {
        $this->spreadsheet = new Spreadsheet();
        $this->titre = $titre;
        $this->sousTitre = $sousTitre;
        $this->spreadsheet->getProperties()
            ->setCreator('Gestion de présence — Le Pharaon')
            ->setTitle($titre);
    }

    /**
     * Ajoute le logo Pharaon dans l'en-tête de la feuille.
     */
    public function ajouterLogo(): void
    {
        $chemin = base_path(self::LOGO_PATH);
        if (!is_file($chemin)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setName('Le Pharaon');
        $drawing->setDescription('Logo Le Pharaon');
        $drawing->setPath($chemin);
        $drawing->setHeight(70);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(8);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());
    }

    /**
     * En-tête de colonnes : fond noir, texte or, gras, centré.
     *
     * @param array<int, string> $colonnes
     */
    public function ecrireEnTete(int $ligne, array $colonnes): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        foreach ($colonnes as $i => $libelle) {
            $cellule = $sheet->getCell([$i + 1, $ligne]);
            $cellule->setValue($libelle);
        }

        $derniereColonne = Coordinate::stringFromColumnIndex(count($colonnes));
        $sheet->getStyle('A' . $ligne . ':' . $derniereColonne . $ligne)
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => self::OR_TRES_CLAIR],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => self::NOIR],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        $sheet->getRowDimension($ligne)->setRowHeight(22);
    }

    /**
     * Écrit une ligne de données avec zébrures alternées.
     *
     * @param array<int, scalar|null> $valeurs
     */
    public function ecrireLigne(int $ligne, array $valeurs): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $i = 1;
        foreach ($valeurs as $valeur) {
            $sheet->getCell([$i, $ligne])->setValue($valeur ?? '');
            $i++;
        }

        // Zébrures alternées
        if ($ligne % 2 === 0) {
            $derniereColonne = Coordinate::stringFromColumnIndex(count($valeurs));
            $sheet->getStyle('A' . $ligne . ':' . $derniereColonne . $ligne)
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFF1EDE0'));
        }
    }

    /**
     * Applique une couleur de texte à une plage (ex. note d'évaluation).
     */
    public function colorerCellule(int $ligne, int $colonne, string $couleur): void
    {
        $this->spreadsheet->getActiveSheet()
            ->getCell([$colonne, $ligne])
            ->getStyle()
            ->getFont()
            ->getColor()
            ->setARGB($couleur);
    }

    /**
     * Largeur automatique des colonnes + cadres légers + en-tête de titre.
     *
     * @param array<int, string> $colonnes
     */
    public function finaliser(int $ligneMax, array $colonnes): void
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Largeurs automatiques
        foreach ($colonnes as $i => $libelle) {
            $lettre = Coordinate::stringFromColumnIndex($i + 1);
            $largeur = mb_strlen($libelle) * 2.4 + 6;
            $sheet->getColumnDimension($lettre)->setWidth(max(12, min(45, $largeur)));
        }

        // Cadres légers sur toutes les données
        $derniereColonne = Coordinate::stringFromColumnIndex(count($colonnes));
        if ($ligneMax >= 2) {
            $sheet->getStyle('A2:' . $derniereColonne . $ligneMax)
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setARGB('FFE7E7E7');
        }

        // Texte renvoyé à la ligne pour les colonnes larges (rendements, commentaires)
        $derniereColonneIndex = count($colonnes);
        $sheet->getStyle('A2:' . $derniereColonne . $ligneMax)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
    }

    /**
     * Retourne le contenu binaire du fichier .xlsx.
     */
    public function contenu(): string
    {
        $writer = new Xlsx($this->spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    /**
     * Raccourci : crée un export complet (logo + titre + en-tête + lignes).
     *
     * @param array<int, string> $colonnes
     * @param array<int, array<int, scalar|null>> $lignes
     */
    public static function creer(string $titre, string $sousTitre, array $colonnes, array $lignes): self
    {
        $service = new self($titre, $sousTitre);
        $service->ajouterLogo();

        $sheet = $service->spreadsheet->getActiveSheet();

        // Bandeau titre (ligne 2, sous le logo)
        $sheet->setCellValue('A2', $titre);
        $sheet->getStyle('A2')->getFont()->applyFromArray([
            'bold' => true,
            'size' => 16,
            'color' => ['argb' => self::NOIR],
        ]);

        if ($sousTitre !== '') {
            $sheet->setCellValue('A3', $sousTitre);
            $sheet->getStyle('A3')->getFont()->applyFromArray([
                'size' => 10,
                'color' => ['argb' => 'FF555555'],
            ]);
        }

        // Ligne d'en-tête : après le logo (4) + titre (2) + sous-titre (1) = 5
        $ligneEnTete = $sousTitre !== '' ? 5 : 4;
        $service->ecrireEnTete($ligneEnTete, $colonnes);

        $ligne = $ligneEnTete + 1;
        foreach ($lignes as $donnees) {
            $service->ecrireLigne($ligne, $donnees);
            $ligne++;
        }

        $service->finaliser($ligne - 1, $colonnes);

        return $service;
    }

    public function getSpreadsheet(): Spreadsheet
    {
        return $this->spreadsheet;
    }
}
