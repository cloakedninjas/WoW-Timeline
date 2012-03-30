<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initAutoload() {
		//$moduleLoader = new Zend_Application_Module_Autoloader(array('namespace' => '', 'basePath' => APPLICATION_PATH));

		//$autoloader = Zend_Loader_Autoloader::getInstance();
		//$autoloader->registerNamespace(array('ZC_','My_'));

        //return $moduleLoader;
    }

    protected function _initConfig() {
    	$config = new Zend_Config($this->getOptions());
    	Zend_Registry::set('config',$config);

    	$db = $this->getPluginResource('db');
    	$db->getDbAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
    	Zend_Registry::set('db', $db->getDbAdapter('db'));

    	// profiler
    	//$db->getDbAdapter()->getProfiler()->setEnabled(false);

    }

    protected function _initRoutes() {
    	$ctrl  = Zend_Controller_Front::getInstance();
		$router = $ctrl->getRouter();
		$router->addRoute('chars', new Zend_Controller_Router_Route('char/:region/:realm/:name', array('controller' => 'char')));
	}
}