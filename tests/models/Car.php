<?php

namespace tests\models;

use alekciy\Yii2SaveAutoRelations\Yii2SaveAutoRelationsTrait;
use alekciy\Yii2SaveAutoRelations\Yii2SaveAutoRelationsBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "car".
 *
 * @property int $id
 * @property Driver[] $drivers
 * @property string|null $name Название
 */
class Car extends ActiveRecord
{
	use Yii2SaveAutoRelationsTrait;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'car';
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
			'name' => 'Название',
		];
	}

	public function behaviors()
	{
		return [
			[
				'class' => Yii2SaveAutoRelationsBehavior::class,
				'manyRelationList' => [
					'drivers' => Driver::class
				]
			]
		];
	}
}
