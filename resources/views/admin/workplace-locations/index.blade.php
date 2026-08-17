@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <span>Gestion des lieux de travail</span>
</div>
@endsection

@section('content')
<div class="mx-auto sm:px-6 lg:px-8 px-4 py-8">
    <div class="bg-white rounded-2xl border border-gray-200/70 shadow-card p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-[#080808]">Lieux de travail</h1>
            <a href="{{ route('admin.workplace-locations.create') }}" class="btn-gold btn-press">
                Ajouter un lieu
            </a>
        </div>
    
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif
    
        <div class="table-wrap">
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
                <tbody class="table-body bg-white divide-y divide-gray-200">
                    @forelse($locations as $location)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FBF3E6] text-[#B77F1D]">
                                        <svg class="h-4.5 w-4.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-gray-900">{{ $location->nom }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $location->latitude }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $location->longitude }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge badge-info">{{ $location->rayon }} m</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="badge {{ $location->actif ? 'badge-success' : 'badge-danger' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $location->actif ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $location->actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.workplace-locations.edit', $location) }}" class="btn-press inline-flex items-center rounded-lg bg-pharaoh-gold/10 px-3 py-1.5 text-xs font-semibold text-pharaoh-bronze-dark hover:bg-pharaoh-gold/20 mr-3">
                                    Modifier
                                </a>
                                <form action="{{ route('admin.workplace-locations.destroy', $location) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-inset ring-red-600/20 transition-colors duration-150 hover:bg-red-50" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu de travail?')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
    </div>
    
    <div class="mt-6 bg-white rounded-2xl border border-gray-200/70 shadow-card p-6">
        <h2 class="text-xl font-bold text-[#080808] mb-4">Aide</h2>
        <div class="prose text-gray-600">
            <p>Les lieux de travail définissent les zones géographiques où vos employés peuvent marquer leur présence.</p>
    
            <h3 class="text-lg font-medium text-gray-900 mt-4">Comment ça marche ?</h3>
            <ul class="list-disc pl-5 space-y-1">
                <li>Chaque lieu de travail est défini par ses coordonnées (latitude et longitude) et un rayon en mètres.</li>
                <li>Lorsqu'un employé marque sa présence, sa position géographique est vérifiée.</li>
                <li>Si l'employé se trouve dans le rayon d'un lieu de travail actif, sa présence est validée.</li>
                <li>Si l'employé n'est pas dans un lieu de travail valide, un message d'erreur lui est affiché.</li>
            </ul>
    
            <h3 class="text-lg font-medium text-gray-900 mt-4">Conseils</h3>
            <ul class="list-disc pl-5 space-y-1">
                <li>Ajoutez tous vos bureaux et sites de travail pour permettre à vos employés de marquer leur présence.</li>
                <li>Définissez un rayon approprié : trop petit et les employés pourraient ne pas être détectés même s'ils sont au bureau, trop grand et ils pourraient marquer leur présence depuis l'extérieur.</li>
                <li>Vous pouvez désactiver temporairement un lieu (sans le supprimer) en décochant l'option "Actif".</li>
            </ul>
        </div>
    </div>
</div>
@endsection
