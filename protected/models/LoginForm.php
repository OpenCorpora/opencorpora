<?php

/**
 * Form model for logging in and requesting new password
 */
class LoginForm extends CFormModel
{
	public $login;
	public $password;
	
	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that login and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			array('password', 'required','on'=>'login'),
			array('login', 'required'),
                        array('password', 'authenticate','on'=>'login'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'login' => 'Логин',
			'password'=>'Пароль',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
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
	public function login()
	{
		if($this->_identity === null) {
			$this->_identity = new UserIdentity($this->login,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode === UserIdentity::ERROR_NONE) {
			$duration = 3600*24*30; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
                        $this->_setTransitionVars($this->_identity->id);
			return true;
		}
		else {
			return false;
		}
	}
        
        /**
         * Sets user session variables, used by non-yii application
         * @param integer $user_id user id
         */
        private function _setTransitionVars($user_id) {
            
            $user = User::model()->findByPk($user_id);
            if(!$user) {
                throw new CException('User not found');
            }
            
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['user_name'] = $user->user_name;
            $_SESSION['options'] = $options;
            //$_SESSION['user_permissions'] = $permissions;
            //$_SESSION['token'] = $token;
            $_SESSION['user_level'] = $user->user_level;
            $_SESSION['show_game'] = $user->show_game;
        }
}