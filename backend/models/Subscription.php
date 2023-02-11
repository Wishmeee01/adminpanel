<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property string|null $plan_name
 * @property int|null $validity_in_days
 * @property float|null $amount
 * @property string|null $currency
 * @property string|null $icon
 * @property string|null $description
 * @property string|null $feature1
 * @property string|null $feature2
 * @property int|null $cycle
 * @property int $offer_status
 * @property int $status
 */
class Subscription extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['validity_in_days', 'cycle', 'offer_status', 'status'], 'integer'],
            [['amount'], 'number'],
            [['description', 'feature1', 'feature2'], 'string'],
            [['plan_name'], 'string', 'max' => 200],
            [['currency'], 'string', 'max' => 50],
            [['icon'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_name' => 'Plan Name',
            'validity_in_days' => 'Validity In Days',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'icon' => 'Icon',
            'description' => 'Description',
            'feature1' => 'Feature1',
            'feature2' => 'Feature2',
            'cycle' => 'Cycle',
            'offer_status' => 'Offer Status',
            'status' => 'Status',
        ];
    }
}
