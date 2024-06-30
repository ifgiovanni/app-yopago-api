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
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use App\Models\LogLogin;
use Auth;

class ClientsController extends Controller
{
    
    public function list(Request $request){
        $user = auth()->user();
        $user_role = UserRole::where('user_id', $user->id)->first();
        // check if rol is admin or self user
        if($user_role->role->code != "admin"){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $page_clients = $request->page_clients ? $request->page_clientss : 1;
        $limit_clients = $request->limit_clients ? $request->limit_clients : 10;
        $offset_clients = ($page_clients - 1) * $limit_clients;

        // could be all, unverified_phone, unverified_mail, kyc_pending, banned, with_balance, 
        $status = $request->status ? $request->status : "all";

        $clients = User::select("id", "name", "last_name", "referral_code","email","created_at", "balance", "last_login")
                        ->when($status == "unverified_phone", function($query){
                            return $query->where("phone_verified", 0);
                        })
                        ->when($status == "unverified_mail", function($query){
                            return $query->where("email_verified", 0);
                        })
                        ->when($status == "kyc_pending", function($query){
                            return $query->where("kyc", 0);
                        })
                        ->when($status == "banned", function($query){
                            return $query->where("banned", 1);
                        })
                        ->when($status == "with_balance", function($query){
                            return $query->where("balance", ">", 0);
                        })
                        ->offset($offset_clients)->limit($limit_clients)
                        ->orderBy('created_at', 'desc')
                        ->get();

        // map balance to cents to dollars
        $clients->map(function($client){
            $client->balance = number_format($client->balance / 100, 2);
            return $client;
        });

        $total_clients = User::count();

        $data = [
            'clients' => $clients,
            'page' => $page_clients,
            'total' => $total_clients,
            'limit' => $limit_clients

        ];

        return response()->json($data);
    }

    public function getRoles(Request $request){

        $user = auth()->user();

        $user_role = UserRole::where('user_id', $user->id)->first();
        // check if rol is admin or self user
        if($user_role->role->code != "admin"){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roles = Role::select("code", "name", "per_profit", "per_increase")->get();
        // map roles to percentage
        $roles->map(function($role){
            $role->per_profit = $role->per_profit * 100;
            $role->per_increase = $role->per_increase * 100;
            return $role;
        });
        $data = [
            'roles' => $roles
        ];
        return response()->json($data);
    }
    
    public function getUserDetails(Request $request, $id){

        $user = auth()->user();

        $user_role = UserRole::where('user_id', $user->id)->first();
        // check if rol is admin or self user
        if($user_role->role->code != "admin" && $user->id != $id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $client = User::select("id", "name", "last_name", "referral_code", "email","created_at", "balance", "last_login", "deposits", "withdrawals", "country", "address", "kyc", "phone_number", "phone_verified", "email_verified")->where("id", $id)->first();
        $client->balance = number_format($client->balance / 100, 2);
        $client->deposits = number_format($client->deposits / 100, 2);
        $client->withdrawals = number_format($client->withdrawals / 100, 2);

        // get user role
        $role = UserRole::select("role_id")->where("user_id", $id)->first();
        $client->role = Role::select("code", "name", "per_profit", "per_increase")->where("id", $role->role_id)->first();
        $client->role->per_profit = $client->role->per_profit * 100;
        $client->role->per_increase = $client->role->per_increase * 100;

        // get user transactions
        $page_transactions = $request->page_transactions ? $request->page_transactions : 1;
        $limit_transactions = $request->limit_transactions ? $request->limit_transactions : 10;
        $offset_transactions = ($page_transactions - 1) * $limit_transactions;

        $transactions = Transaction::with(['product:id,name', 'status:id,code,name'])->where('user_id', $id)
                            ->select('id', 'final_price', 'profit','authorization','status_id','created_at', 'product_id')
                            ->orderBy('created_at', 'desc')
                            ->offset($offset_transactions)->limit($limit_transactions)
                            ->get();
        $total_transactions = Transaction::where('user_id', $id)->count();
        $transactions = $transactions->map(function($transaction){
            $transaction->final_price = $transaction->final_price / 100;
            $transaction->final_price = round($transaction->final_price, 2);

            $transaction->profit = $transaction->profit / 100;
            $transaction->profit = round($transaction->profit, 2);
            return $transaction;
        });

        // get  user logs
        $logs = LogLogin::select("id", "ip", "login_at", "user_agent", "result")->where("user_id", $id)->orderBy('id', 'desc')->get();

        $data = [
            'client' => $client,
            'transactions' => [
                'data' => $transactions,
                'page' => $page_transactions,
                'total' => $total_transactions,
                'limit' => $limit_transactions
            ],
            'logs' => $logs
        ];

        return response()->json($data);
    }
}
