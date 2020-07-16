<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\User;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function sendNotif(Request $request){
        $user = Auth::user();
        if($user){
            $event = "Test notifikasi !";
            Notification::send($user,new \App\Notifications\Notifikasi($event));
        }
    }
}