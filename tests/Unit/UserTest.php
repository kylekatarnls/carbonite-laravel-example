<?php

namespace Tests\Unit;

use App\User;
use Carbon\Carbonite;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\User
 */
class UserTest extends TestCase
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

    /**
     * @covers ::isInTrial
     */
    public function testUserTrial()
    {
        $bob = $this->createNewUser();

        $this->assertSame((string) Date::now(), (string) $bob->created_at);
        // This test will even work if you use microsecond precision, because the time is frozen

        $this->assertTrue($bob->isInTrial());

        Carbonite::elapse('7 days');
        // We simulate a week ellipsis

        $this->assertFalse($bob->isInTrial());

        Carbonite::rewind('1 second');
        // And we can go back 1 second earlier, so Date::now() is precisely
        // $bob->created_at + 7 days - 1 second (no microsecond gap as time is still frozen)

        $this->assertTrue($bob->isInTrial());

        Carbonite::speed(10); // Now time passes 10 times as fast

        usleep(100 * 1000); // So sleeping 0.1 second is enough to expire the trial:

        $this->assertFalse($bob->isInTrial());

        // Remember: when time is not frozen, each line of code add a few microseconds (execution is not
        // instantaneous), so results have unpredictable imprecision.
        // So, you can use Carbonite::speed() but it always safer to achieve your unit tests using
        // frozen time.
    }

    public function testValidUntil()
    {

    }
}
