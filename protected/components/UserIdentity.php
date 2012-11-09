<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

    private $_id = 0;
    public $is_openid;


    /**
    * Constructor.
    * @param string $username username
    * @param string $password password
    * @param boolean $is_openid whether user logs in via open id
    */
    public function __construct($username,$password = '', $is_openid = false) {
        $this->username = $username;
        if($password) {
            $this->password = $password;
        }
        elseif($is_openid) {
            $this->is_openid = TRUE;
        }
        else {
            throw new CException('Не передан пароль.');
        }
        
    }

    public function authenticate() {

        $login = strtolower($this->username);

        $user = User::model()->find('user_name=?',array($login));

        if($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        }
        else if(!$this->is_openid && !$user->validatePassword($this->password)) {
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        }
        else {
            // change user, if he has primary alias
            $alias = User::model()->getAlias($user->user_id);
            if($alias != NULL) {
                $user = $alias;
            }
            $this->_id=$user->user_id;
            $this->errorCode=self::ERROR_NONE;

            $token = UserToken::model()->create($user->user_id);
            $this->setState('token', $token->token);
            LoginForm::setTransitionCookie($user->user_id, $token->token);
        }
        return $this->errorCode==self::ERROR_NONE;
    }

    public function getId() {
        return $this->_id;
    }
}