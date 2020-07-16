<?php

namespace App\Http\Controllers;

use App\Book;
use App\City;
use App\Province;
use App\Http\Resources\Provinces as ResourceProvices;
use App\http\Resources\Cities as ResourceCities;
use App\Order;
use App\BookOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function provinces(){
        return new ResourceProvices(Province::get());
    }

    public function cities(){
        return new ResourceCities(City::get());
    }

    public function couriers(){
        $couriers = DB::select('select * from couriers');
        return response()->json([
            'code' => 200,
            'status' => 'succcess',
            'message' => 'courier data',
            'data' => $couriers,
        ],200);
    }

    public function shipping(Request $request){
        $user = Auth::user();
        $status = 'error';
        $message = '';
        $data = '';
        $code = 400;
        if($user){
            $validasi = Validator::make($request->all(),[
                'name' => 'required',
                'address' => 'required',
                'phone' => 'required',
                'province_id' => 'required',
                'city' => 'required',
            ]);
            // $validasi = $this->validate($request,[
            //     'name' => 'required',
            //     'address' => 'required',
            //     'phone' => 'required',
            //     'province_id' => 'required',
            //     'city_id' => 'required',
            // ]);
            if ($validasi->fails()){
                $message = $validasi->errors();
            }else{
                $user->address = $request->address;
                $user->phone = $request->phone;
                $user->province_id = $request->province_id;
                $user->city = $request->city;
                if($user->save()){
                    $code = 200;
                    $status = 'success';
                    $message = 'Update shipping success';
                    $data = $user->toArray();
                }else{
                    $code = 401;
                    $status = 'failed';
                    $message = 'Update shipping failed';
                }
            }
        }else{
            $message = 'User not found !';
        }
        return response()->json([
            'code' => $code,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function validateCart($carts){
        $safe_carts = [];
        $total = [
            'qty_before' => 0,
            'qty' => 0,
            'price' => 0,
            'weight' => 0,
        ];
        $idx=0;
        foreach($carts as $cart){
            $id = (int)$cart['id'];
            $total['qty_before'] += (int)$cart['quantity'];
            $book = Book::find($id);
            if($book){
                // Buku ada dalam sistem
                if($book->stock>0){
                    // Cek stok
                    $safe_carts[$idx]['id'] = $book->id;
                    $safe_carts[$idx]['title'] = $book->title;
                    $safe_carts[$idx]['cover'] = $book->cover;
                    $safe_carts[$idx]['price'] = $book->price;
                    $safe_carts[$idx]['weight'] = $book->weight;
                    if($book->stock < (int)$cart['quantity']){
                        $safe_carts[$idx]['quantity'] = $book->stock;
                    }else{
                        $safe_carts[$idx]['quantity'] = (int)$cart['quantity'];
                    }
                    $total['qty'] += $safe_carts[$idx]['quantity'];
                    $total['price'] += ($safe_carts[$idx]['quantity'] * $book->price);
                    $total['weight'] += ($safe_carts[$idx]['weight'] * $safe_carts[$idx]['quantity']);
                    $idx++;
                }
            }
        };
        return [
            'safe_carts' => $safe_carts,
            'total' => $total,
        ];
    }

    public function getService($data){
        $url_ongkir = 'https://api.rajaongkir.com/starter/cost';
        $key = "791968c2a8f0e2a3d5d3d68dbe40155b";
        $post_data = http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl,[
            CURLOPT_URL => $url_ongkir,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => [
                "content-type => application/x-www-form-urlencoded",
                "key:".$key,
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return [
            'error' => $error,
            'response' => $response,
        ];
    }

    public function service(Request $request){
        //Validasi kelengkapan data
        $this->validate($request,[
            'courier' => 'required',
            'carts' => 'required',
        ]);

        $user = Auth::user();
        $status = 'error';
        if($user){
            $destination = $user->city;
            if ($destination>0){
                $origin = DB::table('global_parameter')
                            ->where('global_name','origin')
                            ->value('global_value_number');
                $courier = $request->courier;
                $carts = $request->carts;
                $carts = json_decode($carts,true);

                //Validasi data belanja
                $validCarts = $this->validateCart($carts);
                $data['safe_carts'] = $validCarts['safe_carts'];
                $data['total'] = $validCarts['total'];

                $qty_different = $validCarts['total']['qty_before'] <> $validCarts['total']['qty'];

                $weight = $validCarts['total']['weight']*1000;
                if ($weight>0){
                    //request ke API rajaongkir
                    $parameter = [
                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $courier,
                    ];
                    $response_service = $this->getService($parameter);
                    if($response_service['error']==null){
                        $services = [];
                        $response = json_decode($response_service['response']);

                        $costs = $response->rajaongkir->results[0]->costs;
                        foreach($costs as $cost){
                            $services[] = [
                                'service' => $cost->service,
                                'cost' => $cost->cost[0]->value,
                                'estimation' => str_replace('hari','',trim($cost->cost[0]->etd)),
                                'resume' => $cost->service." [ Rp.".number_format($cost->cost[0]->value)." Estimation: ".
                                str_replace('hari','',trim($cost->cost[0]->etd))."day(s) ]",
                            ];
                        }
                        if(count($services)>0){
                            $data['service'] = $services;
                            $status = "success";
                            $message = "Getting services success";
                        }else{
                            $message = "Service unavailable !";
                        }
                    }else{
                        $message = "cURL Error : ".$response_service['error'];
                    }
                }else{
                    $message = "Weight invalid !";
                }
            }else{
                $message = "Destination not set !";
            }
        }else{
            $message = "User not found !";
        }
        return response()->json([
            'code' => 200,
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ],200);
    }

    public function payment(Request $request){
        $status = 'error';
        $message = "";
        $data = [];
        $error =0 ;

        $user = Auth::user();
        if($user){
            $this->validate($request,[
                'courier' => 'required',
                'service' => 'required',
                'carts' => 'required',
            ]);
            
            DB::beginTransaction();
            try{
                $origin = DB::table('global_parameter')
                            ->where('global_name','origin')
                            ->value('global_value_number');
                $destination = $user->city;
                $city_row = City::find($destination);
                $city_name = $city_row->city;
                $province_row = Province::find($user->province_id);
                $province_name = $province_row->province;
                if($destination<=0){
                    $error++;
                }
                $courier = $request->courier;
                $service = $request->service;
                $carts = json_decode($request->carts,true);

                $order = new Order;
                $order->user_id = $user->id;
                $order->total_bill = 0;
                $order->invoice_number = date('YmdHis');
                $order->courier_service = $courier.'-'.$service;
                $order->address = $user->address.' '.$city_name.' '.$province_name;
                $order->status = DB::table('master_status_order')
                                ->where('id',1)
                                ->value('status_order');
                if($order->save()){
                    $total_price = 0;
                    $total_weight = 0;
                    foreach($carts as $cart){
                        $id = (int)$cart['id'];
                        $qty = (int)$cart['quantity'];
                        $book = Book::find($id);
                        if($book){
                            if($book->stock>=$qty){
                                $total_price += $book->price*$qty;
                                $total_weight += $book->weight*$qty;

                                $book_order = new BookOrder;
                                $book_order->order_id = $order->id;
                                $book_order->book_id = $id;
                                $book_order->qty = $qty;
                                $book_order->price = $book->price;
                                $book_order->discount = 0;
                                if($book_order->save()){
                                    $book->stock = $book->stock - $qty;
                                    $book->save();
                                }
                            }else{
                                $error++;
                                throw new \Exception('Out of stock !');
                            }
                        }else{
                            $error++;
                            throw new \Exception('Book is not found !');
                        }
                    }

                    $totalBill = 0;
                    $weight = $total_weight*1000;
                    if($weight<=0){
                        $error++;
                        throw new \Exception('Weight null !');
                    }

                    $data = [
                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $courier,
                    ];

                    $data_cost = $this->getService($data);
                    if($data_cost['error']){
                        $error++;
                        throw new \Exception('Courier service unavailable !');
                    }

                    $response = json_decode($data_cost['response']);
                    $costs = $response->rajaongkir->results[0]->costs;
                    $service_cost = 0;
                    foreach($costs as $cost){
                        if($service==$cost->service){
                            $service_cost = $cost->cost[0]->value;
                            break;
                        }
                    }
                    if($service_cost<=0){
                        $error++;
                        throw new \Exception('Service cost invalid !');
                    }

                    $total_bill = $total_price + $service_cost;
                    $order->total_bill = $total_bill;
                    $order->courier_cost = $service_cost;
                    if($order->save()){
                        if($error==0){
                            DB::commit();
                            $status = "success";
                            $message = "Transaction success";
                            \Veritrans_Config::$serverKey = "SB-Mid-server-Frb0Nj2ejp5i09N1aJGp8i8B";
                            \Veritrans_Config::$isProduction = false;
                            \Veritrans_Config::$isSanitized = true;
                            \Veritrans_Config::$is3ds = true;
                            $transaction_data = [
                                'transaction_details' => [
                                    'order_id' => $order->invoice_number,
                                    'gross_amount' => $total_bill,
                                ]
                            ];
                            $payment_link = \Veritrans_Snap::createTransaction($transaction_data)->redirect_url;
                            $data = [
                                'payment_link' => $payment_link,
                            ];
                            // $data = [
                            //     "order_id" => $order->id,
                            //     "total_bill" => $total_bill,
                            //     "invoice_number" => $order->invoice_number,
                            // ];
                        }else{
                            $message = "There are ".$error." errors";
                        }
                    }
                }
            }catch(\Exception $ex){
                $message = $ex->getMessage();
                DB::rollback();               
            }
        }else{
            $message = "User not found !";
        }

        return response()->json([
            "code" => 200,
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ],200);
    }
    public function myOrder(Request $request){
        $user = Auth::user();
        $status = 'error';
        $message = '';
        $data = [];
        if($user){
            $orders = Order::select('*')
                    ->where('user_id','=',$user->id)
                    ->orderBy('id','DESC')
                    ->get();
            $status = "success";
            $message = "Retrive data my order";
            $data = $orders;
        }else{
            $message = "User not found !";
        }
        return response()->json([
            "code" => 200,
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ],200);
    }
}