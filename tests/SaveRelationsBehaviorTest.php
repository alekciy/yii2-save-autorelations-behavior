<?php

namespace tests;

use alekciy\Yii2SaveAutoRelationsBehavior\Yii2SaveAutoRelationsBehavior;
use tests\models\Car;
use tests\models\Company;
use tests\models\Driver;
use tests\models\DummyModel;
use tests\models\DummyModelParent;
use tests\models\Link;
use tests\models\Project;
use tests\models\ProjectLink;
use tests\models\ProjectNoTransactions;
use tests\models\Tag;
use tests\models\User;
use tests\models\UserProfile;
use Yii;
use yii\base\Model;
use yii\db\Migration;
use yii\helpers\VarDumper;

class SaveRelationsBehaviorTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->setupDbData();
    }

    protected function tearDown()
    {
        $db = Yii::$app->getDb();
        $db->createCommand()->dropTable('car')->execute();
        $db->createCommand()->dropTable('driver')->execute();
        parent::tearDown();
    }

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
    }

    public function testRelation()
	{
		$car = Car::findOne(1);
		$driver = Driver::findOne(2);
		$car->drivers = [$driver];
		$car->save();

		$this->assertInstanceOf($car->drivers[0], Driver::class);
	}
}
