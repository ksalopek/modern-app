<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_note_shows_a_flash_message(): void
    {
        // 1. Arrange: Create a user to act as.
        $user = User::factory()->create();

        // The data for our new note.
        $noteData = [
            'title' => 'Test Note Title',
            'content' => 'This is the content of the test note.',
            'notes' => 'Some extra notes.',
            'tags' => 'testing, laravel',
        ];

        // 2. Act: Simulate the user submitting the form.
        $response = $this->actingAs($user)
                         ->post(route('notes.store'), $noteData);

        // 3. Assert: Check that everything happened as expected.

        // Assert that the response was a redirect back to the previous page.
        $response->assertRedirect();

        // Assert that the session now has a 'message' key.
        $response->assertSessionHas('message', 'Note created successfully!');
    }

    public function test_updating_a_note_shows_a_flash_message(): void
    {
        // Arrange
        $user = User::factory()->create();
        $note = \App\Models\Note::factory()->create(['user_id' => $user->id]);
        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated content.',
            'notes' => 'Updated extra notes.',
            'tags' => 'updated',
        ];

        // Act
        $response = $this->actingAs($user)
                         ->put(route('notes.update', $note), $updatedData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note updated successfully!');
    }

    public function test_soft_delete_a_note_shows_a_flash_message(): void
    {
        // 1. Arrange: Create a user to act as.
        $user = User::factory()->create();
        $note = \App\Models\Note::factory()->create(['user_id' => $user->id]);

        //now need to attempt to delete the new note
        // Act
        $response = $this->actingAs($user)
            ->delete(route('notes.destroy', $note));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note moved to trash!');
    }

    public function test_restore_delete_a_note_shows_a_flash_message(): void
    {
        // 1. Arrange: Create a user to act as.
        $user = User::factory()->create();
        $note = \App\Models\Note::factory()->create(['user_id' => $user->id]);

        //now need to attempt to delete the new note
        // Act
        $response = $this->actingAs($user)
            ->delete(route('notes.destroy', $note));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note moved to trash!');

        //now permanently delete the note
        $response = $this->actingAs($user)
            ->put(route('notes.restore', $note->id));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note restored successfully!');
    }

    public function test_force_delete_a_note_shows_a_flash_message(): void
    {
        // 1. Arrange: Create a user to act as.
        $user = User::factory()->create();
        $note = \App\Models\Note::factory()->create(['user_id' => $user->id]);

        //now need to attempt to delete the new note
        // Act
        $response = $this->actingAs($user)
            ->delete(route('notes.destroy', $note));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note moved to trash!');

        //now permanently delete the note
        $response = $this->actingAs($user)
            ->delete(route('notes.forceDelete', $note->id));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('message', 'Note permanently deleted!');
    }
}
