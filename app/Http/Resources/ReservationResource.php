<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->whenLoaded('user')->id,
                'fullName' => $this->whenLoaded('user')->first_name . " " . $this->whenLoaded('user')->last_name,
                'phone' => $this->whenLoaded('user')->phone,
            ],
            'book' => new BookResource($this->whenLoaded('book')),
            'expirationDate' => $this->expiration_date,
            'isPaid' => $this->is_paid,
            'cost' => $this->cost,
        ];
    }
}
