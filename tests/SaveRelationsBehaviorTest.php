<?php

namespace tests;

use tests\models\Car;
use tests\models\Driver;
use Yii;
use yii\db\Exception;
use yii\db\Migration;

class SaveRelationsBehaviorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @inheritDoc
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setupDbData();
	}

	/**
	 * @inheritDoc
	 */
	protected function tearDown()
	{
		$db = Yii::$app->getDb();
		$db->createCommand()->dropTable('link__car__to__driver')->execute();
		$db->createCommand()->dropTable('car')->execute();
		$db->createCommand()->dropTable('driver')->execute();
		parent::tearDown();
	}

	/**
	 * @throws Exception
	 */
	protected function setupDbData()
	{
		/** @var \yii\db\Connection $db */
		$db = Yii::$app->getDb();
		$migration = new Migration();

		// Car
		$db->createCommand()->createTable('car', [
			'id'   => $migration->primaryKey(),
			'name' => $migration->string(256)->notNull(),
		])->execute();

		// Driver
		$db->createCommand()->createTable('driver', [
			'id'   => $migration->primaryKey(),
			'name' => $migration->string(256)->notNull(),
		])->execute();

		/**
		 * Добавляем модели
		 */
		$db->createCommand()->batchInsert('car', ['id', 'name'], [
			[1, 'KIA'],
			[2, 'ВАЗ'],
			[3, 'BMW'],
		])->execute();

		$db->createCommand()->batchInsert('driver', ['id', 'name'], [
			[1, 'Иванов'],
			[2, 'Петров'],
			[3, 'Сидоров'],
		])->execute();

		$car = Car::findOne(1);
		$driver = Driver::findOne(2);
		$car->drivers = [$driver];
		$car->save();
	}

	/**
	 * @test
	 */
	public function testRelation()
	{
		$car = Car::findOne(1);
		$this->assertInstanceOf( Driver::class, $car->drivers[0]);
	}

	/**
	 * @test
	 */
	public function testChangeRelation()
	{
		$car = Car::findOne(1);
		$driver = Driver::findOne(2);
		$car->drivers = [$driver];
		$car->save();

		$this->assertEquals('Петров', $car->drivers[0]->name);
		$this->assertCount(1, $car->drivers);
	}

	/**
	 * @test
	 */
	public function testLoadRelation()
	{
		$car = Car::findOne(1);
		$car->loadRelations([
			'drivers' => [
				['name' => 'Jack'],
				['name' => 'Joy']
			],
		]);
		$car->save();

		$this->assertCount(2, $car->drivers);
		$this->assertEquals('Joy', $car->drivers[1]->name);
	}
}
