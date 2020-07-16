<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Categories as CategoryResource;
use App\Http\Resources\Category as KategoriResouce;
class CategoryController extends Controller
{
    public function index(){
        $count = DB::table('global_parameter')
                ->where('global_name','count_category_each_page')
                ->value('global_value_number');
        $data = Category::paginate($count);
        return new CategoryResource($data);
    }

    public function random(){
        $count = DB::table('global_parameter')
                ->where('global_name','count_book_category_random')
                ->value('global_value_number');
        $criteria = Category::select(['id','name','slug','image','status'])
            ->inRandomOrder()
            ->limit($count)
            ->get();
        
        return new CategoryResource($criteria);
    }

    public function slug($slug){
        $kriteria = Category::where('slug',$slug)->first();
        return new KategoriResouce($kriteria);
    }
}
