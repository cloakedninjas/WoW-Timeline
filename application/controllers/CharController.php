<?php

class CharController extends Zend_Controller_Action {

    public function indexAction() {
    	var_dump($this->_getAllParams());

    	$char = new App_Model_Character();

    	try {
	    	$char->load(array(
	    			'region' => $this->_getParam('region'),
	    			'realm' => $this->_getParam('realm'),
	    			'name' => $this->_getParam('name')
	    	));

	    	$char->loadAchievements(0, 100);

  	    	// load achievement info for cross reference
	    	$achievement = new App_Model_Achievement();
	    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

	    	$this->view->json_data = $char->getJsonFormat($achievement_data);

    	}
    	catch (Exception $e) {
    		$this->view->error = 'Could not load character : ' . $e->getMessage();
    	}

    	$this->view->char = $char;
	}
}