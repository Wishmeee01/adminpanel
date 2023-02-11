<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Subscription;

/**
 * SubscriptionSearch represents the model behind the search form of `backend\models\Subscription`.
 */
class SubscriptionSearch extends Subscription
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'validity_in_days', 'cycle', 'offer_status', 'status'], 'integer'],
            [['plan_name', 'currency', 'icon', 'description', 'feature1', 'feature2'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Subscription::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'validity_in_days' => $this->validity_in_days,
            'amount' => $this->amount,
            'cycle' => $this->cycle,
            'offer_status' => $this->offer_status,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'plan_name', $this->plan_name])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'icon', $this->icon])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'feature1', $this->feature1])
            ->andFilterWhere(['like', 'feature2', $this->feature2]);

        return $dataProvider;
    }
}
