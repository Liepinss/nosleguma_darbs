<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * API testa gadījumi (t.sk. noslēguma darba „testcase” pierādījums).
 */
class ApiNoslegumaPrasibasTest extends TestCase
{
    use RefreshDatabase;

    public function test_01_public_statistics_returns_expected_json_fields(): void
    {
        $response = $this->getJson('/api/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'animals_total',
                'animals_available',
                'adoption_applications_total',
                'registered_users_total',
            ]);
    }

    public function test_02_register_rejects_invalid_email_with_validation_error(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Tests',
            'email' => 'nav-epasts',
            'password' => 'parole1',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_03_login_returns_token_for_valid_user(): void
    {
        $user = User::factory()->create([
            'email' => 'api_test_login@example.com',
            'password' => 'secret12',
            'is_blocked' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api_test_login@example.com',
            'password' => 'secret12',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->assertJsonPath('user.email', 'api_test_login@example.com');
    }

    public function test_04_animals_index_includes_created_animal_in_payload(): void
    {
        $animal = Animal::query()->create([
            'name' => 'API Test Mīmulis',
            'species' => 'Kaķis',
            'gender' => 'Sieviete',
            'age' => 2,
            'description' => 'Īss tests.',
            'image' => 'https://example.com/pet.jpg',
            'category_id' => 1,
        ]);

        $response = $this->getJson('/api/animals');

        $response->assertOk();
        $rows = $response->json('animals');
        $this->assertIsArray($rows);
        $match = collect($rows)->firstWhere('id', $animal->id);
        $this->assertNotNull($match);
        $this->assertSame('API Test Mīmulis', $match['name']);
    }

    public function test_05_admin_can_patch_animal_name(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_blocked' => false,
        ]);

        $animal = Animal::query()->create([
            'name' => 'Vecais vārds',
            'species' => 'Suns',
            'gender' => 'Vīrietis',
            'age' => 4,
            'description' => 'Apraksts.',
            'image' => 'https://example.com/dog.jpg',
            'category_id' => 1,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/animals/{$animal->id}", [
            'name' => 'Jaunais vārds',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Jaunais vārds');

        $this->assertDatabaseHas('animals', [
            'id' => $animal->id,
            'name' => 'Jaunais vārds',
        ]);
    }

    public function test_06_admin_can_approve_pending_adoption_application(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_blocked' => false,
        ]);

        $user = User::factory()->create(['is_blocked' => false]);

        $animal = Animal::query()->create([
            'name' => 'Apstiprināmais',
            'species' => 'Suns',
            'gender' => 'Vīrietis',
            'age' => 1,
            'description' => 'Tests.',
            'image' => 'https://example.com/a.jpg',
            'category_id' => 1,
        ]);

        $appId = DB::table('applications')->insertGetId([
            'user_id' => $user->id,
            'animal_id' => $animal->id,
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'status' => 'pending',
            'message' => null,
            'phone' => '+37120000000',
            'address' => null,
            'experience' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/admin/applications/{$appId}/approve");

        $response->assertOk()->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('applications', [
            'id' => $appId,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('contact_messages', [
            'email' => $user->email,
            'source' => 'adoption_approved',
            'status' => 'approved',
        ]);
    }

    public function test_07_user_can_delete_approved_application_and_matching_notification(): void
    {
        $user = User::factory()->create([
            'email' => 'withdraw_approved@example.com',
            'password' => 'secret12',
            'is_blocked' => false,
        ]);

        $animal = Animal::query()->create([
            'name' => 'Dzēšamais',
            'species' => 'Suns',
            'gender' => 'Vīrietis',
            'age' => 2,
            'description' => 'X',
            'image' => 'https://example.com/x.jpg',
            'category_id' => 1,
        ]);

        $appId = DB::table('applications')->insertGetId([
            'user_id' => $user->id,
            'animal_id' => $animal->id,
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'status' => 'approved',
            'message' => null,
            'phone' => '+37121111111',
            'address' => null,
            'experience' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ContactMessage::query()->create([
            'user_id' => $user->id,
            'name' => 'Administrators',
            'email' => $user->email,
            'message' => 'Apstiprināts.',
            'selected_animals' => (string) $animal->id,
            'source' => 'adoption_approved',
            'status' => 'approved',
            'is_read' => false,
            'sent_at' => now(),
            'moderated_at' => now(),
            'approved_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/my/applications/{$appId}")->assertOk();

        $this->assertDatabaseMissing('applications', ['id' => $appId]);
        $this->assertDatabaseMissing('contact_messages', [
            'email' => $user->email,
            'source' => 'adoption_approved',
            'selected_animals' => (string) $animal->id,
        ]);
    }
}
