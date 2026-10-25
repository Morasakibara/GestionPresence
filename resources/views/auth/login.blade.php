@if (Auth::check())
@extends('layouts.app')
@endif

@section('content')
<div class="flex min-h-screen">
  <!-- Bloc gauche avec image en arrière-plan et texte d'accueil -->
  <div class="hidden md:flex md:w-1/2 relative bg-[#080808] overflow-hidden">
    <!-- Image en arrière-plan -->
    <div class="absolute inset-0 z-0">
      <img src="{{ asset('storage/avatars/loginBackground.jpg') }}" alt="Background" class="w-full h-full object-cover">
    </div>
    
    <!-- Couche noir + dégradé or -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/85 via-black/70 to-black/40 z-10"></div>
    <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-pharaoh-gold/20 blur-3xl z-10"></div>
    
    <!-- Contenu du bloc gauche -->
    <div class="relative z-20 flex flex-col justify-center items-start px-12 w-full h-full">
      <img class="h-28 mb-10 rounded-2xl bg-white/10 p-3 shadow-gold backdrop-blur-md" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
      
      <!-- Texte d'accueil animé -->
      <div class="inline-flex items-center gap-2 mb-4 rounded-full border border-pharaoh-gold/40 bg-pharaoh-gold/10 px-4 py-1.5 text-sm font-medium text-pharaoh-gold-light animate-fade-in">
        <span class="h-2 w-2 rounded-full bg-pharaoh-gold-bright"></span>
        Gestion de présence & rendement
      </div>
      <h1 class="font-display text-4xl font-bold leading-tight mb-4 text-white text-shadow animate-fade-in">Bienvenue au <span class="text-pharaoh-gold-bright">Pharaon</span></h1>
      <p class="text-lg text-gray-200 mb-6 max-w-md animate-slide-up">
        Connectez-vous pour accéder à votre espace personnel : pointage, fiches de rendement et évaluation de votre discipline.
      </p>
      <div class="animate-bounce-slow mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pharaoh-gold-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </div>
    </div>
  </div>

  <!-- Bloc droit avec formulaire de connexion -->
  <div class="w-full md:w-1/2 flex flex-col justify-center bg-[#F8F8F8] px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <div class="md:hidden">
        <img class="mx-auto h-20 w-auto" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
      </div>
      <h2 class="font-display mt-10 text-center text-2xl font-semibold leading-9 tracking-tight text-[#080808]">Connexion à votre compte</h2>
      <p class="mt-2 text-center text-sm text-gray-500">Accédez à votre espace Le Pharaon</p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      @if ($errors->any())
      <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6 shadow-md">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      @if(!empty($pinRecovery ?? null))
      {{-- ═══ Récupération de session expirée par code PIN ═══ --}}
      <div id="pinRecoveryPanel" class="rounded-xl border-2 border-pharaoh-gold bg-white p-5 shadow-md mb-6">
        <div class="flex items-center gap-2 mb-1">
          <svg class="h-5 w-5 text-pharaoh-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
          </svg>
          <span class="font-semibold text-[#080808]">Session expirée — déverrouillage rapide</span>
        </div>
        <p class="text-xs text-gray-500 mb-3">Connecté précédemment sous <b>{{ $pinRecovery['email'] }}</b>… saisissez votre code PIN pour reprendre votre session.</p>

        <div id="pinRecoveryError" class="hidden bg-red-50 border border-red-200 text-red-600 text-sm rounded-md px-3 py-2 mb-3"></div>

        <div class="flex justify-center gap-2">
          @for($i = 0; $i < 4; $i++)
            <input type="tel" inputmode="numeric" maxlength="1"
                   class="recovery-digit w-12 h-12 text-center text-xl font-bold rounded-lg border-2 border-gray-300 focus:border-pharaoh-gold focus:outline-none"
                   autocomplete="off">
          @endfor
        </div>

        <button type="button" id="pinRecoverySubmit"
                class="mt-4 w-full rounded-xl bg-pharaoh-gold py-2.5 text-sm font-semibold text-white hover:brightness-110">
          Déverrouiller
        </button>

        <button type="button" id="pinRecoverySwitch"
                class="mt-2 w-full text-xs font-medium text-gray-500 hover:text-pharaoh-gold">
          Utiliser email &amp; mot de passe
        </button>
      </div>

      <script>
      document.addEventListener('DOMContentLoaded', function () {
        const panel = document.getElementById('pinRecoveryPanel');
        const form = panel.nextElementSibling;
        const errorMsg = document.getElementById('pinRecoveryError');
        const digits = panel.querySelectorAll('.recovery-digit');
        const btn = document.getElementById('pinRecoverySubmit');

        digits[0].focus();

        digits.forEach((input, i) => {
          input.addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && i < digits.length - 1) digits[i + 1].focus();
            if (digits[0].value && digits[1].value && digits[2].value && digits[3].value) submitRecovery();
          });
          input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && i > 0) { digits[i - 1].focus(); digits[i - 1].value = ''; }
            if (e.key === 'Enter') { e.preventDefault(); submitRecovery(); }
          });
          input.addEventListener('click', function () { this.select(); });
        });

        function submitRecovery() {
          const code = Array.from(digits).map(d => d.value).join('');
          if (code.length !== 4) return;
          errorMsg.classList.add('hidden');
          btn.disabled = true;
          btn.textContent = 'Vérification…';

          fetch('{{ route('pin.recovery') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ pin: code })
          })
          .then(r => r.json().then(j => ({ ok: r.ok, data: j })))
          .then(({ ok, data }) => {
            if (ok && data.success) {
              btn.textContent = '✅ Session restaurée ! Redirection…';
              setTimeout(() => { window.location.href = data.redirect || '/'; }, 600);
            } else {
              errorMsg.textContent = data.error || 'Code PIN incorrect.';
              errorMsg.classList.remove('hidden');
              digits.forEach(d => { d.value = ''; });
              digits[0].focus();
              btn.disabled = false;
              btn.textContent = 'Déverrouiller';
            }
          })
          .catch(() => {
            errorMsg.textContent = 'Erreur réseau. Vérifiez votre connexion.';
            errorMsg.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Déverrouiller';
          });
        }

        btn.addEventListener('click', submitRecovery);
        document.getElementById('pinRecoverySwitch').addEventListener('click', function () {
          panel.classList.add('hidden');
          form.classList.remove('hidden');
          form.querySelector('#email').focus();
        });
      });
      </script>

      @endif

      {{-- Le formulaire complet apparaît uniquement si aucun PIN de récupération n'est disponible --}}
      <form class="{{ empty($pinRecovery ?? null) ? 'space-y-6' : 'space-y-6 hidden' }}" method="POST" action="{{ route('login') }}">
        @csrf

        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-700">Adresse email</label>
          <div class="mt-2">
            <input id="email" type="email" name="email" autocomplete="email" required
                  class="input-field @error('email') input-field-error @enderror"
                  value="{{ old('email') }}">
          </div>
          @error('email')
            <p class="input-error">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium leading-6 text-gray-700">Mot de passe</label>
          </div>
          <div class="mt-2 relative">
            <input id="password" type="password" name="password" autocomplete="current-password" required
                  class="input-field pr-10 @error('password') input-field-error @enderror">
            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
          @error('password')
            <p class="input-error">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <button type="submit"
                  class="btn-gold flex w-full justify-center rounded-xl px-3 py-2.5 text-sm font-semibold leading-6 text-white">
            Se connecter
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<style>
  .text-shadow {
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
  }
  
  @keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
  
  @keyframes slideUp {
    0% { transform: translateY(20px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
  }
  
  @keyframes bounceSlow {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(10px); }
  }
  
  .animate-fade-in {
    animation: fadeIn 1.5s ease-in-out;
  }
  
  .animate-slide-up {
    animation: slideUp 1.5s ease-out 0.5s both;
  }
  
  .animate-bounce-slow {
    animation: bounceSlow 2s infinite;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        // Change the eye icon
        if (type === 'text') {
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
        } else {
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
        }
    });
});
</script>
@endsection