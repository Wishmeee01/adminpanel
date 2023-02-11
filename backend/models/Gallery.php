<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "gallery".
 *
 * @property int $id
 * @property int $category_id
 * @property string $image_link
 * @property int $uploaded_at
 * @property int $status
 */
class Gallery extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gallery';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'uploaded_at'], 'required'],
            [['category_id', 'uploaded_at', 'status'], 'integer'],
            [['image_link'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'image_link' => 'Image Link',
            'uploaded_at' => 'Uploaded At',
            'status' => 'Status',
        ];
    }
}
