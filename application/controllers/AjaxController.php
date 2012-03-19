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

    	$char->loadJson($this->_getParam('start_at'), 100);

    	// load achievement info for cross reference
    	$achievement = new Model_Achievement();
    	$cross_ref = $achievement->loadCrossReference($char);

    	//echo json_encode($value)
	}
}