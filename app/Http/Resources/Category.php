<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
// use App\Book;
use Illuminate\Support\Facades\DB;

class Category extends JsonResource
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
        $count = DB::table('global_parameter')
                ->where('global_name','count_book_in_detil_category_each_page')
                ->value('global_value_number');
        $data['books'] = $this->books()->paginate($count);
        $data = array_merge($parent,$data);
        return [
            'code' => 200,
            'status' => 'success',
            'message' => 'category data',
            'data' => $data,
        ];
    }
}
