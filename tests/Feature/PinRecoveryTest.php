<?php

namespace Tests\Feature;

use App\Http\Controllers\PinController;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Le code PIN ne doit être demandé qu'à l'expiration de la session :
 * - connexion fraîche (email + mot de passe) → aucune demande de PIN ;
 * - session expirée → panneau PIN sur la page de login + reconnexion par PIN.
 */
class PinRecoveryTest extends TestCase
{
    private Utilisateur $user;

    private const MOT_DE_PASSE = 'secret-pin-2026';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Utilisateur::create([
            'nom'          => 'Test PIN',
            'email'        => 'pin.test.' . uniqid() . '@example.com',
            'motDePasse'   => self::MOT_DE_PASSE,
            'role'         => 'Employer',
            'pin_code'     => Hash::make('1234'),
            'pin_required' => true,
        ]);
    }

    private function cookieRecuperation(): string
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => self::MOT_DE_PASSE,
        ]);

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === PinController::COOKIE_NAME) {
                return $cookie->getValue();
            }
        }

        $this->fail('Le cookie de récupération PIN attendu après connexion.');
    }

    /** Noms des cookies d'une réponse (getName() — la propriété name est protégée). */
    private function cookieNames($response): \Illuminate\Support\Collection
    {
        return collect($response->headers->getCookies())->map(fn ($c) => $c->getName());
    }

    /** Simule une expiration de session : garde auth désolidarisée + session vidée. */
    private function expireSession(): void
    {
        auth()->logout();
        $this->app['session']->flush();
    }

    /** Connexion fraîche : la session arrive déjà validée par PIN → plus de modal */
    public function test_connexion_fraiche_ne_demande_pas_le_pin(): void
    {
        $this->post('/login', [
            'email'    => $this->user->email,
            'password' => self::MOT_DE_PASSE,
        ])->assertRedirect('/user/dashboard');

        $this->get('/pin/status')
            ->assertOk()
            ->assertJson(['pin_required' => false, 'has_pin' => true]);
    }

    /** La connexion pose le cookie de récupération pour un utilisateur à PIN actif */
    public function test_connexion_pose_le_cookie_de_recuperation(): void
    {
        $response = $this->post('/login', [
            'email'    => $this->user->email,
            'password' => self::MOT_DE_PASSE,
        ]);

        $this->assertTrue($this->cookieNames($response)->contains(PinController::COOKIE_NAME));
    }

    /** Déconnexion explicite : le cookie de récupération est effacé */
    public function test_deconnexion_efface_le_cookie_de_recuperation(): void
    {
        $this->post('/login', [
            'email'    => $this->user->email,
            'password' => self::MOT_DE_PASSE,
        ]);

        $response = $this->post('/logout');

        $expire = collect($response->headers->getCookies())
            ->first(fn ($c) => $c->getName() === PinController::COOKIE_NAME);

        $this->assertNotNull($expire, 'Le cookie de récupération doit être effacé à la déconnexion');
        // Cookie::forget → expiration dans le passé (le middleware chiffre la valeur vide)
        $this->assertLessThanOrEqual(0, $expire->getExpiresTime() - now()->timestamp);
    }

    /** La page de login expose le panneau PIN quand le cookie de récupération est présent */
    public function test_page_login_affiche_le_panneau_pin_apres_expiration(): void
    {
        $cookie = $this->cookieRecuperation();

        // Session détruite côté serveur (expiration) mais cookie conservé
        $this->expireSession();

        $this->call('GET', '/login', [], [PinController::COOKIE_NAME => $cookie])
            ->assertOk()
            ->assertSee('pinRecoveryPanel')
            ->assertSee('recovery-digit');
    }

    /** Sans cookie de récupération : la page de login est classique */
    public function test_page_login_sans_cookie_n_affiche_pas_le_panneau(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('pinRecoveryPanel');
    }

    /** PIN correct : reconnexion complète sans email ni mot de passe */
    public function test_recuperation_avec_pin_correct_reloggue(): void
    {
        $cookie = $this->cookieRecuperation();
        $this->expireSession();

        $response = $this->call('POST', '/pin/recovery', ['pin' => '1234'], [PinController::COOKIE_NAME => $cookie]);

        $response->assertOk()
            ->assertJson(['success' => true, 'redirect' => '/user/dashboard']);

        $this->assertTrue(auth()->check());
        $this->assertSame($this->user->id, auth()->id());
        $this->assertTrue(session('pin_verified', false));
        $this->assertSame('Employer', session('current_role'));
    }

    /** PIN incorrect : refus, aucune session ouverte */
    public function test_recuperation_avec_pin_incorrect_refusee(): void
    {
        $cookie = $this->cookieRecuperation();
        $this->expireSession();

        $this->call('POST', '/pin/recovery', ['pin' => '9999'], [PinController::COOKIE_NAME => $cookie])
            ->assertStatus(422)
            ->assertJson(['error' => 'Code PIN incorrect.']);

        $this->assertFalse(auth()->check());
    }

    /** 5 tentatives échouées → le panneau exige une reconnexion complète */
    public function test_recuperation_bloquee_apres_5_echecs(): void
    {
        $cookie = $this->cookieRecuperation();
        $this->expireSession();

        for ($i = 0; $i < PinController::MAX_ATTEMPTS; $i++) {
            $this->call('POST', '/pin/recovery', ['pin' => '0000'], [PinController::COOKIE_NAME => $cookie]);
        }

        $this->call('POST', '/pin/recovery', ['pin' => '1234'], [PinController::COOKIE_NAME => $cookie])
            ->assertStatus(429);
    }

    protected function tearDown(): void
    {
        Utilisateur::withTrashed()->where('email', 'like', 'pin.test.%@example.com')->forceDelete();
        parent::tearDown();
    }
}
