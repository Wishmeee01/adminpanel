<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_details".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $date_of_birth
 * @property string|null $anniversary_date
 * @property string|null $profile_image
 * @property int|null $created
 * @property int|null $updated
 * @property int $status
 */
class UserDetails extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'created', 'updated', 'status'], 'integer'],
            [['date_of_birth', 'anniversary_date'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['profile_image'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'date_of_birth' => 'Date Of Birth',
            'anniversary_date' => 'Anniversary Date',
            'profile_image' => 'Profile Image',
            'created' => 'Created',
            'updated' => 'Updated',
            'status' => 'Status',
        ];
    }
    
    public function getname($id) {
       
       $query = UserDetails::find()->select('name')->where([
                   'user_id' => $id,
               ])->one();
       
       if (isset($query->name)) { 
           return $query->name; 
       } else { 
           return NULL; 
       } 
        
   } 
}
