<?php

/*
*
*/

class Crypto extends Framaddons {
    public $name = 'Crypto';
    public $author = 'Stéphane Codazzi';
    public $version = '1.0';
    public $website = 'https://codazzi.fr';
    public $description = "Hash, encrypt and decrypt methods";
    public $licence = 'MIT';
    
    function __construct () {
    	
    }
	
	/*function hash($data) {
        if ($data != null && $data != '') {
            $td = mcrypt_module_open(MCRYPT_TRIPLEDES , '', 'nofb', ''); // charge le chiffrement
            $ks = mcrypt_enc_get_key_size($td); //détermine la taille de la clé
            $key = substr(md5(CRYPT_TOKEN), 0, $ks); //creation de la cle
            mcrypt_generic_init($td, $key, CRYPT_TOKEN2); // initialisation
            $encrypted = mcrypt_generic($td, $data); //Chiffrement
            mcrypt_generic_deinit($td); // Liberation du chiffrement
            mcrypt_module_close($td);
            return sha1(base64_encode($encrypted));
        }
    }*/
    
    public static function hash1($str) {
    	return hash ("sha512", $str, false);
    }
    
    public static function hash2($str) {
    	return hash ("sha256", $str, false);
    }
    
    public static function encrypt($data, $key) {
        if ($data != null && $data != '') {
            $td = mcrypt_module_open(MCRYPT_TRIPLEDES , '', 'nofb', ''); // charge le chiffrement
            $ks = mcrypt_enc_get_key_size($td); //détermine la taille de la clé
            $key = substr(md5($key), 0, $ks); //creation de la cle
            $key2 = substr(md5($key), 0, 8); //creation de la cle 2
            mcrypt_generic_init($td, $key, $key2); // initialisation
            $encrypted = mcrypt_generic($td, $data); //Chiffrement
            mcrypt_generic_deinit($td); // Liberation du chiffrement
            mcrypt_module_close($td);
            return base64_encode($encrypted);
        }
        return '';
    }

    public static function decrypt($data, $key) {
        if ($data != null && $data != '') {
            $data = base64_decode($data);
            $td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'nofb', ''); // charge le chiffrement
            $ks = mcrypt_enc_get_key_size($td); //détermine la taille de la clé
            $key = substr(md5($key), 0, $ks); //creation de la cle
            $key2 = substr(md5($key), 0, 8); //creation de la cle 2
            mcrypt_generic_init($td, $key, $key2); // Initialisation pour le déchiffrement
            $decrypted = mdecrypt_generic($td, $data); // décryptage
            mcrypt_generic_deinit($td);//liberation
            mcrypt_module_close($td);
            return $decrypted;
        }
    }
    
    public static function formatPhoneNumber($str) {
    	/* TODO : Internationnalization for the phone Number */
    	$number = preg_replace('/\s+/', '', $str);
    	return substr($number, -9);
    }
    /*
    public static function crypt ($data, $key) {
    	$key1 = substr($key, 0, 24);
    	$key2 = substr($key, 0, 8);
		return mcrypt_encrypt(MCRYPT_3DES, $key1, $data, MCRYPT_MODE_NOFB, $key2;
    }
    
    public static function decrypt ($data) {
    	$key1 = substr($key, 0, 24);
    	$key2 = substr($key, 0, 8);
    	return mcrypt_decrypt(MCRYPT_3DES, $key1, $data, MCRYPT_MODE_NOFB, $key2;
    }*/
    
    public static function random($length=16) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; //length:36
        $final_rand = '';
        for($i=0; $i<$length; $i++) {
            $final_rand .= $chars[ rand(0,strlen($chars)-1)];
        }
        return $final_rand;
    }
}
?>