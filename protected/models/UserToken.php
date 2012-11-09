<?php

/**
 * This is the model class for table "user_tokens".
 *
 * The followings are the available columns in table 'user_tokens':
 * @property integer $user_id
 * @property string $token
 * @property string $timestamp
 */
class UserToken extends CActiveRecord {
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return UserToken the static model class
     */
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }
    
    public function primaryKey() {
        return array('user_id','token');
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user_tokens';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO,'User','user_id')
        );
    }
    
    /**
     * generates new token, saves it & returns
     * @param integer $user_id
     */
    public function create($user_id) {
        $model = new UserToken();
        $model->token = mt_rand();
        $model->user_id = $user_id;
        $model->timestamp = time();
        if($model->save()) {
            return $model;
        }
        else {
            throw new CDbException('Не удалось сохранить токен пользователя.');
        }
    }
    
    /**
     * deletes token
     * @param integer $user_id
     * @param string $token
     * @return boolean
     */
    public function forget($user_id,$token) {
        $model = UserToken::model()->findByAttributes(array('user_id' => $user_id, 'token' => $token));
        if($model) {
            return $model->delete();
        }
        else {
            return TRUE;
        }
    }
    
}