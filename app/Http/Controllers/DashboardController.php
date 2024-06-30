<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use Auth;

class DashboardController extends Controller
{
    
    public function init(Request $request){
        // get token from header
        //$token = $request->header('Authorization');
        $user = auth()->user();

        $user = User::where('id', $user->id)->first();
        
        // map balance, cents to dollars and round to 2 decimals
        $user->balance = $user->balance / 100;
        $user->balance = round($user->balance, 2);

        $transactions = Transaction::with(['product:id,name', 'status:id,code,name'])->where('user_id', $user->id)
                            ->select('id', 'final_price', 'profit','created_at','status_id','product_id')
                            ->whereHas('status', function($query){
                                $query->where('code', 'completed');
                            })
                            ->where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->limit(10)->get();

        $transactions = $transactions->map(function($transaction){
            $transaction->final_price = $transaction->final_price / 100;
            $transaction->final_price = round($transaction->final_price, 2);

            $transaction->profit = $transaction->profit / 100;
            $transaction->profit = round($transaction->profit, 2);
            return $transaction;
        });

        $data = [
            'name' => $user->name,
            'balance' => $user->balance,
            'transactions' => $transactions
        ];
    
        return response()->json($data);
    }


}
