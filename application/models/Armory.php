<?php
class App_Model_Armory {

	const REGION_US = 1;
	const REGION_EU = 2;
	const REGION_KR = 3;
	const REGION_TW = 4;
	const REGION_CN = 5;

	const RACE_HUMAN =		1;
	const RACE_ORC =		2;
	const RACE_DWARF =		3;
	const RACE_NIGHT_ELF =	4;
	const RACE_UNDEAD =		5;
	const RACE_TAUREN =		6;
	const RACE_GNOME =		7;
	const RACE_TROLL =		8;
	const RACE_GOBLIN =		9;
	const RACE_BLOOD_ELF =	10;
	const RACE_DRAENEI =	11;
	const RACE_WORGEN =		22;
	const RACE_PANDAREN =	24;

	const CLASS_WARRIOR =	1;
	const CLASS_PALADIN =	2;
	const CLASS_HUNTER =	3;
	const CLASS_ROGUE =		4;
	const CLASS_PRIEST =	5;
	const CLASS_DEATH_KNIGHT =	6;
	const CLASS_SHAMAN =	7;
	const CLASS_MAGE =		8;
	const CLASS_WARLOCK =	9;
	const CLASS_MONK =		10;
	const CLASS_DRUID =		11;

	public $region_list = array(
		self::REGION_US => 'us',
		self::REGION_EU => 'eu',
		self::REGION_KR => 'kr',
		self::REGION_TW => 'tw',
		self::REGION_CN => 'cn',
	);

	public $race_list = array (
		self::RACE_HUMAN => 'Human',
		self::RACE_ORC => 'Orc',
		self::RACE_DWARF => 'Dwarf',
		self::RACE_NIGHT_ELF => 'Night Elf',
		self::RACE_UNDEAD => 'Undead',
		self::RACE_TAUREN => 'Tauren',
		self::RACE_GNOME => 'Gnome',
		self::RACE_TROLL => 'Troll',
		self::RACE_GOBLIN => 'Goblin',
		self::RACE_BLOOD_ELF => 'Blood Eld',
		self::RACE_DRAENEI => 'Draenei',
		self::RACE_WORGEN => 'Worgen',
		self::RACE_PANDAREN => 'Pandaren'
	);

	public $class_list = array (
		self::CLASS_WARRIOR => 'Warrior',
		self::CLASS_PALADIN => 'Paladin',
		self::CLASS_HUNTER => 'Hunter',
		self::CLASS_ROGUE => 'Rogue',
		self::CLASS_PRIEST => 'Priest',
		self::CLASS_DEATH_KNIGHT => 'Death Knight',
		self::CLASS_SHAMAN => 'Shaman',
		self::CLASS_MAGE => 'Mage',
		self::CLASS_WARLOCK => 'Warlock',
		self::CLASS_MONK => 'Monk',
		self::CLASS_DRUID => 'Druid'
	);



	public $error = null;
	public $params;

	public function __construct($params=null) {
		if ($params !== null) {
			$this->params = $params;
		}
	}

	public function getCharacterProfile($extra_fields=array()) {
		//return json_decode(file_get_contents(APPLICATION_PATH . '/../cache/kinkeh.js'));

		$url = $this->getBaseUrl() . '/api/wow/character/' . strtolower($this->params['realm']) . '/' . strtolower($this->params['char']);

		if (!empty($extra_fields)) {
			$url .= '?fields=';
			$url .= implode(',', $extra_fields);
		}

		return $this->makeRequest($url);
	}

	/*
	 * API resource getters
	 */

	public function getAchievementResource() {
		$url = 'http://eu.battle.net/api/wow/data/character/achievements';
		return $this->makeRequest($url);
	}

	public function getRealmList($region=null) {
		if ($region === null) {
			$return = array();

			foreach ($this->region_list as $region) {
				$url = 'http://' . $region . '.battle.net/api/wow/realm/status';
				$list = $this->makeRequest($url);
				$return[$region] = $list;
			}
			return $return;
		}
		else {
			$url = 'http://' . $region . '.battle.net/api/wow/realm/status';
			return $this->makeRequest($url);
		}
	}

	public function getDataResource($type) {
		switch ($type) {
			case 'achievements':

				break;

			case 'realms':
				$url = 'http://eu.battle.net/api/wow/data/character/achievements';
				break;
		}

		return $this->makeRequest($url);
	}

	public function lookupRealm($region, $prefix, $limit) {
		$db = Zend_Registry::get('db');

		$region = array_search($region, $this->region_list);

		if ($region === false) {
			return;
		}

		$prefix = str_replace('%', '', $prefix) . '%';
		$prefix = ucfirst($prefix);

		$query = '
		SELECT name FROM realms WHERE region = ' . $region . ' AND name LIKE ' . $db->quote($prefix) . '
		ORDER BY name
		LIMIT ' . intval($limit);

		return $db->fetchAll($query);
	}

	public function validateParams($region, $realm_name, $char_name) {
		$params = array(
			'region'=>null,
			'region_id'=>null,
			'realm'=>null,
			'realm_id'=>null,
			'char'=>null,
		);

		if (($index = array_search($region, $this->region_list)) !== false) {
			$params['region'] = $region;
			$params['region_id'] = $index;

			$realm = new App_Model_Realm();
			$realm = $realm->validateName($realm_name, $index);

			if ($realm !== false) {
				$params['realm'] = $realm->slug;
				$params['realm_id'] = $realm->id;
				$params['char']= $this->validateCharacterName($char_name);

				return $params;
			}
			else {
				$this->error = 'Could not find realm : ' . $region . '-' . $realm_name;
			}
		}
		else {
			$this->error = 'We do not currently support the region : ' . $region;
		}
		return false;
	}

	public function validateCharacterName($name) {
		if (preg_match("/[0-9]/", $name)) {
			return false;
		}

		if (strlen($name) > 12) {
			return false;
		}

		return ucfirst($name);
	}

	protected function getBaseUrl() {
		return 'http://' . strtolower($this->params['region']) . '.battle.net';
	}

	protected function makeRequest($url) {

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL					=>	$url,
			CURLOPT_RETURNTRANSFER		=>	true
		));


		$line = date("Y-m-d H:i:s") . "\t\t$url";
		file_put_contents(APPLICATION_PATH . '/../cache/http.log', $line, FILE_APPEND);

		$response = curl_exec($ch);

		$line = "\t\t OK\n";
		file_put_contents(APPLICATION_PATH . '/../cache/http.log', $line, FILE_APPEND);



		if ($response !== false) {
			$response = json_decode($response);

			if ($response !== null) {
				return $response;
			}
			else {
				$this->error = 'Could not decode JSON';
			}
		}
		else {
			$this->error = 'HTTP request failed: ' . curl_error($ch);
		}

		return false;
	}

}