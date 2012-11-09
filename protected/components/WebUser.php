<?php
 
// extends default user component to get extra user data

class WebUser extends CWebUser {

	// Store model to not repeat query.
	private $_model;
	
	/**
	 * returns user's AR
	 */
	public function getModel(){
		return $this->loadUser($this->id);
	}
	
	/**
	 */
	function getRole() {
		$user = $this->loadUser($this->id);
		if($user!==null) {
			return User::getTypeName($user->type);
		}
		
	}

	// Load user model.
	protected function loadUser($id=null) {
		if($this->_model===null) {
			if($id!==null) {
				$this->_model=User::model()->findByPk($id);
			}
		}
		return $this->_model;
	}
        
        /**
         * hook before user login. here we check user token, if he logs in from cookie
         * @param integer $id
         * @param array $states
         * @param boolean $fromCookie
         * @return boolean
         */
        public function beforeLogin($id, $states, $fromCookie) {
            if($fromCookie) {
                $token = $states['token'];
                if(!$token) {
                    return FALSE;
                }
                $tk = UserToken::model()->findByAttributes(array('user_id' => $id, 'token' => $token));
                if($tk) {
                    return TRUE;
                }
                else {
                    return FALSE;
                }
            }
            
            return TRUE;
        }
        
        /**
         * hook before user logout, here we delete user token
         * @return boolean
         */
        public function beforeLogout() {
            return UserToken::model()->forget(Yii::app()->user->id, Yii::app()->user->token) && LoginForm::unsetTransitionCookie();;
        }

        public function getIsAdmin() {
            return FALSE;
        }
        
        public function getIsAdder() {
            return FALSE;
        }
        
        public function getIsDict() {
            return FALSE;
        }
        
        public function getIsCheckMorph() {
            return FALSE;
        }
}