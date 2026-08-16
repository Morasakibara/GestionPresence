@extends('layouts.app')

@section('title', 'Bilan de présence du mois')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-3hcig-blue-dark sm:text-3xl">Bilan de présence du mois</h1>
            <p class="mt-2 text-sm text-gray-600">Récapitulatif de vos présences et absences</p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <!-- Graphique -->
            <div class="mb-6">
                <canvas id="presenceChart" height="300"></canvas>
            </div>

            <!-- Légende du graphique -->
            <div class="mb-6 flex justify-center space-x-6">
                <div class="flex items-center">
                    <span class="mr-2 inline-block h-4 w-4 rounded-full bg-3hcig-blue"></span>
                    <span class="text-sm text-gray-700">Présences</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-2 inline-block h-4 w-4 rounded-full bg-red-500"></span>
                    <span class="text-sm text-gray-700">Absences</span>
                </div>
            </div>

            <!-- Informations de présence -->
            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="rounded-lg bg-3hcig-blue-light/10 p-4 text-center">
                    <p class="text-sm text-gray-600">Total de présences</p>
                    <p class="mt-1 text-3xl font-bold text-3hcig-blue">{{ $totalPresences }}</p>
                    <p class="mt-1 text-xs text-gray-500">jours ce mois-ci</p>
                </div>
                <div class="rounded-lg bg-red-100 p-4 text-center">
                    <p class="text-sm text-gray-600">Total d'absences</p>
                    <p class="mt-1 text-3xl font-bold text-red-600">{{ $totalAbsences }}</p>
                    <p class="mt-1 text-xs text-gray-500">jours ce mois-ci</p>
                </div>
            </div>

            <!-- Taux de présence -->
            @php
                $presenceRate = $totalPresences + $totalAbsences > 0
                    ? round(($totalPresences / ($totalPresences + $totalAbsences)) * 100)
                    : 0;
            @endphp
            <div class="mt-8">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Taux de présence</span>
                    <span class="text-sm font-medium text-3hcig-blue">{{ $presenceRate }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-2 rounded-full bg-3hcig-blue" style="width: {{ $presenceRate }}%"></div>
                </div>
            </div>
            <td class="whitespace-nowrap px-6 py-4">
                @php
                     $lastPresence = App\Models\Presence::where('employerID', Auth::user()->id)
                        ->whereNotNull('latitude_arrivee')
                        ->orderBy('date', 'desc')
                        ->first();
                @endphp

                @if($lastPresence && ($lastPresence->localisation_validee_arrivee || $lastPresence->localisation_validee_depart))
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Validée
                    </span>
                @elseif($lastPresence)
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Non validée
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Non disponible
                    </span>
                @endif
            </td>
        </div>

        <!-- Présences suspectes : contestation -->
        @if(isset($suspectPresences) && $suspectPresences->count() > 0)
        <div class="mt-8 overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Présences suspectes</h2>
            <p class="mt-1 text-sm text-gray-600">
                Des pointages ont été marqués suspects par le système anti-triche. Vous pouvez contester si vous estimez une erreur.
            </p>

            <div class="mt-4 space-y-4">
                @foreach($suspectPresences as $suspect)
                <div class="rounded-lg border border-red-200 bg-red-50/50 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($suspect->date)->format('d/m/Y') }}
                                <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">Suspecte</span>
                            </p>
                            <p class="mt-1 text-sm text-gray-600">Motif : {{ $suspect->motif_suspicion ?? 'Non renseigné' }}</p>
                            @if($suspect->commentaire_contestation)
                                <p class="mt-2 text-sm text-green-700">
                                    ✓ Contesté le {{ \Carbon\Carbon::parse($suspect->conteste_le)->format('d/m/Y à H:i') }} :
                                    « {{ $suspect->commentaire_contestation }} »
                                </p>
                            @endif
                        </div>
                        @if(!$suspect->commentaire_contestation)
                        <form method="POST" action="{{ route('user.contesterPresence', $suspect->id) }}" class="w-full sm:w-auto">
                            @csrf
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input type="text" name="commentaire" required maxlength="1000"
                                    placeholder="Votre explication..."
                                    class="flex-1 rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20">
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                                    Contester
                                </button>
                            </div>
                        </form>
                        @else
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                            Contestation envoyée
                        </span>
                        @endif
                        <a href="{{ route('user.presenceHistory', $suspect->id) }}"
                            class="mt-2 inline-flex items-center text-xs font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                            Voir l'historique complet →
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Script Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('presenceChart').getContext('2d');

    const data = {
        labels: @json($presences->pluck('date')->toArray()), // Récupère les jours du mois
        datasets: [
            {
                label: 'Présences',
                data: @json($presences->where('status', 'présent')->pluck('date')->countBy()->toArray()), // Comptes les jours où l'utilisateur est présent
                backgroundColor: '#1976D2', // Couleur 3hcig-blue
                borderRadius: 4
            },
            {
                label: 'Absences',
                data: @json($presences->where('status', 'absent')->pluck('date')->countBy()->toArray()), // Comptes les jours où l'utilisateur est absent
                backgroundColor: '#EF4444', // Rouge
                borderRadius: 4
            }
        ]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Masquer la légende par défaut car nous avons une légende personnalisée
                },
                tooltip: {
                    backgroundColor: '#1F2937',
                    titleColor: '#F9FAFB',
                    bodyColor: '#F9FAFB',
                    padding: 10,
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0 // Entiers seulement
                    },
                    grid: {
                        color: '#E5E7EB'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            barPercentage: 0.6,
            categoryPercentage: 0.8
        }
    });
});
</script>
@endsection
