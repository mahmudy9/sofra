<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class reviewsResource extends JsonResource
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
            'review_id' =>$this->id,
            'restaurant' =>$this->restaurant->name,
            'client' =>$this->client->name,
            'review' =>$this->review,
            'rating' =>$this->rating,
            'created_at' =>$this->created_at,
            

        ];
    }
}


