<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

    private $_id = 0;

    public function authenticate() {

        $login = strtolower($this->username);

        $user = User::model()->find('LOWER(user_name)=?',array($login));

        if($user === null) {
                $this->errorCode = self::ERROR_USERNAME_INVALID;
        }
        else if(!$user->validatePassword($this->password)) {
                $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        else {
                $this->_id=$user->user_id;
                $this->errorCode=self::ERROR_NONE;
        }
        return $this->errorCode==self::ERROR_NONE;
    }

    public function getId() {
        return $this->_id;
    }
}