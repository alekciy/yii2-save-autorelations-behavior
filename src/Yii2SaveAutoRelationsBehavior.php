<?php

namespace alekciy\Yii2SaveAutoRelations;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\base\UnknownPropertyException;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\Exception;
use yii\helpers\Inflector;

class Yii2SaveAutoRelationsBehavior extends SaveRelationsBehavior
{
	/** @var array Список связей 1-n */
	public $manyRelationList;

	/** @var array Список связей 1-1 */
	public $oneRelationList;

	/** @var ActiveQuery[] */
	public $autoRelationList;

	/**
	 * @inheritDoc
	 * @throws UnknownPropertyException
	 * @throws Exception
	 */
	public function init()
	{
		if (is_array($this->manyRelationList)) {
			foreach ($this->manyRelationList as $relationName => $slaveClass) {
				$this->relations[] = $relationName;
			}
		}
		if (is_array($this->oneRelationList)) {
			foreach ($this->oneRelationList as $relationName => $slaveClass) {
				$this->relations[] = $relationName;
			}
		}
		parent::init();
	}

	/**
	 * @inheritDoc
	 */
	public function attach($owner)
	{
		parent::attach($owner);
		if (empty($this->autoRelationList)) {
			$this->attachSingleRelation();
			$this->attachMultipleRelation();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function canGetProperty($name, $checkVars = true)
	{
		if (isset($this->manyRelationList[$name])
			|| isset($this->oneRelationList[$name])
		) {
			return true;
		}
		return parent::canGetProperty($name, $checkVars);
	}

	/**
	 * @inheritDoc
	 */
	public function canSetProperty($name, $checkVars = true)
	{
		if (isset($this->manyRelationList[$name])
			|| isset($this->oneRelationList[$name])
		) {
			return true;
		}
		return parent::canSetProperty($name, $checkVars);
	}

	/**
	 * @inheritDoc
	 */
	public function __get($name)
	{
		if (isset($this->manyRelationList[$name])
			|| isset($this->oneRelationList[$name])
		) {
			return $this->autoRelationList[$name];
		}
		return parent::__get($name);
	}

	/**
	 * Проверит существование таблицы связей и создаст её при необходимости. Вернет имя таблицы связей и имена колонок
	 * в ней для основной таблицы модели и связанной модели.
	 *
	 * @param string $masterClass
	 * @param string $slaveClass
	 * @return array
	 * @throws Exception
	 */
	protected function createTableIfNotExists(string $masterClass, string $slaveClass): array
	{
		/** @var Connection $db */
		$db = $this->owner::getDb();
		$masterId = Inflector::camel2id($masterClass::tableName(), '_');
		$slaveId = Inflector::camel2id($slaveClass::tableName(), '_');
		$junctionTableName = sprintf('link__%s__to__%s',
			$masterId, $slaveId
		);

		$masterPk = current($masterClass::getTableSchema()->primaryKey);
		$masterColumnName = $masterId . '_' . $masterPk;
		$slavePk = current($slaveClass::getTableSchema()->primaryKey);
		$slaveColumnName = $slaveId . '_' . $slavePk;

		if ($db->schema->getTableSchema($junctionTableName) === null) {
			$db->createCommand()
				->createTable($junctionTableName, [
					$masterColumnName => $masterClass::getTableSchema()->getColumn($masterPk)->dbType,
					$slaveColumnName => $slaveClass::getTableSchema()->getColumn($slavePk)->dbType,
					"PRIMARY KEY ({$masterColumnName}, {$slaveColumnName})",
					sprintf('CONSTRAINT master_fk FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE CASCADE',
						$masterColumnName, $masterClass::tableName(), $masterPk
					),
					sprintf('CONSTRAINT slave_fk FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE CASCADE',
						$slaveColumnName, $slaveClass::tableName(), $slavePk
					),
				])->execute();
		}

		return [$junctionTableName, $masterColumnName, $slaveColumnName];
	}

	/**
	 * Автоматически добавляем связи 1-1.
	 *
	 * @throws Exception
	 */
	protected function attachSingleRelation()
	{
		if (is_array($this->oneRelationList)) {
			$masterClass = get_class($this->owner);
			foreach ($this->oneRelationList as $relationName => $slaveClass) {
				// Связывающая таблица должна существовать
				list($junctionTableName, $masterColumnName, $slaveColumnName) = $this->createTableIfNotExists($masterClass, $slaveClass);
				$this->autoRelationList[$relationName] = $this->owner
					->hasOne($slaveClass, [current($slaveClass::getTableSchema()->primaryKey) => $slaveColumnName])
					->viaTable($junctionTableName, [$masterColumnName => current($masterClass::getTableSchema()->primaryKey)]);
				$this->relations[] = $relationName;
			}
		}
	}

	/**
	 * Автоматически добавляем связи 1-n.
	 *
	 * @throws Exception
	 */
	protected function attachMultipleRelation()
	{
		if (is_array($this->manyRelationList)) {
			$masterClass = get_class($this->owner);
			foreach ($this->manyRelationList as $relationName => $slaveClass) {
				// Связывающая таблица должна существовать
				list($junctionTableName, $masterColumnName, $slaveColumnName) = $this->createTableIfNotExists($masterClass, $slaveClass);
				$this->autoRelationList[$relationName] = $this->owner
					->hasMany($slaveClass, [current($slaveClass::getTableSchema()->primaryKey) => $slaveColumnName])
					->viaTable($junctionTableName, [$masterColumnName => current($masterClass::getTableSchema()->primaryKey)]);
				$this->relations[] = $relationName;
			}
		}
	}
}