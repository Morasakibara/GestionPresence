@extends('layouts.app')


@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-3hcig-blue-dark">Rapport de Présence</h1>
            <p class="text-gray-600">Période: <span class="font-medium">{{ request('start_date') }} - {{ request('end_date') }}</span></p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse mb-6">
                <thead>
                    <tr class="bg-3hcig-blue text-white">
                        <th class="border border-gray-300 px-4 py-3 text-left">Nom de l'employé</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Total Présence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($presences as $data)
                    <tr class="hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-3">{{ $data->employer_nom }}</td>
                        <td class="border border-gray-300 px-4 py-3">{{ $data->total_presence }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            <form action="{{ route('admin.exportReport') }}" method="POST">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="bg-3hcig-green hover:bg-3hcig-green-light text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Exporter en PDF
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
