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

class ReferralsController extends Controller
{
    
    public function list(Request $request){
        // get token from header
        //$token = $request->header('Authorization');
        $user = auth()->user();
        $page = $request->page ? $request->page : 1;
        $limit = $request->limit ? $request->limit : 10;
        $offset = ($page - 1) * $limit;

        $referrals = User::where('referred_by', $user->id)
                    ->select('id', 'name', 'last_name','email', 'created_at')            
                    ->offset($offset)->limit($limit)->get();

        $total = User::where('referred_by', $user->id)->count();
        $data = [
            'referrals' => $referrals,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    
        return response()->json($data);
    }


}
