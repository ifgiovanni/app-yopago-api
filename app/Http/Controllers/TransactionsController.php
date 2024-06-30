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

class TransactionsController extends Controller
{
    
    public function list(Request $request, $status){
        // get token from header
        //$token = $request->header('Authorization');
        $user = auth()->user();
        
        // custom pagination
        $page = $request->page ? $request->page : 1;
        $limit = $request->limit ? $request->limit : 10;
        $offset = ($page - 1) * $limit;

        $user = User::where('id', $user->id)->first();

        $transactions = Transaction::with(['product:id,name,description', 'status:id,code,name'])->where('user_id', $user->id)
                            ->select('id', 'final_price', 'profit','authorization','status_id','created_at', 'product_id')
                            ->orderBy('created_at', 'desc')
                            ->where('user_id', $user->id)
                            ->whereHas('status', function($query) use ($status){
                                if($status != 'all'){
                                    $query->where('code', $status);
                                }
                            })
                            ->offset($offset)->limit($limit)->get();

        $total = Transaction::where('user_id', $user->id)
                    ->whereHas('status', function($query) use ($status){
                        if($status != 'all'){
                            $query->where('code', $status);
                        }
                    })->count();

        $transactions = $transactions->map(function($transaction){
            $transaction->final_price = $transaction->final_price / 100;
            $transaction->final_price = round($transaction->final_price, 2);

            $transaction->profit = $transaction->profit / 100;
            $transaction->profit = round($transaction->profit, 2);
            return $transaction;
        });

        $data = [
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    
        return response()->json($data);
    }


}
