<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "offers".
 *
 * @property int $id
 * @property int|null $subscription_id
 * @property int|null $country_id
 * @property string|null $offer_name
 * @property float|null $offer_price
 * @property string|null $offer_start_date
 * @property string|null $offer_end_date
 * @property string|null $createdAt
 * @property string|null $updatedAt
 * @property int $status
 */
class Offers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'offers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subscription_id', 'country_id', 'status'], 'integer'],
            [['offer_price'], 'number'],
            [['offer_start_date', 'offer_end_date', 'createdAt', 'updatedAt'], 'safe'],
            [['offer_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'subscription_id' => 'Subscription ID',
            'country_id' => 'Country ID',
            'offer_name' => 'Offer Name',
            'offer_price' => 'Offer Price',
            'offer_start_date' => 'Offer Start Date',
            'offer_end_date' => 'Offer End Date',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'status' => 'Status',
        ];
    }
}
