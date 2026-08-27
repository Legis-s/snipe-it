<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class BitrixCredentialsTest extends TestCase
{
    public function test_edit_form_contains_bitrix_fields_without_exposing_saved_token(): void
    {
        $user = User::factory()->create(['bitrix_id' => 123]);
        $user->setBitrixToken('saved-secret');
        $user->save();

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.edit', $user));

        $response->assertOk()
            ->assertSee('name="new_bitrix_token"', false)
            ->assertSee('name="bitrix_id"', false)
            ->assertSee('name="clear_bitrix_token"', false)
            ->assertDontSee('saved-secret')
            ->assertDontSee($user->bitrix_token);
    }

    public function test_web_create_saves_bitrix_id_and_encrypted_token(): void
    {
        $this->actingAs(User::factory()->createUsers()->viewUsers()->create())
            ->post(route('users.store'), [
                'first_name' => 'Bitrix',
                'username' => 'bitrix-web-create',
                'bitrix_id' => 456,
                'new_bitrix_token' => 'web-secret',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user = User::where('username', 'bitrix-web-create')->firstOrFail();

        $this->assertSame(456, $user->bitrix_id);
        $this->assertNotSame('web-secret', $user->bitrix_token);
        $this->assertSame('web-secret', Crypt::decryptString($user->bitrix_token));
    }

    public function test_web_update_replaces_and_clears_bitrix_token(): void
    {
        $actor = User::factory()->superuser()->create();
        $user = User::factory()->create(['bitrix_id' => 111]);
        $user->setBitrixToken('old-secret');
        $user->save();

        $this->actingAs($actor)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'bitrix_id' => 222,
                'new_bitrix_token' => 'new-secret',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(222, $user->refresh()->bitrix_id);
        $this->assertSame('new-secret', Crypt::decryptString($user->bitrix_token));

        $this->actingAs($actor)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'bitrix_id' => 222,
                'clear_bitrix_token' => true,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->bitrix_token);
    }

    public function test_api_accepts_legacy_token_field_but_stores_it_encrypted(): void
    {
        $response = $this->actingAsForApi(User::factory()->createUsers()->create())
            ->postJson(route('api.users.store'), [
                'first_name' => 'Bitrix API',
                'username' => 'bitrix-api-create',
                'bitrix_id' => 789,
                'bitrix_token' => 'api-secret',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $user = User::findOrFail($response['payload']['id']);

        $this->assertNotSame('api-secret', $user->bitrix_token);
        $this->assertSame('api-secret', Crypt::decryptString($user->bitrix_token));
        $this->assertArrayNotHasKey('bitrix_token', $user->toArray());
    }

    public function test_api_update_encrypts_new_token_and_can_clear_it(): void
    {
        $actor = User::factory()->superuser()->create();
        $user = User::factory()->create();

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'new_bitrix_token' => 'updated-api-secret',
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSame('updated-api-secret', Crypt::decryptString($user->refresh()->bitrix_token));

        $this->actingAsForApi($actor)
            ->patchJson(route('api.users.update', $user), [
                'clear_bitrix_token' => true,
            ])
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertNull($user->refresh()->bitrix_token);
    }

    public function test_invalid_stored_token_is_handled_without_decryption_exception(): void
    {
        $user = User::factory()->create(['bitrix_token' => 'not-encrypted']);

        $this->assertNull($user->decryptedBitrixToken());
    }
}
