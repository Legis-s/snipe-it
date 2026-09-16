<?php

namespace Tests\Unit;

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;
use Tests\TestCase;

class ConsumableTest extends TestCase
{
    public function test_percent_remaining_returns_one_hundred_when_nothing_is_checked_out()
    {
        $consumable = Consumable::factory()->create([
            'qty' => 25,
        ]);

        $this->assertEquals(100, $consumable->percentRemaining());
    }

    public function test_percent_remaining_returns_expected_value_when_partially_checked_out()
    {
        $consumable = Consumable::factory()->create([
            'qty' => 20,
        ]);
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $consumable->users()->attach($user->id, ['created_by' => $user->id]);
        }

        $this->assertEquals(75.0, $consumable->percentRemaining());
    }

    public function test_percent_remaining_is_zero_when_checked_out_exceeds_quantity(): void
    {
        $consumable = Consumable::factory()->create([
            'qty' => 3,
        ]);
        $this->assertTrue((new ConsumableAssignment([
            'consumable_id' => $consumable->id,
            'type' => ConsumableAssignment::ISSUED,
            'quantity' => 5,
        ]))->save());

        $this->assertEquals(-2, $consumable->numRemaining());
        $this->assertEquals(0, $consumable->percentRemaining());
    }
}
