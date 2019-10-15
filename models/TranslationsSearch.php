<?php

namespace wdmg\translations\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\translations\models\Translations;

/**
 * TranslationsSearch represents the model behind the search form of `wdmg\pages\translations\Translations`.
 */
class TranslationsSearch extends Translations
{
    public $languages;
    public $category;
    public $alias;
    public $sources;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['languages', 'category', 'alias', 'sources', 'language', 'translation', 'status'], 'safe'],
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
        $query = Translations::find();

        // Join the query with our related models
        $query->joinWith(['languages', 'sources']);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Configure sorting for related `languages` attribute
        $dataProvider->sort->attributes['languages'] = [
            'asc' => [
                '{{%trans_langs}}.name' => SORT_ASC
            ],
            'desc' => [
                '{{%trans_langs}}.name' => SORT_DESC
            ]
        ];

        // Configure sorting for related `category` attribute
        $dataProvider->sort->attributes['category'] = [
            'asc' => [
                '{{%trans_sources}}.category' => SORT_ASC
            ],
            'desc' => [
                '{{%trans_sources}}.category' => SORT_DESC
            ]
        ];

        // Configure sorting for related `alias` attribute
        $dataProvider->sort->attributes['alias'] = [
            'asc' => [
                '{{%trans_sources}}.alias' => SORT_ASC
            ],
            'desc' => [
                '{{%trans_sources}}.alias' => SORT_DESC
            ]
        ];
        // Configure sorting for related `sources` attribute
        $dataProvider->sort->attributes['sources'] = [
            'asc' => [
                '{{%trans_sources}}.message' => SORT_ASC
            ],
            'desc' => [
                '{{%trans_sources}}.message' => SORT_DESC
            ]
        ];

        // No search? Then return data Provider
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        // Here we search the attributes of our relations using our previously config
        $query->andFilterWhere(['like', '{{%trans_langs}}.name', $this->languages])
            ->andFilterWhere(['like', '{{%trans_sources}}.category', $this->category])
            ->andFilterWhere(['like', '{{%trans_sources}}.alias', $this->alias])
            ->andFilterWhere(['like', '{{%trans_sources}}.message', $this->sources]);

        $query->andFilterWhere(['like', 'language', $this->language])
            ->andFilterWhere(['like', 'translation', $this->translation]);

        if($this->status !== "*")
            $query->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }

}
