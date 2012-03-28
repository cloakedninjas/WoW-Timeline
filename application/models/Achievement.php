<?php
class Model_Achievement extends Model_Base {

	const EXP_INTRO = 0;
	const EXP_WOTLK = 1;
	const EXP_CATA = 2;
	const EXP_MOP = 3;

	protected $_dbTableName = 'achievements';

	public $cross_ref = array();

	public function loadCrossReference($achv_by_day) {
		$db = Zend_Registry::get("db");

		if (count($achv_by_day) > 0) {
	    	$query = "SELECT * FROM achievements WHERE id IN (";

	    	$i = 0;

	    	foreach ($achv_by_day as $day=>$achvs) {
				$achvs = explode(',', $achvs);

				foreach ($achvs as $a) {
	    			$query .= $a . ', ';
	    			$i++;
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

	public function getExpansion($date) {
		$config = Zend_Registry::get('config');

		$region = 'us';

		$exps = $config->wow->expansions->$region;

		$return = 0;
		foreach ($exps as $index=>$e) {
			if ($date >= $e) {
				$return = $index;
			}
		}

		return $return;
	}

	public function insert($object, $parent_category) {
		$this->id = $object->id;
		$this->name = $object->title;
		$this->points = $object->points;
		$this->description = $object->description;
		$this->category_id = $parent_category;
		$this->save(true);
	}

	public function checkMatchesArmory($achv, $cat_id) {
		$update = false;

		if ($this->name != $achv->title) {
			$update = true;
			$this->name = $achv->title;
		}

		if ($this->description != $achv->description) {
			$update = true;
			$this->description = $achv->description;
		}

		if ($this->points != $achv->points) {
			$update = true;
			$this->points = $achv->points;
		}

		if ($this->category_id != $cat_id) {
			$update = true;
			$this->category_id = $cat_id;
		}

		if ($update) {
			$this->save();
		}

		return $update;
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