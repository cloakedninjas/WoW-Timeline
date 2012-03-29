<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */

    	//Zend_Cache_Backend_File

    	$cache = Zend_Cache::factory(
			Zend_Registry::get('config')->cache->frontEnd,
			Zend_Registry::get('config')->cache->backEnd,
			array('automatic_serialization' => true, 'lifetime' => Zend_Registry::get('config')->cache->lifetime)
		);
    	echo $cache->getBackend()->getTmpDir();

    }

    public function indexAction() {
    	// get Halbu achieves
    	$char = new App_Model_Character();
    	$char->load(array(
    			'region' => 'eu',
    			'realm' => 'Lightbringer',
    			'name' => 'Halbu'
    	));

    	$fo = new App_Model_Achievement(6);

    	var_dump($fo);

    	exit;

    	$char->loadAchievements(0, 100);
    	$this->view->char = $char;

    	// load achievement info for cross reference
    	$achievement = new App_Model_Achievement();
    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

    	$this->view->json_data = $char->getJsonFormat($achievement_data);
	}
}