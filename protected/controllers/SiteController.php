<?php

class SiteController extends Controller {
    /**
     * Declares class-based actions.
     */
    public function actions() {
        return array(
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page'=>array(
                'class'=>'CViewAction',
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        if (!Yii::app()->user->isAdmin) {
            if (!Yii::app()->user->isGuest){
                //$smarty->assign('available', get_available_tasks($_SESSION['user_id'], true, 5, true));
            }
            //$smarty->assign('answer_count', count_all_answers());
        }
        $this->render('index',array(
            'answer_count' => 100500,
            'available' => array()
        ));
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if($error=Yii::app()->errorHandler->error) {
            if(Yii::app()->request->isAjaxRequest) 
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {

        $model=new LoginForm('login');

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['LoginForm'])) {
            $model->attributes=$_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login()) {
                LoginForm::setTransitionVars();
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }
        // display the login form
        $this->render('login',array('model'=>$model));
    }
    
    /**
     * 
     */
    public function actionLoginzaAuth() {

        $token = $_POST['token'];
        
        // get data from loginza
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://loginza.ru/api/authinfo?token=$token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = json_decode(curl_exec($ch), true);
        
        if (isset($data['error_type'])) {
            throw new CException('Auth error: ' . $data['error_message']);
        }
        $id = '';
        if (strpos($data['provider'], 'google') !== false) {
            if (!trim($data['uid']))
                throw new CException('Ошибка авторизации через Google. Пожалуйста, попробуйте ещё раз.');
            $id = 'google:'.$data['uid'];
        }
        else {
            $id =  $data['identity'];
            if (!trim($id))
                throw new CException('Ошибка авторизации. Пожалуйста, попробуйте ещё раз.');
        }
        
        $uid = new UserIdentity($id,'',TRUE);
        // if user exists, we log him in
        if($uid->authenticate()) {
            $duration = 3600*24*30; // 30 days
            Yii::app()->user->login($uid,$duration);
            LoginForm::setTransitionVars();
            $this->redirect(Yii::app()->user->returnUrl);
        }
        // if not, we render form 
        else {
            $model = new User('register_openid');
            $model->user_name = $id;
            $this->render('//user/register_openid',array(
                'model' => $model,
            ));
        }
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        Yii::app()->user->logout();
        LoginForm::unsetTransitionVars();
        $this->redirect(Yii::app()->homeUrl);
    }

}
