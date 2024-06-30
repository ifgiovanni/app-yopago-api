<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\UserRole;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Auth;

class ProductsController extends Controller
{
    
    public function list(Request $request){
        $user = auth()->user();
        $user_role = UserRole::where('user_id', $user->id)->first();
        $products = Product::where("active", 1)->get();

        // map base_price by role column base_price
        
        $products = $products->map(function($product) use ($user_role){
            $product->base_price = $product->base_price * (1 + $user_role->role->per_increase);
            $product->base_price = $product->base_price * (1 - $user_role->role->per_profit);
            // cents to dollars and round to 2 decimals
            $product->base_price = $product->base_price / 100;
            $product->base_price = round($product->base_price, 2);
            return $product;
        });

        return response()->json($products);
    }

    public function processTransaction(Request $request){
        $product_id = $request->product;
        $user = auth()->user();
        $user = User::where('id', $user->id)->first();
        $user_role = UserRole::where('user_id', $user->id)->first();

        $product = Product::where('id', $product_id)->first();
        $base_price = $product->base_price;
        // adding product increment (per_increase)
        $product->base_price = $product->base_price * (1 + $user_role->role->per_increase);
        $cost = round($product->base_price, 0);
        // adding profit increment (per_profit)
        $profit = $product->base_price * $user_role->role->per_profit;
        $product->base_price = $product->base_price - $profit;
        $final_price = round($product->base_price, 0);

        // check if user has enough balance
        if($user->balance < $final_price){
            // store with status insufficient.funds
            $process = false;
            $status = Status::where('code', 'insufficient.funds')->first();

        }else{
            // store with status success
            $process = true;
            $status = Status::where('code', 'completed')->first();
        }

        try {
            DB::beginTransaction();
            
            $details = [
                'account' => $request->account ?? null,
                'mail' => $request->mail ?? null,
            ];

            $transaction = new Transaction();
            $transaction->authorization = "T" . $this->get_custom_hex();
            $transaction->user_id = $user->id;
            $transaction->product_id = $product_id;
            $transaction->per_profit = $user_role->role->per_profit;
            $transaction->per_increase = $user_role->role->per_increase;
            $transaction->profit = $profit;
            $transaction->base_price = $base_price;
            $transaction->final_price = $final_price;
            $transaction->details = json_encode($details);
            $transaction->status_id = $status->id;
            $transaction->save();
            
            if($process){
                // update user balance
                $user->balance = $user->balance - $final_price;
                $user->save();
                DB::commit();
                
                return response()->json([
                    'message' => 'Transacción exitosa' . $this->get_custom_hex()
                ]);
            }else{
                DB::commit();
                return response()->json([
                    'message' => 'No posee saldo suficiente para realizar la transacción. (Balance actual: $' . round($user->balance/100, 2) . ')'
                ], 400);
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
        }
       
    }

}
