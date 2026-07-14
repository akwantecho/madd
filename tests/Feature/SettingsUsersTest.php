<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_be_created_with_a_hashed_password(): void
    {
        $this->post('/users', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'password' => 'secret123',
            'role' => 'Manager',
            'status' => 'Active',
        ])->assertRedirect();

        $user = User::where('email', 'sara@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Manager', $user->role);
        $this->assertSame('Active', $user->status);
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::create(['name' => 'A', 'email' => 'dup@example.com', 'password' => 'x', 'role' => 'Operator']);

        $this->post('/users', [
            'name' => 'B', 'email' => 'dup@example.com', 'password' => 'secret123', 'role' => 'Operator',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'dup@example.com')->count());
    }

    public function test_inline_role_change_persists(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'role' => 'Operator']);

        $this->put('/users/'.$user->id, ['role' => 'Administrator'])->assertRedirect();

        $this->assertSame('Administrator', $user->fresh()->role);
    }

    public function test_editing_without_password_keeps_the_old_one(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'original', 'role' => 'Operator']);
        $oldHash = $user->fresh()->password;

        $this->put('/users/'.$user->id, [
            'name' => 'A2', 'email' => 'a@example.com', 'role' => 'Manager', 'password' => '',
        ])->assertRedirect();

        $fresh = $user->fresh();
        $this->assertSame('A2', $fresh->name);
        $this->assertSame('Manager', $fresh->role);
        $this->assertSame($oldHash, $fresh->password); // unchanged
    }

    public function test_a_user_can_be_deleted(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'x', 'role' => 'Operator']);

        $this->delete('/users/'.$user->id)->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_settings_save_persists_submitted_keys(): void
    {
        $this->post('/settings', [
            'tab' => 'finance',
            'vat_rate' => '12',
            'invoice_prefix' => 'BILL-',
        ])->assertRedirect();

        $this->assertSame('12', Setting::get('vat_rate'));
        $this->assertSame('BILL-', Setting::get('invoice_prefix'));
    }

    public function test_settings_page_renders_with_saved_values(): void
    {
        Setting::put('vat_rate', '9');
        $this->get('/settings?tab=finance')->assertOk()->assertSee('value="9"', false);
    }
}
