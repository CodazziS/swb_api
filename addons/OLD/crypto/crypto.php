<?php
class Crypto {
    public static function hash($data) {
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
    }

    public static function encrypt($data) {
        if ($data != null && $data != '') {
            $td = mcrypt_module_open(MCRYPT_TRIPLEDES , '', 'nofb', ''); // charge le chiffrement
            $ks = mcrypt_enc_get_key_size($td); //détermine la taille de la clé
            $key = substr(md5(CRYPT_TOKEN), 0, $ks); //creation de la cle
            mcrypt_generic_init($td, $key, CRYPT_TOKEN2); // initialisation
            $encrypted = mcrypt_generic($td, $data); //Chiffrement
            mcrypt_generic_deinit($td); // Liberation du chiffrement
            mcrypt_module_close($td);
            return base64_encode($encrypted);
        }
        return '';
    }

    public static function decrypt($data) {
        if ($data != null && $data != '') {
            $data = base64_decode($data);
            $td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'nofb', ''); // charge le chiffrement
            $ks = mcrypt_enc_get_key_size($td); //détermine la taille de la clé
            $key = substr(md5(CRYPT_TOKEN), 0, $ks); //creation de la cle
            mcrypt_generic_init($td, $key, CRYPT_TOKEN2); // Initialisation pour le déchiffrement
            $decrypted = mdecrypt_generic($td, $data); // décryptage
            mcrypt_generic_deinit($td);//liberation
            mcrypt_module_close($td);
            return $decrypted;
        }
    }
}
