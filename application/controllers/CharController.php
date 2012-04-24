<?php

class CharController extends Zend_Controller_Action {

    public function indexAction() {

    	// validate params
    	$armory = new App_Model_Armory();
    	$valid_params = $armory->validateParams($this->_getParam('region'), $this->_getParam('realm'), $this->_getParam('char'));

    	if ($valid_params !== false) {

	    	$char = new App_Model_Character();
	    	$config = Zend_Registry::get('config');

	    	try {
		    	$char->load($valid_params);
		    	$char->loadAchievements(0, $config->app->defaultLoadCount);

	  	    	// load achievement info for cross reference
		    	$achievement = new App_Model_Achievement();
		    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

		    	$this->view->json_data = $char->getJsonFormat($achievement_data);

	    	}
	    	catch (Exception $e) {
	    		$this->view->error = 'Could not load character : ' . $e->getMessage();
	    	}

    		$this->view->char = $char;
    		$this->view->headTitle()->append($char->name);
    	}
    	else {
    		$this->view->error = $armory->error;
    	}
	}
}