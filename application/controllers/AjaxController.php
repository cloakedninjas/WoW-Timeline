<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function loadEntriesAction() {
    	$char = new App_Model_Character();
    	$char->load(array(
    			'region' => 'eu',
    			'realm' => 'Lightbringer',
    			'name' => 'Halbu'
    	));

    	$char->loadAchievements($this->_getParam('start'), 100);

    	// load achievement info for cross reference
    	$achievement = new App_Model_Achievement();
    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

    	echo $char->getJsonFormat($achievement_data);
	}

	public function loadRealmsAction() {

		$results = array();

		if ($this->_getParam('region') == null || $this->_getParam('prefix') == null) {

		}
		else {
			$armory = new App_Model_Armory();
			$results = $armory->lookupRealm($this->_getParam('region'), $this->_getParam('prefix'), 20);
		}
		
		// format results as CSV
		$return = '';
		
		foreach ($results as $r) {
			$return .= $r->name . ',';
		}
		
		echo substr($return, 0, -1);
	}
}