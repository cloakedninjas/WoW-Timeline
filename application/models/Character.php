<?php
class Model_Character {// extends Model_Base {

	protected $_dbTableName = 'chars';
	protected $_armory;

	public $url;
	public $achievements = array();
	public $achievements_by_day = array();
	public $load_count;

	public function load(array $params, $load_count) {
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
		$this->json = $this->_armory->loadJson($this->url);

		// parse JSON

		$this->firstAchievementDate = time();
		$this->lastAchievementDate = 0;
		$this->load_count = $load_count;

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
	}

	public function getAchievementsForDate($date) {
		//$day_begins =
	}


}