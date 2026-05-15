<?php

use App\Models\User;

test('admin can delete another user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'consulta']);

    $response = $this->actingAs($admin)->delete(route('users.destroy', $target));

    $response->assertRedirect(route('users.index'));
    expect($target->fresh())->not->toBeNull();
    expect($target->fresh()->deleted_at)->not->toBeNull();
});

test('consulta user cannot access user index', function () {
    $consulta = User::factory()->create(['role' => 'consulta']);

    $this->actingAs($consulta)
        ->get(route('users.index'))
        ->assertForbidden();
});