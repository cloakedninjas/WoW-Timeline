<?php
class App_Model_AchievementCategory extends App_Model_Base {
	protected $_dbTableName = 'achievement_categories';

	public function insert($object, $parent_category=0) {
		$this->id = $object->id;
		$this->name = $object->name;
		$this->parent_id = $parent_category;

		$this->save(true);
	}

	public function checkMatchesArmory($cat, $parent_id) {
		$update = false;

		if ($this->name != $cat->name) {
			$update = true;
			$this->name = $cat->name;
		}

		if ($this->parent_id != $parent_id) {
			$update = true;
			$this->parent_id = $parent_id;
		}

		if ($update) {
			$this->save();
		}

		return $update;
	}
}