<?php

/**
 * This is the model class for table "user_options_values".
 *
 * The followings are the available columns in table 'user_options_values':
 * @property integer $user_id
 * @property integer $option_id
 * @property integer $option_value
 */
class UserOptionValue extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserOptionValue the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
    public function primaryKey() {
        return array('user_id','option_id');
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_options_values';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, option_id, option_value', 'required'),
            array('user_id, option_id, option_value', 'numerical', 'integerOnly'=>true),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
            'option' => array(self::BELONGS_TO, 'UserOption', 'option_id')
        );
    }
    
    /**
     * sets default option values for user
     * @param integer $user_id
     */
    public function setDefaults($user_id) {
        $options = UserOption::model()->findAll();
        $r = TRUE;
        foreach ($options as $option) {
            $val = new UserOptionValue();
            $val->user_id = $user_id;
            $val->option_id = $option->option_id;
            $val->option_value = $option->default_value;
            $r = $r && $val->save();
        }
        return $r;
    }

}