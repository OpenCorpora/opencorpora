<?php

/**
 * Form model for logging in and requesting new password
 */
class LoginForm extends CFormModel {
    public $login;
    public $password;

    private $_identity;

    /**
     * Declares the validation rules.
     * The rules state that login and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            array('password', 'required','on'=>'login'),
            array('login', 'required'),
            array('password', 'authenticate','on'=>'login'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'login' => 'Логин',
            'password'=>'Пароль',
        );
    }

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate($attribute,$params) {
        if(!$this->hasErrors()) {
            $this->_identity = new UserIdentity($this->login,$this->password);
            if(!$this->_identity->authenticate()) {
                $this->addError('password','Неправильный логин или пароль.');
            }
        }
    }

    /**
     * Logs in the user using the given username and password in the model.
     * @return boolean whether login is successful
     */
    public function login() {
        if($this->_identity === null) {
            $this->_identity = new UserIdentity($this->login,$this->password);
            $this->_identity->authenticate();
        }
        if($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        }
        else {
            return false;
        }
    }
    
        
    /**
    * Sets user session variables, used by non-yii application
    */
    public static function setTransitionVars() {

       $user = User::model()->findByPk(Yii::app()->user->id);
       if(!$user) {
           throw new CException('User not found');
       }

       $_SESSION['user_id'] = $user->user_id;
       $_SESSION['user_name'] = $user->user_shown_name;
       $_SESSION['options'] = $user->optionsArray;
       $_SESSION['user_permissions'] = $user->permissionsArray;
       $_SESSION['user_level'] = $user->user_level;
       $_SESSION['show_game'] = $user->show_game;
    }

    /**
    * unsets user session variables, used by non-yii application
    */
    public static function unsetTransitionVars() {

       foreach (array('user_id','user_name','options','user_permissions','token','user_level','show_game') as $k) {
           if(isset($_SESSION[$k])) {
               unset($_SESSION[$k]);
           }
       }
    }
    
    /**
    * Sets user token to cookie, for autologin in non-yii application
    */
    public static function setTransitionCookie($user_id,$token) {
        return setcookie('auth', $user_id.'@'.$token, time()+60*60*24*7, '/');
    }
    
    /**
    * unsets user token in cookie
    */
    public static function unsetTransitionCookie() {
        return setcookie('auth', '', time()-1);
    }

}