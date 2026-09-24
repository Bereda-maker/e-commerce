<?php

use App\Models\Order;
use App\Models\User;

test('a buyer cannot view another buyer\'s order', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $order = Order::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get(route('orders.show', $order))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('orders.show', $order))
        ->assertOk();
});
