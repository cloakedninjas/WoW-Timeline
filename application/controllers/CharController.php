<?php

class CharController extends Zend_Controller_Action {

    public function indexAction() {

    	// validate params
    	$armory = new App_Model_Armory();
    	$valid = $armory->validateParams($this->_getParam('region'), $this->_getParam('realm'), $this->_getParam('char'));

    	if ($valid !== false) {

	    	$char = new App_Model_Character();

	    	try {
		    	$char->load(array(
					'region' => $this->_getParam('region'),
					'realm' => $this->_getParam('realm'),
					'char' => $this->_getParam('char')
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
    	else {
    		$this->view->error = $armory->error;
    	}
	}
}