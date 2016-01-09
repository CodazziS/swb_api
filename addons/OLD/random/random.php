<?php
class Random {
    public static function gen_random_string($length=16) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //length:36
        $final_rand = '';
        for($i=0; $i<$length; $i++) {
            $final_rand .= $chars[ rand(0,strlen($chars)-1)];
        }
        return $final_rand;
    }
}
