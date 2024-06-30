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

class DepositController extends Controller
{
    
    public function list(Request $request){
        // get token from header
        //$token = $request->header('Authorization');
        $user = auth()->user();

        $options = DepositOption::select("id", "name", "description", "image", "validation_field", "validation_desc", "active")->get();

        $data = [
            'options' => $options
        ];
    
        return response()->json($data);
    }


}
