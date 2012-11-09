<?php

/**
 * This is the model class for table "user_permissions".
 *
 * The followings are the available columns in table 'user_permissions':
 * @property integer $user_id
 * @property integer $perm_admin
 * @property integer $perm_adder
 * @property integer $perm_dict
 * @property integer $perm_disamb
 * @property integer $perm_check_tokens
 * @property integer $perm_check_morph
 */
class UserPermissions extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserPermission the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
    public function primaryKey() {
        return 'user_id';
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_permissions';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, perm_admin, perm_adder, perm_dict, perm_disamb, perm_check_tokens, perm_check_morph', 'required'),
            array('user_id, perm_admin, perm_adder, perm_dict, perm_disamb, perm_check_tokens, perm_check_morph', 'numerical', 'integerOnly'=>true),
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
            'user_id' => 'User',
            'perm_admin' => 'Администратор',
            'perm_adder' => 'Может добавлять тексты',
            'perm_dict' => 'Может править словарь',
            'perm_disamb' => 'Perm Disamb',
            'perm_check_tokens' => 'Perm Check Tokens',
            'perm_check_morph' => 'Perm Check Morph',
        );
    }
    
    /**
     * sets default permissions for user
     * @param integer $user_id
     */
    public function setDefaults($user_id) {
        $perm = new UserPermissions();
        $perm->user_id = $user_id;
        $perm->perm_admin = 0;
        $perm->perm_adder = 0;
        $perm->perm_dict = 0;
        $perm->perm_disamb = 0;
        $perm->perm_check_tokens = 0;
        $perm->perm_check_morph = 0;
        return $perm->save();
    }

}