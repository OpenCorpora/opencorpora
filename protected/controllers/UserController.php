<?php
class UserController extends Controller {
    
    public function actionAdmin() {
        $this->render('admin');
    }

    public function actionIndex() {
        $this->render('index');
    }

    public function actionRegister() {
        
        $this->pageTitle = "Регистрация";
        
        $model = new User('register');
        
        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='register-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        
        if(isset($_POST['User'])) {
            $model->attributes = $_POST['User'];
            if($model->validate()) {
                $passwd = $model->user_passwd;
                $model->user_passwd = $model->hashPassword($passwd);
                if(!$model->create()) {
                    throw new CDbException('Не удалось создать пользователя. Попробуйте, пожалуйста, в другой раз.');
                }
                else {
                    // log in new user
                    $uid = new UserIdentity($model->user_name,$passwd);
                    if(!$uid->authenticate()) {
                        throw new CException('Unknown authentication error.');
                    }
                    $duration = 3600*24*30; // 30 days
                    Yii::app()->user->login($uid,$duration);
                    LoginForm::setTransitionVars();
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
        }
        
        $this->render('register', array(
            'model' => $model,
        ));
    }
    
    public function actionRegisterOpenid() {
        
        if(!isset($_POST['User'])) {
            throw new CException('Не переданы данные.');
        }
        $model = new User('register_openid');
        
        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='register-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        
        $model->attributes = $_POST['User'];
        if($model->validate() && $model->create()) {
            $uid = new UserIdentity($model->user_name,'',TRUE);
            if(!$uid->authenticate()) {
                throw new CException('Unknown authentication error.');
            }
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($uid,$duration);
            LoginForm::setTransitionVars();
            $this->redirect(Yii::app()->user->returnUrl);
            
        }
        $this->render('register_openid',array(
            'model' => $model
        ));
    }
    // Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}