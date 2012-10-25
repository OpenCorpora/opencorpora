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
 */
class User extends CActiveRecord {
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
            array('user_name, user_passwd, user_email, user_reg, user_shown_name, user_team, user_level, user_shown_level, user_rating10, show_game', 'required'),
            array('user_team, user_level, user_shown_level, show_game', 'numerical', 'integerOnly'=>true),
            array('user_name, user_shown_name', 'length', 'max'=>120),
            array('user_passwd', 'length', 'max'=>32),
            array('user_email', 'length', 'max'=>100),
            array('user_reg, user_rating10', 'length', 'max'=>10),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('user_id, user_name, user_passwd, user_email, user_reg, user_shown_name, user_team, user_level, user_shown_level, user_rating10, show_game', 'safe', 'on'=>'search'),
    );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
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
        );
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
}