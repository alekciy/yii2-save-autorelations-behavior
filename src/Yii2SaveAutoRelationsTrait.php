<?php

namespace alekciy\Yii2SaveAutoRelations;

trait Yii2SaveAutoRelationsTrait
{
	public function __call($name, $arguments)
	{
		$relationName = preg_replace('~^get~iu', '', $name, 1);
		if (isset($this->autoRelationList[$relationName])) {
			return $this->autoRelationList[$relationName];
		}
		if ($name == 'loadRelations') {
			foreach ($arguments as $relationList) {
				foreach ($relationList as $relationName => $relation) {
					$this->{$relationName} = $relation;
				}
			}
		}
	}
}