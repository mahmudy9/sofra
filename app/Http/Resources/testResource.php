<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\reviewsResource;
class testResource extends JsonResource
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
            'restaurant_id' =>$this->id,
            'name' =>$this->name,
            'city' =>$this->city->name,
            'neighborhood' =>$this->neighborhood->name,
            'category' =>$this->category->name,
            'min_order' =>$this->min_order,
            'delivery_fee' =>$this->delivery_fee,
            'deliverytime_from' =>$this->deliverytime_from,
            'deliverytime_to' =>$this->deliverytime_to,
            'order_days' =>$this->order_days,
            'reviews' => reviewsResource::collection($this->reviews),
            'products' =>ProductResource::collection($this->products)

        ];
    }
}
