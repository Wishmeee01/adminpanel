<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "gallery_category".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string|null $name
 * @property int $status
 */
class GalleryCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gallery_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'name' => 'Name',
            'status' => 'Status',
        ];
    }
    
    public function getname($id) {
       
       $query = GalleryCategory::find()->select('name')->where([
                   'id' => $id,
               ])->one();
       
       if (isset($query->name)) { 
           return $query->name; 
       } else { 
           return NULL; 
       } 
        
   }
}
