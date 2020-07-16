<?php

namespace App\Http\Resources;
// use App\Books;
use Illuminate\Http\Resources\Json\JsonResource;

class Book extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $parent = parent::toArray($request);
        $data['category'] = $this->categories;
        $data = array_merge($parent,$data);

        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'book data',
            'data' => $data,
        ];
    }
}