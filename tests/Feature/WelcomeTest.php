<?php

namespace Tests\Feature;

use App\User;
use Carbon\Carbonite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class WelcomeTest extends TestCase
{
    protected function setUp(): void
    {
        // By default you should freeze the time in unit tests.
        Carbonite::freeze();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbonite::release();
        // Release the time, speed and all Carbonite changes so it does not affect other tests.
    }

    protected function createNewUser(): User
    {
        User::where('email', 'bob@company.com')->delete();

        return User::create([
            'name' => 'Bob',
            'email' => 'bob@company.com',
            'password' => bcrypt('hello'),
        ]);
    }

    public function testValidUntilDisplay()
    {
        // We assume your server app timezone is set to UTC
        // If it's not, Ô please, reconsider: https://medium.com/@kylekatarnls/always-use-utc-dates-and-times-8a8200ca3164
        Carbonite::jumpTo('2019-08-26 15:00'); // Jump to a given moment

        $bob = $this->createNewUser();
        $bob->valid_until = Date::parse('2019-08-27 04:00');
        $bob->timezone = 'Europe/Paris';
        $bob->language = 'fr_FR';

        Auth::login($bob);

        // If Bob is in Paris valid_until is the next day at 6am, so we should display Tomorrow in French:
        $this->get('/')->assertSeeText('Demain à 06:00');

        $bob->timezone = 'America/Los_Angeles';
        $bob->language = 'en_US';

        // But if Bob is in Los Angeles it's still Today:
        $this->get('/')->assertSeeText('Today at 9:00 PM');

         // Then we can jump to any other moment, elapse, rewind and retest
        Carbonite::jumpTo('2019-08-22');

        $this->get('/')->assertSeeText('Monday at 9:00 PM');

        Carbonite::jumpTo('2019-09-02');

        $this->get('/')->assertSeeText('Last Monday at 9:00 PM');
    }
}
