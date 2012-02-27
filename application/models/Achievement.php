<?php
class Model_Achievement extends Model_Base {

	protected $_dbTableName = 'achievements';

	public static function loadCrossReference() {
		//$db = Zend_Registry::get('db');
		//$db->query("SELECT")
		$achievement = new Model_Achievement();
		$foo = $achievement->fetchAll('in_use = 1');

		return $foo;
	}

	public function insert($object, $parent_category) {
		$this->id = $object->id;
		$this->name = $object->title;
		$this->points = $object->points;
		$this->description = $object->description;
		$this->category_id = $parent_category;
		$this->save(true);
	}

	public function load() {
		$url = "http://eu.battle.net/api/wow/data/character/achievements";
		$json = json_decode(file_get_contents($url));



		foreach ($json->achievements as $a) {
			foreach ($a->achievements as $a) {
				var_dump($a);
				exit;
			}
		}
	}

	public function fetchAll($where = null) {
		//return $this->getDbTable()->fetchAll($where);

		$class = get_class($this);

		$resultSet = $this->getDbTable()->fetchAll($where);

		$entries = array();
		foreach ($resultSet as $row) {

			$entry = new stdClass();

			foreach ($this->getDbTable()->getColumns() as $col) {
				$entry->$col = $row->$col;
			}
			$entries[$row->id] = $entry;
		}

		return $entries;
	}

}