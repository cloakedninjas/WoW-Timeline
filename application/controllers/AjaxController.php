<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        $this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    }

    public function loadEntriesAction() {
    	// get Halbu achieves
    	$char = new Model_Character();
    	$char->load(array(
    			'region' => $this->_getParam('region'),
    			'realm' => $this->_getParam('realm'),
    			'name' => $this->_getParam('name')
    			), 100);

    	$achievement = new Model_Achievement();

    	$achievements = $achievement->loadCrossReference($char);
	}
}