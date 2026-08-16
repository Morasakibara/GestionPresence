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
    
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#080808]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Nom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Latitude
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Longitude
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Rayon (m)
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                            Statut
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($locations as $location)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $location->nom }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $location->latitude }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $location->longitude }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $location->rayon }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $location->actif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
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
                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-150" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu de travail?')">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
    
                    @if(count($locations) === 0)
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                Aucun lieu de travail défini. <a href="{{ route('admin.workplace-locations.create') }}" class="text-3hcig-blue hover:text-3hcig-blue-dark">Ajoutez-en un</a>.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
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
