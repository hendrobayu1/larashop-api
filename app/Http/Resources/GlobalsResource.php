<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlobalsResource extends JsonResource
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
            'code' => 200,
            'status' => 'success',
            'message' => 'Globals data',
            'data' => parent::toArray($request),
        ];
    }
}