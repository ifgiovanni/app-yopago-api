<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\UserRole;
use App\Models\Role;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use App\Models\LogLogin;
use Auth;

class CouponsController extends Controller
{
    
    protected function randomPin() {
        $timestamp = strval(time());
        $p1 = substr(strrev($timestamp), 0, 6);
        
        $rnd_1 = rand(100100, 999999);
        $rnd_2 = rand(101000, 999999);
        $rnd_3 = rand(100100, 999999);
        $rnd_4 = rand(110100, 999999);
    
        $c = $p1 . '-' . $rnd_1 . '-' . $rnd_2 . '-' . $rnd_3 . '-' . $rnd_4;
        return $c;
    }

    public function new(Request $request){

        $user = auth()->user();

        $amount = $request->amount;
        $amount = $amount * 100;

        //check if user has enough balance
        if($user->balance < $amount){
            return response()->json(['message' => 'Balance insuficiente']);
        }

        $user->balance = $user->balance - $amount;
        $user->save();

        $code = $this->randomPin();

        $coupon = new Coupon();
        $coupon->code = $code;
        $coupon->status = "unused";
        $coupon->amount = $amount;
        $coupon->created_by = $user->id;
        $coupon->save();

        $data = [
            'coupon' => $coupon
        ];

        return response()->json($data);
    }

    public function list(Request $request, $status = "all"){

        $user = auth()->user();

        // custom pagination
        $page = $request->page ? $request->page : 1;
        $limit = $request->limit ? $request->limit : 10;

        $offset = ($page - 1) * $limit;
        $coupons = Coupon::with(['redeemBy:id,name,last_name',
                            'createdBy:id,name,last_name', 'blockedBy:id,name,last_name'])
                            ->where('created_by', $user->id)
                    ->when($status != 'all', function($query) use ($status){
                        return $query->where('status', $status);
                    })
                    ->select('id', 'code', 'status', 'amount', 'created_at', 'created_by','redeem_at','redeem_by','blocked_by','blocked_at' )
                    ->orderBy('created_at', 'desc')
                    ->offset($offset)->limit($limit)->get();

        $total = Coupon::where('created_by', $user->id)
                ->when($status != 'all', function($query) use ($status){
                    return $query->where('status', $status);
                })->count();

        $coupons = $coupons->map(function($coupon){
            $coupon->amount = $coupon->amount / 100;
            unset($coupon->created_by);
            unset($coupon->createdBy->id);
            if($coupon->redeem_by !== null){
                unset($coupon->redeem_by);
                unset($coupon->redeemBy->id);
            }else{
                unset($coupon->redeem_by);
                unset($coupon->redeem_at);
            }

            if($coupon->blocked_by !== null){
                unset($coupon->blocked_by);
                unset($coupon->blockedBy->id);
            }else{
                unset($coupon->blocked_by);
                unset($coupon->blocked_at);
            }
            return $coupon;
        });

        $data = [
            'coupons' => $coupons,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];

        return response()->json($data);
    }

    public function redeemed(Request $request){

        $user = auth()->user();

        // custom pagination
        $page = $request->page ? $request->page : 1;
        $limit = $request->limit ? $request->limit : 10;

        $offset = ($page - 1) * $limit;
        $coupons = Coupon::with(['redeemBy:id,name,last_name',
                            'createdBy:id,name,last_name'])
                    ->where('status', 'redeemed')
                    ->where('redeem_by', $user->id)
                    ->select('id', 'code', 'status', 'amount', 'created_at', 'created_by','redeem_at','redeem_by' )
                    ->offset($offset)->limit($limit)->get();

        $total = Coupon::where('redeem_by', $user->id)
        ->where('status', 'redeemed')->count();

        $coupons = $coupons->map(function($coupon){
            $coupon->amount = $coupon->amount / 100;
            unset($coupon->created_by);
            unset($coupon->createdBy->id);
            if($coupon->redeem_by !== null){
                unset($coupon->redeem_by);
                unset($coupon->redeemBy->id);
            }else{
                unset($coupon->redeem_by);
                unset($coupon->redeem_at);
            }

            return $coupon;
        });

        $data = [
            'coupons' => $coupons,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];

        return response()->json($data);
    }

    public function redeem(Request $request){

        $user = auth()->user();
        $code = $request->code;
        $coupon = Coupon::where('code', $code)
                    ->where('status', 'unused')
                    ->select("id", "code", "status", "amount", "created_by")->first();

        if($coupon === null){
            return response()->json(['message' => 'El cupón no existe o ya ha sido utilizado']);
        }

        // avoid to redeem own coupon
        if($coupon->created_by == $user->id){
            return response()->json(['message' => 'No puedes redimir tu propio cupón']);
        }

        $coupon->status = 'redeemed';
        $coupon->redeem_by = $user->id;
        $coupon->redeem_at = now();
        $coupon->save();

        // add balance to user
        $user = User::find($user->id);
        $user->balance = $user->balance + $coupon->amount;
        $user->save();

        $data = [
            'coupon' => $coupon
        ];

        return response()->json($data);
    }
}
