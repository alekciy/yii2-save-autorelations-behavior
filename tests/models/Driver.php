<?php

namespace tests\models;

use Yii;
use yii\db\ActiveRecord;

class Driver extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'driver';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['name'], 'string', 'max' => 255],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'ФИО',
		];
	}
}
