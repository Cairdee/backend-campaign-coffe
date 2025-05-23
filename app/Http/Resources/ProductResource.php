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
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'category' => $this->category->name ?? null,
        'price' => $this->price,
        'description' => $this->description,
        'rating' => $this->rating,
        'review_count' => $this->review_count,
        'image' => $this->product && $this->product->image
    ? url('storage/images/menu/' . basename($this->product->image))
    : null,

    ];
}

}
