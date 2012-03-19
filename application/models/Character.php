<?php
class Model_Character {// extends Model_Base {

	//http:// <region> + .battle.net/static-render/ + <region> + / + <the string you got from API as thumbnail>

	protected $_dbTableName = 'chars';
	protected $_armory;
	protected $json;

	public $url;
	public $achievements = array();
	public $achievements_by_day = array();
	public $load_count;

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

		$this->_armory = new Model_Armory();
		$this->url = $this->_armory->getApiUrl($params);

	}

	public function loadJson($from = 0, $count = null) {
		$config = Zend_Registry::get('config');
		if ($count === null) {
			$count = $config->app->defaultLoadCount;
		}

		$this->json = $this->_armory->loadJson($this->url);

		// parse JSON

		$this->firstAchievementDate = time();
		$this->lastAchievementDate = 0;
		$this->load_count = $count;
		$this->achievement_points = $this->json->achievementPoints;

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

		// sort them by date order
		ksort($this->achievements_by_day);

		// reverse it
		$this->achievements_by_day = array_reverse($this->achievements_by_day, true);

		return $this;
	}

	public function getJsonData($achievements) {
		$data = array();
		$i = 0;

		foreach ($this->achievements_by_day as $day=>$achvs) {
			$achvs = explode(',', $achvs);

			$obj = new stdClass();
			$obj->y = date("Y", $day);
			$obj->m = date("M", $day);
			$obj->mm = date("m", $day);
			$obj->da = date("j", $day);

			$obj->a = array();

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

			if ($i >= $this->load_count) {
				break;
			}
		}
	}


}