<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'product_id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cooking_duration' => $this->cooking_duration,
            'pic' => asset('storage/'.$this->pic),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'restaurant' => $this->restaurant->name
        ];
    }
}
