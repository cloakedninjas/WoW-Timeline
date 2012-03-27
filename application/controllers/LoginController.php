<?php

class LoginController extends Zend_Controller_Action {

	public function init() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$this->_helper->redirector('index', 'admin');
		}
	}

    public function indexAction() {
		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($this->_process($this->_getAllParams())) {
				$this->_helper->redirector('index', 'admin');
			}
			else {
				$this->view->error = 'Invalid username / password';
			}
		}
	}

	public function logoutAction() {
		Zend_Auth::getInstance()->clearIdentity();
		$this->_helper->redirector('index');
	}

	protected function _process($values) {
		$adapter = $this->_getAuthAdapter();
		$adapter->setIdentity($values['username']);
		$adapter->setCredential($values['password']);

		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($adapter);

		if ($result->isValid()) {
			$user = $adapter->getResultRowObject();
			$auth->getStorage()->write($user);
			return true;
		}

		return false;
	}

	protected function _getAuthAdapter() {

		$dbAdapter = Zend_Db_Table::getDefaultAdapter();
		$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

		$authAdapter->setTableName('users')
		->setIdentityColumn('username')
		->setCredentialColumn('password')
		->setCredentialTreatment('SHA1(CONCAT(?,salt))');

		return $authAdapter;
	}


}

