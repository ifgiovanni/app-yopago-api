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

class ApiController extends Controller
{
    
    public function test(Request $request){
        $url = "https://mocktarget.apigee.net/xml";
        // get from api
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        // convert xml to json
        $data = $this->XML2JSON($output);
        $data = json_decode($data, true);
        return response()->json($data);
    }

    protected function XML2JSON($xml) {
        function normalizeSimpleXML($obj, &$result) {
            $data = $obj;
            if (is_object($data)) {
                $data = get_object_vars($data);
            }
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $res = null;
                    normalizeSimpleXML($value, $res);
                    if (($key == '@attributes') && ($key)) {
                        $result = $res;
                    } else {
                        $result[$key] = $res;
                    }
                }
            } else {
                $result = $data;
            }
        }
        normalizeSimpleXML(simplexml_load_string($xml), $result);
        return json_encode($result);
    }
}
