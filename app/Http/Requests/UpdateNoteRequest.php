<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Instead of calling Gate::authorize('update', $note) in the controller,
        // we can actually do it right here in the Form Request!

        // $this->route('note') grabs the Note model currently being updated
        // from the URL (e.g. /notes/5 -> grabs Note with ID 5)
        return $this->user()->can('update', $this->route('note'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notes' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
        ];
    }
}
