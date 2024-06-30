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

class ProfileController extends Controller
{
    
    public function getLogLogins(Request $request){
        $user = auth()->user();
        
        $page = $request->page ? $request->page : 1;
        $limit = $request->limit ? $request->limit : 10;
        $offset = ($page - 1) * $limit;

        $logs = LogLogin::select("id", "ip", "login_at", "user_agent", "result")->where("user_id", $user->id)
                    ->orderBy('id', 'desc')->limit($limit)
                    ->offset($offset)->get();

        $total = LogLogin::where("user_id", $user->id)->count();

        $data = [
            'logs' => $logs,
            'page' => $page,
            'total' => $total,
            'limit' => $limit
        ];

        return response()->json($data);
    }

}
