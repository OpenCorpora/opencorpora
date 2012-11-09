<?php

/**
 * This is the model class for table "user_options".
 *
 * The followings are the available columns in table 'user_options':
 * @property integer $option_id
 * @property string $option_name
 * @property string $option_values
 * @property integer $default_value
 * @property integer $order_by
 */
class UserOption extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserOption the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_options';
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('option_id, option_name, option_values, default_value, order_by', 'required'),
            array('option_id, default_value, order_by', 'numerical', 'integerOnly'=>true),
            array('option_name', 'length', 'max'=>128),
            array('option_values', 'length', 'max'=>64),
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

}