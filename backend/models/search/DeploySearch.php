<?php

namespace backend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Deploy;

/**
 * DeploySearch represents the model behind the search form about `common\models\Deploy`.
 */
class DeploySearch extends Deploy
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'user_id', 'code', 'created_at', 'finished_at'], 'integer'],
            [['output', 'branch', 'type'], 'safe'],
            [['finished', 'canceled',], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Deploy::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => [
                'id' => SORT_DESC
            ]
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
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'code' => $this->code,
            'canceled' => $this->canceled,
            'finished' => $this->finished,
            'created_at' => $this->created_at,
            'finished_at' => $this->finished_at,
        ]);

        $query->andFilterWhere(['like', 'output', $this->output])
            ->andFilterWhere(['like', 'branch', $this->branch])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }
}
