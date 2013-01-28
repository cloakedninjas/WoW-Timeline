<?php
class App_Model_Character extends App_Model_Base {
	protected $_dbTableName = 'characters';
	protected $_armory;
	protected $json;

	public $params;

	public $achievements = array();
	public $achievements_by_day = array();

	public $entry_start = 0;
	public $entry_count = 0;

	public function load($params) {
		if (is_array($params)) {
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
		}
		elseif(is_numeric($params)) {
			$this->find($params);

			if ($this->id != 0) {
				// char id does exist - define params
				$armory = new App_Model_Armory();
				$realm = new App_Model_Realm($this->realm);

				$this->params = array(
					'region'=>$armory->region_list[$this->region],
					'region_id'=>$this->region,
					'realm'=>$realm->slug,
					'realm_id'=>$this->realm,
					'char'=>$this->name,
				);
			}
		}

		$this->_armory = new App_Model_Armory($this->params);
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

				$data = $this->loadDataFromArmory();
				$this->updateCache($data);
			}
			else {
				$data = $this->loadDataFromCache();

				if ($data === false) {
					throw new App_Model_Character_Exception('Failed to load cache');
				}
			}
		}
		else {
			$data = $this->loadDataFromArmory();
			$this->createCache($data);
		}

		$this->json = $data;

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
		return $age < $config->app->cache->lifetime;
	}

	public function createCache($data) {
		$this->firstCached = $this->now();
		$this->cacheCount = 0;
		$this->lastModified = null;
		$this->updateCache($data);
	}

	public function updateCache($data) {
		if ($this->lastModified == $data->lastModified) {
			return true;
		}

		$path = $this->getCachePath();

		file_put_contents($path . '/' . $this->getCacheFilename(), json_encode($data));

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

		foreach ($data->titles as $t) {
			if (isset($t->selected) && $t->selected) {
				$this->title = $t->name;
				break;
			}
		}

		if (isset($data->guild)) {
			$this->guildName = $data->guild->name;
		}

		$this->lastCached = $this->now();
		$this->cacheCount++;

		$this->save();
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
				$obj2->i = $achievements->cross_ref[$a]->icon;

				if ($achievements->cross_ref[$a]->noteable) {
					$obj2->no = true;
				}

				$obj->a[] = $obj2;
			}

			$data[] = $obj;
		}

		return json_encode($data);
	}

	public function getDisplayName() {
		if ($this->title) {
			return sprintf($this->title, $this->name);
		}
		return $this->name;
	}

	public function getDisplayGuildName() {
		if ($this->guildName) {
			return '&lt; ' . $this->guildName . '&gt;';
		}
		return '';
	}



	public function getThumbnail() {
		return 'http://' . $this->params['region'] . '.battle.net/static-render/' . $this->params['region'] . '/' . $this->thumbnail;
	}

	public function getRacialName() {
		return $this->_armory->race_list[$this->race];
	}

	public function getClassName() {
		return $this->_armory->class_list[$this->class];
	}

    public function getLastLookups($count = 5) {
        $db = Zend_Registry::get('db');

        $rows = $db->fetchAll('
		SELECT c.name, c.achievementPoints, r.region, r.slug
        FROM characters AS c
        INNER JOIN realms AS r ON c.realm = r.id
        ORDER BY c.lastCached DESC
        LIMIT ' . $count
		);

        return $rows;
    }

	protected function getCachePath() {
		$config = Zend_Registry::get('config');

		$path = $config->app->cache->path . '/' . $this->params['region'] . '/' . strtolower($this->params['realm']);

		if (!is_dir($path)) {
			mkdir($path, intval($config->app->cache->mode, 8), true);
		}

		return $path;
	}

	protected function getCacheFilename() {
		return strtolower($this->params['char']) . '.json';
	}

	protected function loadDataFromArmory() {
		$data = $this->_armory->getCharacterProfile(array('achievements', 'titles', 'guild'));

		if ($data === false) {
			throw new App_Model_Character_Exception($this->_armory->error);
		}
		elseif (isset($data->status) && $data->status == 'nok') {
			throw new App_Model_Character_Exception($data->reason);
		}
		return $data;
	}

	protected function loadDataFromCache() {
		$filename = $this->getCachePath() . '/' . $this->getCacheFilename();

		if (is_file($filename)) {
			return json_decode(file_get_contents($filename));
		}
		return false;
	}
}

class App_Model_Character_Exception extends Zend_Exception {}