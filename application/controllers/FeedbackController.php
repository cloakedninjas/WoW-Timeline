<?php

class FeedbackController extends Zend_Controller_Action {

    public function indexAction() {
    	$csrf = new Zend_Form_Element_Hash(array('name'=>'csrf', 'salt'=>'Xd44ff'));
    	
    	if (!empty($_POST)) {
    		if ($csrf->isValid($this->_getParam('csrf'))) {
    			$config = Zend_Registry::get('config');
    			
    			$msg = "From: " . $this->_getParam('name') . "\n\n";
    			$msg .= "Email: " . $this->_getParam('email') . "\n\n";
    			$msg .= "===============================================\n\n";
    			$msg .= $this->_getParam('issue') . "\n\n";
    			
    			mail($config->feedback->to, "New Feedback", $msg);
    			
    			$this->view->sent = true;
    		}
    	}
    	else {
    		$this->view->csrf = $csrf;
    	}
	}
}