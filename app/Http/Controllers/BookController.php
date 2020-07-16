<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Book;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Books as BookCollection;
use App\Http\Resources\Book as BookResource;

class BookController extends Controller
{
    public function index(){
        $count = DB::table('global_parameter')
                ->where('global_name','count_book_each_page')
                ->value('global_value_number');
        $book = Book::paginate($count);
        return new BookCollection($book);
    }

    public function top(){
        $count = DB::table('global_parameter')
                ->where('global_name','count_book_top')
                ->value('global_value_number');
        $criteria = Book::select(['id','title','slug','description','author','publisher','cover','price','weight','stock','views','status'])
                    ->orderBy('views','desc')
                    ->limit($count)
                    ->get();
        return new BookCollection($criteria);
    }

    public function slug($slug){
        $criteria = Book::where('slug',$slug)->first();
        $criteria->views = $criteria->views+1;
        $criteria->save();
        return new BookResource($criteria);
    }

    public function search($keyword){
        $criteria = Book::select('*')
                    ->where('title','like',"%$keyword%")
                    ->orderBy('views','desc')
                    ->get();
        return new BookCollection($criteria);
    }
}