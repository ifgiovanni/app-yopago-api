<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DepositOption;
use Auth;

class DepositController extends Controller
{
    
    public function list(Request $request){
        // get token from header
        //$token = $request->header('Authorization');
        $user = auth()->user();

        $options = DepositOption::select("id", "name", "description", "image", "validation_field", "active", "options")->get();

        $data = [
            'options' => $options
        ];
    
        return response()->json($data);
    }

    public function store(Request $request){
        
        $user = auth()->user();
        
        $user_role = UserRole::where('user_id', $user->id)->first();
        // check if rol is admin or self user
        if($user_role->role->code != "admin"){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $type = $request->input('type');
        $name = $request->input('name');
        $description = $request->input('description');
        $account_number = $request->input('account_number');
        $account_name = $request->input('account_name');
        $account_type = $request->input('account_type');
        $description = $request->input('description');
        $details = $request->input('details');
        $active = $request->input('status');
        $validation_field = $request->input('validation_field');

        // make json with account_number, account_name, account_type, description, details
        $options = [
            'account_number' => $account_number,
            'account_name' => $account_name,
            'account_type' => $account_type,
            'description' => $description,
            'details' => $details
        ];

        // if id is set, update
        if($request->input('id')){
            $deposit_option = DepositOption::find($request->input('id'));
            $deposit_option->name = $name;
            $deposit_option->type = $type;
            $deposit_option->description = $description;
            $deposit_option->options = json_encode($options);
            $deposit_option->active = $active;
            $deposit_option->validation_field = $validation_field;
            $deposit_option->save();

            $data = [
                'message' => 'Deposit option updated successfully'
            ];
        
            return response()->json($data);
        }else{
            // if id is not set, create
            $deposit_option = new DepositOption();
            $deposit_option->name = $name;
            $deposit_option->type = $type;
            $deposit_option->description = $description;
            $deposit_option->options = json_encode($options);
            $deposit_option->active = $active;
            $deposit_option->validation_field = $validation_field;
            $deposit_option->save();

            $data = [
                'message' => 'Deposit option created successfully'
            ];
        
            return response()->json($data);
        }

        $data = [
            'message' => 'Deposit option created successfully'
        ];
    
        return response()->json($data);

    }

}
