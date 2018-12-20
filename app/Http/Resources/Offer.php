<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Offer extends JsonResource
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
            'offer_id' => $this->id,
            'restaurant' => $this->restaurant->name,
            'name' => $this->name,
            'description' => $this->description,
            'discount' => $this->discount_percent.'%',
            'from' => $this->from_date,
            'to' => $this->to_date,
            'pic' => asset('storage/'.$this->pic),
            'created_at' => $this->created_at
        ];
    }
}
