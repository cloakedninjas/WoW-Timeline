<?php
class App_Model_Character extends App_Model_Base {

	//http:// <region> + .battle.net/static-render/ + <region> + / + <the string you got from API as thumbnail>

	protected $_dbTableName = 'characters';
	protected $_armory;
	protected $json;

	public $achievements = array();
	public $achievements_by_day = array();

	public $entry_start = 0;
	public $entry_count = 0;

	public function load(array $params) {
		if (!isset($params['region'])) {
			throw new BadMethodCallException('Missing param: region');
		}

		if (!isset($params['realm'])) {
			throw new BadMethodCallException('Missing param: realm');
		}

		if (!isset($params['name'])) {
			throw new BadMethodCallException('Missing param: name');
		}

		$this->_armory = new App_Model_Armory($params);
	}

	public function loadAchievements($start, $count) {
		$this->entry_start = $start;
		$this->entry_count = $count;

		$this->json = $this->_armory->getCharacterProfile(array('achievements'));

		$this->firstAchievementDate = time();
		$this->lastAchievementDate = 0;
		$this->achievement_points = $this->json->achievementPoints;

		// parse JSON
		foreach ($this->json->achievements->achievementsCompleted as $index=>$achv_id) {

			$time = $this->json->achievements->achievementsCompletedTimestamp[$index] / 1000;
			$this->achievements[$achv_id] = $time;

			$day_start = strtotime(date("Y-m-d", $time));

			if (isset($this->achievements_by_day[$day_start])) {
				$this->achievements_by_day[$day_start] .= ',' . $achv_id;
			}
			else {
				$this->achievements_by_day[$day_start] = $achv_id;
			}


			if ($time < $this->firstAchievementDate) {
				$this->firstAchievementDate = $time;
			}

			if ($time > $this->lastAchievementDate) {
				$this->lastAchievementDate = $time;
			}
		}

		$this->total_entries = count($this->achievements_by_day);

		// sort them by date order
		ksort($this->achievements_by_day);

		// reverse it
		$this->achievements_by_day = array_reverse($this->achievements_by_day, true);

		// splice it to the required portion
		$this->achievements_by_day = array_slice($this->achievements_by_day, $start, $count, true);

		return $this;
	}

	public function getJsonFormat(App_Model_Achievement $achievements) {

		$data = array();
		$i = 0;

		$prev_exp = null;

		foreach ($this->achievements_by_day as $day=>$achvs) {
			$achvs = explode(',', $achvs);

			$obj = new stdClass();
			$obj->y = date("Y", $day);
			$obj->m = date("M", $day);
			$obj->mm = date("m", $day);
			$obj->da = date("j", $day);
			$obj->a = array();

			$exp = $achievements->getExpansion($day);

			if ($prev_exp != $exp) {
				$obj->exp = $exp;
			}

			$prev_exp = $exp;

			foreach ($achvs as $a) {
				$i++;
				$obj2 = new stdClass();
				$obj2->n = $achievements->cross_ref[$a]->name;
				$obj2->d = $achievements->cross_ref[$a]->description;

				if ($achievements->cross_ref[$a]->noteable) {
					$obj2->no = true;
				}

				$obj->a[] = $obj2;
			}

			$data[] = $obj;
		}

		return json_encode($data);
	}
}

class App_Model_Character_Exception extends Zend_Exception {}