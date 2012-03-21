<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function loadEntriesAction() {
    	$char = new Model_Character();
    	$char->load(array(
    			'region' => 'eu',
    			'realm' => 'Lightbringer',
    			'name' => 'Halbu'
    	));

    	$char->loadAchievements($this->_getParam('start'), 100);

    	// load achievement info for cross reference
    	$achievement = new Model_Achievement();
    	$achievement_data = $achievement->loadCrossReference($char->achievements_by_day);

    	echo $char->getJsonFormat($achievement_data);
	}
}