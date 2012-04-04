<?php
class App_Model_Character extends App_Model_Base {

	//http:// <region> + .battle.net/static-render/ + <region> + / + <the string you got from API as thumbnail>

	protected $_dbTableName = 'characters';
	protected $_armory;
	protected $json;

	public $params;

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

		if (!isset($params['char'])) {
			throw new BadMethodCallException('Missing param: char');
		}

		$this->params = $params;
		$this->_armory = new App_Model_Armory($params);
	}

	public function loadAchievements($start, $count) {
		$this->entry_start = $start;
		$this->entry_count = $count;

		// is there a cache?
		$exists = $this->findByParams($this->params);

		if ($exists) {
			// is cache fresh?
			if (!$this->cacheUpToDate()) {
				// attempt to get data

				try {
					$data = $this->loadAchievementsFromArmory();
					$this->updateCache($data);
				}
				catch (App_Model_Character_Exception $e) {
					// Char no longer exists
				}
			}
		}
		else {
			$data = $this->loadAchievementsFromArmory();
			$this->createCache($data);
		}

		// start parsing

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

	public function findByParams($params) {
		$db = Zend_Registry::get('db');

		$row = $db->fetchRow('
		SELECT * FROM characters
		WHERE realm = ' . $db->quote($params['realm_id']) . '
		AND name = ' . $db->quote($params['char']));

		if ($row !== false) {
			$this->bindFromRow($row);
			return true;
		}
		return false;
	}

	public function cacheUpToDate() {
		$config = Zend_Registry::get('config');

		$age = time() - strtotime($this->lastCached);

		echo "<p>age is $age</p>";

		var_dump($age < $config->app->cache->lifetime);

		exit;

		return $age < $config->app->cache->lifetime;
	}

	public function createCache($data) {
		$config = Zend_Registry::get('config');
		$path = $config->app->cache->path . '/' . $this->params['region'] . '/' . strtolower($this->params['realm']);

		if (!is_dir($path)) {
			mkdir($path, intval($config->app->cache->mode, 8), true);
		}

		file_put_contents($path . '/' . strtolower($this->params['char']) . '.json', json_encode($data));

		$this->name = $data->name;
		$this->region = $this->params['region_id'];
		$this->realm =  $this->params['realm_id'];

		$this->lastModified = $data->lastModified;
		$this->class = $data->class;
		$this->race = $data->race;
		$this->gender = $data->gender;
		$this->level = $data->level;
		$this->achievementPoints = $data->achievementPoints;
		$this->thumbnail = $data->thumbnail;

		$this->firstCached = $this->now();
		$this->lastCached = $this->now();
		$this->cacheCount = 1;

		$this->save();
	}

	public function updateCache($data) {
		exit;
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

	protected function loadAchievementsFromArmory() {
		$data = $this->_armory->getCharacterProfile(array('achievements'));

		if ($data === false) {
			throw new App_Model_Character_Exception($this->_armory->error);
		}
		elseif (isset($data->status) && $data->status == 'nok') {
			throw new App_Model_Character_Exception($data->reason);
		}
		return $data;
	}
}

class App_Model_Character_Exception extends Zend_Exception {}