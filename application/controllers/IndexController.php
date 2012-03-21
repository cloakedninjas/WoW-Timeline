<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
    	// get Halbu achieves
    	$char = new Model_Character();
    	$char->load(array(
    			'region' => 'eu',
    			'realm' => 'Lightbringer',
    			'name' => 'Halbu'
    	));

    	$char->loadAchievements(0, 100);
    	$this->view->char = $char;

    	// load achievement info for cross reference
    	$achievement = new Model_Achievement();
    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

    	$this->view->json_data = $char->getJsonFormat($achievement_data);
	}
}