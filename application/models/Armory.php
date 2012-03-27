<?php
class Model_Armory {

	const REGION_US = 1;
	const REGION_EU = 2;
	const REGION_KR = 3;
	const REGION_TW = 4;
	const REGION_CN = 5;


	public $region_list = array(
		self::REGION_US => 'us',
		self::REGION_EU => 'eu',
		self::REGION_KR => 'kr',
		self::REGION_TW => 'tw',
		self::REGION_CN => 'cn',
	);

	public $error = null;
	public $params;

	public function __construct($params=null) {
		if ($params !== null) {
			$this->params = $params;
		}
	}

	public function getCharacterProfile($extra_fields=array()) {
		return json_decode(file_get_contents(APPLICATION_PATH . '/../cache/kinkeh.js'));

		$url = $this->getBaseUrl() . '/api/wow/character/' . strtolower($this->params['realm']) . '/' . strtolower($this->params['name']);

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