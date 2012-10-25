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