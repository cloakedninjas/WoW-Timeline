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

    	$char->loadJson(0, 100);

    	// load achievement info for cross reference
    	$achievement = new Model_Achievement();

    	$this->view->achievements = $achievement->loadCrossReference($char);
    	$this->view->char = $char;
    	$this->view->json_data = $char->getJsonData();
	}
}