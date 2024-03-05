<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'isbn'        => $this->isbn,
            'price'       => $this->price,
            'releaseDate' => $this->release_date,
            'genre'       => $this->whenLoaded('genre', '12'),
            'author'      => [
                'fullName' => $this->author->first_name . " " . $this->author->last_name,
                'city'     => $this->author->city->name,
            ],
        ];
    }
}
