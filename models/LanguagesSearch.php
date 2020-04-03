<?php

namespace wdmg\translations\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\translations\models\Languages;

/**
 * LanguagesSearch represents the model behind the search form of `wdmg\pages\translations\Languages`.
 */
class LanguagesSearch extends Languages
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['url', 'locale', 'name', 'is_default', 'is_system', 'is_frontend', 'status'], 'safe'],
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
        $query = Languages::find();

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
        ]);

        $query->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'locale', $this->locale])
            ->andFilterWhere(['like', 'name', $this->name]);

        if($this->is_default !== "*")
            $query->andFilterWhere(['like', 'is_default', $this->is_default]);

        if($this->is_system !== "*")
            $query->andFilterWhere(['like', 'is_system', $this->is_system]);

        if($this->is_frontend !== "*")
            $query->andFilterWhere(['like', 'is_frontend', $this->is_frontend]);

        if($this->status !== "*")
            $query->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }

}
