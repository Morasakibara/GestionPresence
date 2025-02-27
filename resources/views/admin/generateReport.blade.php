@extends('layouts.app')

@section('title', 'Générer le bilan de présence')

@section('content')
<div class="mx-auto max-w-md px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-3hcig-blue-dark">Générer le bilan de présence</h1>
        <p class="mt-2 text-sm text-gray-600">Sélectionnez la période et le format pour votre rapport</p>
    </div>

    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
        <form action="{{ route('admin.generateReport') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Date de début</label>
                <div class="mt-1">
                    <input type="date" id="start_date" name="start_date" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">Date de fin</label>
                <div class="mt-1">
                    <input type="date" id="end_date" name="end_date" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label for="export_format" class="block text-sm font-medium text-gray-700">Format d'exportation</label>
                <div class="mt-1">
                    <select id="export_format" name="export_format" required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                        <option value="rien">Choisir le format d'exportation</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-3hcig-green px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-green-light focus:outline-none focus:ring-2 focus:ring-3hcig-green focus:ring-offset-2">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Générer
                </button>
            </div>
        </form>

        <div class="mt-6 rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        Le rapport généré sera disponible au téléchargement immédiatement.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script pour vérifier que la date de fin est après la date de début
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (endDate < startDate) {
                endDateInput.setCustomValidity('La date de fin doit être après la date de début');
            } else {
                endDateInput.setCustomValidity('');
            }
        }

        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    });
</script>
@endsection
