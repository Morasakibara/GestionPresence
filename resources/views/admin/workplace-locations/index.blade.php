@extends('layouts.dashboard')

@section('title', 'Lieux de travail')
@section('breadcrumb', 'Lieux de travail')

@section('header')
    <div>
        <span>Lieux de travail</span>
        <p class="console-subtitle">Zones géographiques autorisées pour le pointage des employés.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.workplace-locations.create') }}" class="btn-gold">
            <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter un lieu
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Lieux enregistrés', 'value' => $locations->count(), 'meta' => 'Tous sites confondus', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Lieux actifs', 'value' => $locations->where('actif', true)->count(), 'meta' => 'Pointage autorisé', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'Lieux inactifs', 'value' => $locations->where('actif', false)->count(), 'meta' => 'Pointage bloqué', 'tone' => 'danger'])
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Liste des lieux</h2>
                <p class="console-card-sub">Chaque lieu combine des coordonnées GPS et un rayon de tolérance.</p>
            </div>
            <span class="badge badge-gold">{{ $locations->count() }} lieu{{ $locations->count() > 1 ? 'x' : '' }}</span>
        </div>

        <div class="table-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Latitude</th>
                        <th scope="col">Longitude</th>
                        <th scope="col">Rayon (m)</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body divide-y divide-gray-200 bg-white">
                    @forelse($locations as $location)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FBF3E6] text-[#B77F1D]">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-gray-900">{{ $location->nom }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">{{ $location->latitude }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-500">{{ $location->longitude }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="badge badge-info">{{ $location->rayon }} m</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span class="badge {{ $location->actif ? 'badge-success' : 'badge-danger' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $location->actif ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $location->actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('admin.workplace-locations.edit', $location) }}" class="btn-soft">
                                        Modifier
                                    </a>
                                    <form action="{{ route('admin.workplace-locations.destroy', $location) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger-soft ring-1 ring-inset ring-red-600/20" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu de travail?')">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucun lieu de travail défini</h3>
                                    <p class="mt-1 text-sm text-gray-500">Ajoutez un lieu pour autoriser le pointage géolocalisé.</p>
                                    <a href="{{ route('admin.workplace-locations.create') }}" class="btn-gold mt-5">Ajouter un lieu</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Aide</h2>
                <p class="console-card-sub">Fonctionnement du pointage géolocalisé.</p>
            </div>
        </div>
        <div class="console-card-body space-y-4 text-sm text-gray-600">
            <p>Les lieux de travail définissent les zones géographiques où vos employés peuvent marquer leur présence.</p>

            <div>
                <h3 class="text-sm font-semibold text-gray-900">Comment ça marche ?</h3>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    <li>Chaque lieu de travail est défini par ses coordonnées (latitude et longitude) et un rayon en mètres.</li>
                    <li>Lorsqu'un employé marque sa présence, sa position géographique est vérifiée.</li>
                    <li>Si l'employé se trouve dans le rayon d'un lieu de travail actif, sa présence est validée.</li>
                    <li>Si l'employé n'est pas dans un lieu de travail valide, un message d'erreur lui est affiché.</li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900">Conseils</h3>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    <li>Ajoutez tous vos bureaux et sites de travail pour permettre à vos employés de marquer leur présence.</li>
                    <li>Définissez un rayon approprié : trop petit et les employés pourraient ne pas être détectés même s'ils sont au bureau, trop grand et ils pourraient marquer leur présence depuis l'extérieur.</li>
                    <li>Vous pouvez désactiver temporairement un lieu (sans le supprimer) en décochant l'option "Actif".</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
