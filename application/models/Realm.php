<?php
class App_Model_Realm extends App_Model_Base {
	protected $_dbTableName = 'realms';
	
	public function validateName($name, $region) {
		$db = Zend_Registry::get('db');
		$query = "SELECT id, slug FROM realms WHERE name = " . $db->quote($name) . " AND region = " . intval($region);
		$result = $db->fetchRow	($query);
		
		return $result;
	}
}