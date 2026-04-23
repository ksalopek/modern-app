<?php

namespace Tests\Feature\Api;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    // This trait will automatically reset the database after each test.
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_notes_api(): void
    {
        // Act: Make a request to the API without being authenticated.
        $response = $this->getJson('/api/notes');

        // Assert: Check that the server responded with a 401 Unauthorized status.
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_their_own_notes(): void
    {
        // Arrange: Create a user and create 5 notes specifically for this user.
        $user = User::factory()->create();
        Note::factory()->count(5)->create(['user_id' => $user->id]);

        // Act: Authenticate as this user (using the 'sanctum' guard) and hit the API endpoint.
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/notes');

        // Assert: Check that the request was successful (200 OK).
        $response->assertStatus(200);
        // Assert: Check that the JSON response contains exactly 5 notes in the 'data' key.
        $response->assertJsonCount(5, 'data');
    }

    public function test_user_cannot_fetch_another_users_notes(): void
    {
        // Arrange: Create two separate users.
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();

        // Create 3 notes for User One.
        Note::factory()->count(3)->create(['user_id' => $userOne->id]);
        // Create 4 notes for User Two.
        Note::factory()->count(4)->create(['user_id' => $userTwo->id]);

        // Act: Authenticate as User One and request the notes.
        $response = $this->actingAs($userOne, 'sanctum')->getJson('/api/notes');

        // Assert: Check that the request was successful.
        $response->assertStatus(200);
        // Assert: Crucially, check that we only received the 3 notes belonging to User One,
        // and not the 4 notes from User Two.
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_delete_note()
    {
        // Arrange: Create a user and create 5 notes specifically for this user.
        $user = User::factory()->create();
        Note::factory()->count(5)->create(['user_id' => $user->id]);
        $note = $user->notes()->first();

        $response = $this->actingAs($user, 'sanctum')->deleteJSON('/api/notes/' . $note->id);

        $response->assertStatus(204);
        $this->assertSoftDeleted('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_user_update_note()
    {
        // Arrange: Create a user and create 5 notes specifically for this user.
        $user = User::factory()->create();
        Note::factory()->count(1)->create(['user_id' => $user->id]);
        $note = $user->notes()->first();
        // Define the new data we want to send in the request.
        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'notes' => 'This is an extra note.',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJSON('/api/notes/' . $note->id,$updatedData);
        $response->assertStatus(200);

        // Assert: Check that the JSON response contains the updated title.
        $response->assertJson([
            'title' => 'Updated Title',
            'content' => 'Updated content.',git
            'notes' => 'This is an extra note.',
        ]);

        // Assert: Check that the database was actually updated with the new data.
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Title',
            'content' => 'Updated content.',
        ]);
    }
}
