<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = "books";
    protected $fillable = ["title","slug","description"];

    public function categories(){
        return $this->belongsToMany('App\Category');
    }
}
