<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "media".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $friend_id
 * @property string|null $title
 * @property string|null $link
 * @property string $tags
 * @property string|null $description
 * @property int|null $month
 * @property int|null $year
 * @property int|null $created
 * @property int $status
 */
class Media extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'friend_id', 'month', 'year', 'created', 'status'], 'integer'],
            [['friend_id', 'tags'], 'required'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 200],
            [['link', 'tags'], 'string', 'max' => 100],
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
            'friend_id' => 'Friend ID',
            'title' => 'Title',
            'link' => 'Link',
            'tags' => 'Tags',
            'description' => 'Description',
            'month' => 'Month',
            'year' => 'Year',
            'created' => 'Created',
            'status' => 'Status',
        ];
    }
}
