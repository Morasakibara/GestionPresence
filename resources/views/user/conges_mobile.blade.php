<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mes Congés — Le Pharaon</title>
    @vite('resources/css/app.css')
</head>
<body class="h-full bg-gray-50">
    <!-- Header mobile -->
    <div class="sticky top-0 z-50 bg-[#080808] px-4 py-3 shadow-lg">
        <div class="flex items-center justify-between">
            <a href="{{ route('user.dashboard') }}" class="text-pharaoh-gold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-lg font-bold text-white">Congés</h1>
            <div class="w-6"></div>
        </div>
    </div>

    <div class="px-4 py-4 space-y-4">
        <!-- Success/Error messages -->
        @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 p-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="h-5 w-5 text-red-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Stats rapides -->
        @php
            $userConges = \App\Models\Conge::where('employer_id', auth()->id())->orderByDesc('created_at')->get();
        @endphp
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-3 bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-[#080808]">{{ $userConges->count() }}</div>
                <div class="text-xs text-gray-500">Total</div>
            </div>
            <div class="text-center p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                <div class="text-2xl font-bold text-emerald-600">{{ $userConges->where('statut', 'approuve')->count() }}</div>
                <div class="text-xs text-emerald-600">Approuvés</div>
            </div>
            <div class="text-center p-3 bg-amber-50 rounded-xl border border-amber-200">
                <div class="text-2xl font-bold text-amber-600">{{ $userConges->where('statut', 'en_attente')->count() }}</div>
                <div class="text-xs text-amber-600">En attente</div>
            </div>
        </div>

        <!-- Formulaire nouvelle demande -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-card p-5">
            <h2 class="text-base font-bold text-[#080808] mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Nouvelle demande</span>
            </h2>
            <form action="{{ route('user.conges.store') }}" method="POST">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                        <input type="date" name="date_debut" required min="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
                        <input type="date" name="date_fin" required class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select name="type" required class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base focus:border-pharaoh-gold">
                            <option value="conge">Congé</option>
                            <option value="absence_justifiee">Absence justifiée</option>
                            <option value="absence_injustifiee">Absence injustifiée</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motif *</label>
                        <textarea name="motif" rows="3" required class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20" placeholder="Décrivez le motif..."></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-pharaoh-gold px-4 py-3 text-base font-bold text-white shadow-gold hover:bg-pharaoh-gold-light active:scale-[0.97] transition-all" onclick="return confirm('Envoyer cette demande ?')">
                        Soumettre la demande
                    </button>
                </div>
            </form>
        </div>

        <!-- Historique -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-card p-5">
            <h2 class="text-base font-bold text-[#080808] mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Historique</span>
            </h2>
            @if($userConges->isEmpty())
                <p class="text-center text-gray-400 py-6">Aucune demande</p>
            @else
            <div class="space-y-3">
                @foreach($userConges as $c)
                <div class="border border-gray-100 rounded-xl p-3 hover:bg-pharaoh-gold/5 transition-colors">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-semibold text-sm text-[#080808]">{{ $c->typeLabel() }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $c->date_debut->format('d/m') }} → {{ $c->date_fin->format('d/m/Y') }}
                                · <span class="font-bold text-pharaoh-gold">{{ $c->dureeJours() }}j</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-1 truncate max-w-[250px]" title="{{ $c->motif }}">{{ $c->motif }}</div>
                        </div>
                        <div>{!! $c->statutBadge() !!}</div>
                    </div>
                    @if($c->commentaire_admin)
                    <div class="mt-2 flex items-start gap-1.5 text-xs text-gray-500 bg-gray-50 rounded-lg p-2">
                        <svg class="h-3.5 w-3.5 text-gray-400 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span>{{ $c->commentaire_admin }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Bottom nav -->
    <nav class="sticky bottom-0 bg-[#080808] border-t border-white/10 px-4 py-2 safe-area-bottom">
        <div class="flex items-center justify-around">
            <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center py-1 text-gray-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-xs mt-0.5">Accueil</span>
            </a>
            <a href="{{ route('presence.index') }}" class="flex flex-col items-center py-1 text-gray-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs mt-0.5">Pointage</span>
            </a>
            <a href="{{ route('user.conges') }}" class="flex flex-col items-center py-1 text-pharaoh-gold">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs mt-0.5 font-bold">Congés</span>
            </a>
            <a href="{{ route('user.profile') }}" class="flex flex-col items-center py-1 text-gray-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-xs mt-0.5">Profil</span>
            </a>
        </div>
    </nav>
</body>
</html>
