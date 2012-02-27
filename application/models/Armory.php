<?php
class Model_Armory {

	public function getApiUrl($params) {
		return 'http://' . strtolower($params['region']) . '.battle.net/api/wow/character/' . strtolower($params['realm']) . '/' . strtolower($params['name']) . '?fields=achievements';
	}

	public function loadJson($url) {
		return json_decode(file_get_contents("D:/www/WoW Timeline/public/js/halbu.js"));
		return json_decode(file_get_contents($url));
	}

}