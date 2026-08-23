<?php

namespace Tests\Unit;

use App\Services\LoginAttemptService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LoginAttemptServiceTest extends TestCase
{
    private LoginAttemptService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoginAttemptService();
        Cache::flush();
    }

    public function test_not_locked_out_initially(): void
    {
        $this->assertFalse($this->service->isLockedOut('test@example.com'));
    }

    public function test_lockout_after_10_failures(): void
    {
        $email = 'lockout@example.com';

        for ($i = 1; $i <= 10; $i++) {
            $attempts = $this->service->recordFailure($email);
        }

        $this->assertEquals(10, $attempts);
        $this->assertTrue($this->service->isLockedOut($email));
    }

    public function test_not_locked_out_before_10_failures(): void
    {
        $email = 'almost@example.com';

        for ($i = 1; $i <= 9; $i++) {
            $this->service->recordFailure($email);
        }

        $this->assertFalse($this->service->isLockedOut($email));
    }

    public function test_reset_clears_lockout(): void
    {
        $email = 'reset@example.com';

        for ($i = 1; $i <= 10; $i++) {
            $this->service->recordFailure($email);
        }

        $this->assertTrue($this->service->isLockedOut($email));

        $this->service->reset($email);

        $this->assertFalse($this->service->isLockedOut($email));
        $this->assertEquals(0, $this->service->getAttempts($email));
    }

    public function test_get_attempts_returns_correct_count(): void
    {
        $email = 'count@example.com';

        $this->assertEquals(0, $this->service->getAttempts($email));

        $this->service->recordFailure($email);
        $this->service->recordFailure($email);
        $this->service->recordFailure($email);

        $this->assertEquals(3, $this->service->getAttempts($email));
    }

    public function test_record_failure_returns_incremented_count(): void
    {
        $email = 'increment@example.com';

        $this->assertEquals(1, $this->service->recordFailure($email));
        $this->assertEquals(2, $this->service->recordFailure($email));
        $this->assertEquals(3, $this->service->recordFailure($email));
    }

    public function test_lockout_message_contains_minutes(): void
    {
        $email = 'message@example.com';

        for ($i = 1; $i <= 10; $i++) {
            $this->service->recordFailure($email);
        }

        $message = $this->service->lockoutMessage($email);
        $this->assertStringContainsString('minute(s)', $message);
        $this->assertStringContainsString('10 tentatives', $message);
    }

    public function test_max_attempts_is_10(): void
    {
        $this->assertEquals(10, $this->service->maxAttempts());
    }

    public function test_lockout_minutes_is_15(): void
    {
        $this->assertEquals(15, $this->service->lockoutMinutes());
    }

    public function test_different_emails_are_independent(): void
    {
        $email1 = 'user1@example.com';
        $email2 = 'user2@example.com';

        for ($i = 1; $i <= 10; $i++) {
            $this->service->recordFailure($email1);
        }

        $this->assertTrue($this->service->isLockedOut($email1));
        $this->assertFalse($this->service->isLockedOut($email2));
    }

    public function test_seconds_remaining_is_zero_when_not_locked(): void
    {
        $this->assertEquals(0, $this->service->secondsRemaining('fresh@example.com'));
    }
}
