@extends('layouts.dashboard')

@section('title', 'Générer le bilan de présence')
@section('breadcrumb', 'Générer le bilan')

@section('header')
    <div>
        <span>Générer le bilan de présence</span>
        <p class="console-subtitle">Sélectionnez la période et le format pour votre rapport.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="console-card xl:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Paramètres du rapport</h2>
                    <p class="console-card-sub">Le bilan couvre la période comprise entre les deux dates.</p>
                </div>
                <span class="badge badge-info">PDF</span>
            </div>

            <div class="console-card-body">
                <form action="{{ route('admin.generateReport') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="start_date" class="input-label">Date de début</label>
                            <div class="mt-1">
                                <input type="date" id="start_date" name="start_date" required class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="end_date" class="input-label">Date de fin</label>
                            <div class="mt-1">
                                <input type="date" id="end_date" name="end_date" required class="input-field">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="export_format" class="input-label">Format d'exportation</label>
                        <div class="mt-1">
                            <select id="export_format" name="export_format" required class="select-field">
                                <option value="rien">Choisir le format d'exportation</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-4">
                        <button type="submit" class="btn-gold">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Générer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="console-card">
                <div class="console-card-body">
                    <span class="console-quick-icon" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <h2 class="mt-3 console-card-title">Bon à savoir</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Le rapport généré sera disponible au téléchargement immédiatement après validation du formulaire.
                    </p>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        // La date de fin doit rester postérieure à la date de début.
        document.addEventListener('DOMContentLoaded', function () {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (!startDateInput || !endDateInput) return;

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
@endpush
