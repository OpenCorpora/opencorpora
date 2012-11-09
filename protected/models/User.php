<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_passwd
 * @property string $user_email
 * @property string $user_reg
 * @property string $user_shown_name
 * @property integer $user_team
 * @property integer $user_level
 * @property integer $user_shown_level
 * @property string $user_rating10
 * @property integer $show_game
 * 
 * @property integer $id user_id alias
 * @property string $name user_shown_name or user_name
 * 
 */
class User extends CActiveRecord {

    public $id;
    public $name;


    public $is_license_accepted;
    public $is_subscribe_checked;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'users';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_name', 'required', 'on' => 'register,register_openid'),
            array('user_passwd', 'required', 'on' => 'register'),
            array('user_name, user_email', 'unique', 'on' => 'register,register_openid'),
            array('is_license_accepted','required','requiredValue'=>1, 'on' => 'register,register_openid','message'=>'Необходимо согласиться с лицензией.'),
            array('user_email','filter','filter'=>'strtolower'),
            array('user_email', 'email'),
            array('user_name, user_shown_name', 'length', 'max'=>120),
            array('user_name,user_passwd', 'match', 'pattern'=>'/^[a-zA-Z0-9_-]+$/', 'message' => 'Допустимые символы: латинские буквы, цифры, дефис и подчеркивание.','on'=>'register'),
            array('user_passwd', 'length', 'max'=>32),
            array('user_email', 'length', 'max'=>100),
            
    );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'options' => array(self::HAS_MANY, 'UserOptionValue', 'user_id'),
            'permissions' => array(self::HAS_ONE, 'UserPermissions', 'user_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'user_id' => 'id',
            'user_name' => 'Логин',
            'user_passwd' => 'Пароль',
            'user_email' => 'Email',
            'user_reg' => 'User Reg',
            'user_shown_name' => 'User Shown Name',
            'user_team' => 'User Team',
            'user_level' => 'User Level',
            'user_shown_level' => 'User Shown Level',
            'user_rating10' => 'User Rating10',
            'show_game' => 'Show Game',
            
            'is_license_accepted' => ' Я согласен на неотзывную публикацию всех вносимых мной изменений в соответствии с лицензией ' . CHtml::link('Creative Commons Attribution/Share-Alike 3.0','http://creativecommons.org/licenses/by-sa/3.0/deed.ru'),
            'is_subscribe_checked' => 'Подписаться на рассылку новостей проекта',
        );
    }
    
    /**
     * treat model after it's found in DB
     */
    public function afterFind() {
        $this->id = $this->user_id;
        $this->name = $this->user_shown_name ? $this->user_shown_name : $this->user_name;
        parent::afterFind();
    }

    /**
     * checks password hash
     * @param string $password
     */
    public function validatePassword($password) {
        return $this->user_passwd === $this->hashPassword($password);
    }
    
    /**
     * generates password hash
     * @param string $password
     */
    public function hashPassword($password) {
        return md5(md5($password).substr($this->user_name, 0, 2));
    }
    
    /**
     * get user's options as an array indexed by option_id
     * @return array
     */
    public function getOptionsArray() {
        
        $res = array();
        foreach ($this->options as $ar) {
            $res[$ar->option_id] = $ar->option_value;
        }
        return $res;
    }
    
    /**
     * get user's permissions as an array indexed by permission type
     * @return array
     */
    public function getPermissionsArray() {
        
        $res = array();
        foreach ($this->permissions as $k => $v) {
            $res[$k] = $v;
        }
        return $res;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('user_id',$this->user_id);
        $criteria->compare('user_name',$this->user_name,true);
        $criteria->compare('user_passwd',$this->user_passwd,true);
        $criteria->compare('user_email',$this->user_email,true);
        $criteria->compare('user_reg',$this->user_reg,true);
        $criteria->compare('user_shown_name',$this->user_shown_name,true);
        $criteria->compare('user_team',$this->user_team);
        $criteria->compare('user_level',$this->user_level);
        $criteria->compare('user_shown_level',$this->user_shown_level);
        $criteria->compare('user_rating10',$this->user_rating10,true);
        $criteria->compare('show_game',$this->show_game);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    /**
     * creates a user: saves AR without validation & sets default permissions&options
     * @return type
     */
    public function create() {
        $r = TRUE;
        $trans = Yii::app()->db->beginTransaction();
        $this->user_reg = time();
        $this->user_shown_name = $this->user_name;
        $r = $r && $this->save(FALSE);
        $r = $r && UserPermissions::model()->setDefaults($this->user_id);
        $r = $r && UserOptionValue::model()->setDefaults($this->user_id);
        if($r) {
            $trans->commit();
        }
        else {
            $trans->rollback();
        }
        return $r;
    }
    
    public function getAlias($user_id) {
        $cmd = Yii::app()->db->createCommand();
        $cmd
            ->select('primary_uid')
            ->from('user_aliases')
            ->where('alias_uid=:id', array(':id'=>$user_id));
        $id = $cmd->queryScalar();
        if($id) {
            return User::model()->findByPk($id);
        }
        else {
            return NULL;
        }
    }
}