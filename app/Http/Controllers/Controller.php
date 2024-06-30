<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //

    protected function get_custom_hex() {
        $current_timestamp = time();
        $hex = dechex($current_timestamp);
        $allowed_chars = array_merge(range(0, 9), range('a', 'z'));
        $hex = strtolower($hex);
        $filtered_hex = '';
        for ($i = 0; $i < strlen($hex); $i++) {
            if (in_array($hex[$i], $allowed_chars)) {
                $filtered_hex .= $hex[$i];
            }
        }
        $uppercase_hex = strtoupper($filtered_hex);
        return $uppercase_hex;
    }
    
    
}
