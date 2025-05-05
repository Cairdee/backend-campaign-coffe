<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'category'      => $this->category,
            'description'   => $this->description,
            'image'         => url($this->image),
            'price'         => (int) $this->price,
            'rating'        => (float) $this->rating,
            'review_count'  => (int) $this->review_count,
        ];
    }
}
