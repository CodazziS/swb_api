<?php

class Authentication {
    public function is_auth($username, $token) {
        if (DB_PDO_LIB == "phpactiverecord") {
            return $this->log_phpactiverecord($username, $token);
        }
        echo 'Error : Authentication addon : No ORM found';
        return false;
    }

    private function log_phpactiverecord ($username, $token) {
        $opt = array('conditions' => array('username ILIKE ?', $username));
        $user = User::find('first', $opt);
        if (isset($user)) {
            $opt = array('conditions' =>
                array('user_id = ? AND token = ?', $user->id, $token));
            $token_user = Token::find('first', $opt);
            if (isset($token_user)) {
                return true;
            }
        }
        return false;
    }
}
