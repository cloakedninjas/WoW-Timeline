<?php

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
    	$armory = new App_Model_Armory();

    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    		$valid = $armory->validateParams($this->_getParam('region'), $this->_getParam('realm_name'), $this->_getParam('char_name'));

    		if ($valid !== false) {
    			$this->getResponse()->setRedirect('/char/' . $valid['region'] . '/' . $valid['realm'] . '/' . strtolower($valid['char']));
    		}
    		else {
    			echo $armory->error;
    		}
    	}

		$this->view->regions = $armory->region_list;

		$this->view->detected_region = null;

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			if (
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
			elseif (stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en-us') !== false) {
				// leave this to the end - highly likely a foreign machine has en_US
				$this->view->detected_region = $armory::REGION_US;
			}


		}

	}
}