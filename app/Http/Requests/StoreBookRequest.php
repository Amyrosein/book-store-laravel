<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title"        => ['required', 'string', 'max:30'],
            "release_date" => ['required', 'date'],
            "isbn"         => [
                'required',
                'string',
                'max:13',
                'unique:books'
            ],
            "price"        => ['required', 'integer'],
            "genre_id"     => ['required', 'exists:genres,id', 'integer'],
            "author_id"    => ['required', 'exists:authors,id', 'integer'],
        ];
    }
}
