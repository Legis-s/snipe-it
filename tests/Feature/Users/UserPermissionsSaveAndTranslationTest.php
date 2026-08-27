<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserPermissionsSaveAndTranslationTest extends TestCase
{
    public function test_web_create_saves_allowed_denied_and_inherited_permissions(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('users.store'), [
                'first_name' => 'Permission',
                'username' => 'permission-create-test',
                'permission' => [
                    'assets.view' => '1',
                    'consumables.checkout' => '-1',
                    'reports.view' => '0',
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $permissions = User::where('username', 'permission-create-test')
            ->firstOrFail()
            ->decodePermissions();

        $this->assertSame(1, $permissions['assets.view']);
        $this->assertSame(-1, $permissions['consumables.checkout']);
        $this->assertSame(0, $permissions['reports.view']);
    }

    public function test_web_update_saves_permission_state_changes(): void
    {
        $user = User::factory()->create([
            'permissions' => json_encode([
                'assets.view' => 1,
                'consumables.checkout' => -1,
            ]),
        ]);

        $this->actingAs(User::factory()->superuser()->create())
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'username' => $user->username,
                'permission' => [
                    'assets.view' => '-1',
                    'consumables.checkout' => '1',
                    'reports.view' => '0',
                ],
            ])
            ->assertSessionHasNoErrors();

        $permissions = $user->refresh()->decodePermissions();

        $this->assertSame(-1, $permissions['assets.view']);
        $this->assertSame(1, $permissions['consumables.checkout']);
        $this->assertSame(0, $permissions['reports.view']);
    }

    public function test_create_form_restores_permissions_after_validation_error(): void
    {
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->withSession([
                '_old_input' => [
                    'permission' => [
                        'assets.view' => '-1',
                    ],
                ],
            ])
            ->get(route('users.create'));

        $response->assertOk();

        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);

        $this->assertCount(
            1,
            $xpath->query('//input[@name="permission[assets.view]" and @value="-1" and @checked]')
        );
    }

    public function test_every_displayed_permission_has_russian_translation(): void
    {
        $translations = include resource_path('lang/ru-RU/permissions.php');

        foreach (config('permissions') as $section => $permissions) {
            $this->assertArrayHasKey(Str::slug($section), $translations, "Missing Russian section translation: {$section}");

            foreach ($permissions as $permission) {
                if (($permission['display'] ?? false) !== true) {
                    continue;
                }

                $translationKey = Str::slug($permission['permission']);
                $this->assertArrayHasKey($translationKey, $translations, "Missing Russian permission translation: {$permission['permission']}");
                $this->assertNotEmpty($translations[$translationKey]['name'] ?? null);
            }
        }
    }

    public function test_russian_permission_strings_do_not_fall_back_to_english(): void
    {
        $english = include resource_path('lang/en-US/permissions.php');
        $russian = include resource_path('lang/ru-RU/permissions.php');

        foreach ($english as $key => $englishValue) {
            if (! array_key_exists($key, $russian)) {
                continue;
            }

            if (is_array($englishValue)) {
                foreach ($englishValue as $field => $text) {
                    if ($text !== '' && isset($russian[$key][$field])) {
                        $this->assertNotSame($text, $russian[$key][$field], "Untranslated permission string: {$key}.{$field}");
                    }
                }

                continue;
            }

            $this->assertNotSame($englishValue, $russian[$key], "Untranslated permission string: {$key}");
        }
    }
}
