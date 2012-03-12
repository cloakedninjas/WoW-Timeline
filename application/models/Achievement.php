<?php
class Model_Achievement extends Model_Base {

	protected $_dbTableName = 'achievements';

	public $cross_ref = array();

	public function loadCrossReference(Model_Character $char) {
		$db = Zend_Registry::get("db");

		if (count($char->achievements_by_day) > 0) {
	    	$query = "SELECT * FROM achievements WHERE id IN (";

	    	$i = 0;

	    	foreach ($char->achievements_by_day as $day=>$achvs) {
				$achvs = explode(',', $achvs);

				foreach ($achvs as $a) {
	    			$query .= $a . ', ';
	    			$i++;
				}

				// allow more than load_count to complete day's achievements
				if ($i >= $char->load_count) {
					break;
				}
	    	}

	    	$query = substr($query, 0, -2);
	    	$query .= ')';

			$rows = $db->fetchAll($query);

			foreach ($rows as $row) {
				$this->cross_ref[$row->id] = $row;
			}
		}

		return $this;
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