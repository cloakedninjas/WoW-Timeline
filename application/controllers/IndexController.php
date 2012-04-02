<?php

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
    	$armory = new App_Model_Armory();
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    		var_dump($_POST);
    		
    		if (isset($armory->region_list[$this->_getParam('region')])) {
    			$region = $armory->region_list[$this->_getParam('region')];
    			
    			$realm = new App_Model_Realm();
    			$realm->validateName($this->_getParam('realm_name'), $this->_getParam('region'));
    		}
    			
    			

    	}

		
		$this->view->regions = $armory->region_list;

		$this->view->detected_region = null;

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			if (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en-us') !== false) {
				$this->view->detected_region = $armory::REGION_US;
			}
			elseif (
			stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'de-de') !== false ||
			stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'de') !== false ||
			stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en-gb') !== false ||
			stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'nl') !== false ||
			stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'fr') !== false) {
				$this->view->detected_region = $armory::REGION_EU;
			}
			elseif (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh-tw') !== false) {
				$this->view->detected_region = $armory::REGION_TW;
			}
			elseif (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh-cn') !== false) {
				$this->view->detected_region = $armory::REGION_CN;
			}


		}

	}
}