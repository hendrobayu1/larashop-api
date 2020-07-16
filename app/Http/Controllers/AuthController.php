<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request){
        $user = User::where('email','=',$request->email)->first();
        $status = 'error';
        $message = '';
        $data = null;
        $code = 401;
        if ($user){
            if(Hash::check($request->password, $user->password)){
                $user->generateToken();
                $status = 'success';
                $message = 'login sukses';
                $data = $user->toArray();
                $code = 200;
            }else{
                $message = 'Login gagal, password salah';
            }
        }else{
            $message = 'Login gagal, username salah';
        }
        return response()->json([
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ],$code);
    }

    public function infoUser(Request $request){
        $user_data = DB::select("select u.Email,u.name Nama,u.roles Roles,
        concat(u.address,' ',c.city,' ',p.province) Alamat,u.phone 'Telp',
        convert(varchar,u.created_at,103) 'Tgl Registrasi',isnull(u.avatar,'') Avatar 
        from users u inner join cities c on c.id=u.city 
        inner join provinces p on p.id=u.province_id 
        where u.email=?", [$request->email]);
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Retrive profile data',
            'data' => $user_data,
        ]);
    }

    public function register(Request $request){
        $validate = Validator::make($request->all(), 
                    ['name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:6']
                );
        $status = 'error';
        $message = '';
        $data = null;
        $code = 400;
        if ($validate->fails()){
            $message = $validate->errors();
        }else{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => json_encode(['CUSTOMER']),
            ]);

            if ($user){
                $user->generateToken();
                $status = 'success';
                $message = 'register successfully';
                $data = $user->toArray();
                $code = 200;
            }else{
                $message = 'register failed';
            }
        }

        return response()->json([
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ],$code);
    }

    public function logout(Request $request){
        $user = Auth::user();
        if  ($user){
            $user->api_token=null;
            $user->save();
        }
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Logout successfully',
            'data' => []
        ],200);
    }
}