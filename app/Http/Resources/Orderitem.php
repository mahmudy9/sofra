<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;

class Orderitem extends JsonResource
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
            'order_id' => $this->id,
            'client' => $this->client->name,
            'restaurant' => $this->restaurant->name,
            'offer' => $this->offer ? $this->offer->name : null,
            'order_status' => $this->order_status,
            'price' => $this->price,
            'delivery_fee' => $this->delivery_fee,
            'total' => $this->total,
            'notes' => $this->notes,
            'discount' => $this->discount,
            'created_at' => $this->created_at,
            'products' => ProductResource::collection($this->products),
        ];
    }
}
