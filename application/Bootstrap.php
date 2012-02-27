<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initAutoload() {
		$moduleLoader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '',
			'basePath' => APPLICATION_PATH));

		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace(array('ZC_','My_'));

		$config = new Zend_Config($this->getOptions());
		Zend_Registry::set('config',$config);

        return $moduleLoader;
    }
}