<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index de performance pour les pages les plus consultées
 * (tableaux de bord admin et superviseur, caisse, évaluations, alertes de présence).
 *
 * Chaque index n'est créé que s'il est absent : la migration reste rejouable
 * sur une base déjà partiellement indexée.
 */
return new class extends Migration
{
    /**
     * Index à ajouter : [table, colonnes, nom de l'index].
     *
     * @var array<int, array{0: string, 1: array<int, string>, 2: string}>
     */
    private array $indexes = [
        // Caisse : agrégations et rapports par superviseur et par jour
        ['commandes', ['superviseur_id', 'date'], 'commandes_superviseur_date_idx'],
        ['commandes', ['statut_paiement', 'date'], 'commandes_statut_date_idx'],
        ['services_fournis', ['superviseur_id', 'date'], 'services_fournis_superviseur_date_idx'],
        ['retraits', ['superviseur_id', 'date'], 'retraits_superviseur_date_idx'],
        ['retraits', ['date'], 'retraits_date_idx'],

        // Présences : alertes temps réel (arrivées / départs depuis une heure donnée)
        ['presence', ['date', 'heureArrivee'], 'presence_date_arrivee_idx'],
        ['presence', ['date', 'heureDepart'], 'presence_date_depart_idx'],

        // Effectifs, filtres d'équipe et routage par type de superviseur
        ['utilisateur', ['role', 'deleted_at'], 'utilisateur_role_deleted_idx'],
        ['employer', ['equipe'], 'employer_equipe_idx'],
        ['superviseur', ['type_superviseur'], 'superviseur_type_idx'],
        ['superviseur', ['equipe'], 'superviseur_equipe_idx'],

        // Congés : calcul des absences justifiées par période
        ['conges', ['employer_id', 'statut', 'date_debut'], 'conges_employer_statut_debut_idx'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (! Schema::hasTable($table) || Schema::hasIndex($table, $columns)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                $blueprint->index($columns, $name);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name) {
                $blueprint->dropIndex($name);
            });
        }
    }
};
