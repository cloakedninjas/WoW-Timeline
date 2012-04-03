<?php
class App_Model_Realm extends App_Model_Base {
	protected $_dbTableName = 'realms';

	public function validateName($name, $region_id) {
		$db = Zend_Registry::get('db');
		$query = "SELECT id, slug FROM realms WHERE name = " . $db->quote(ucfirst($name)) . " AND region = " . intval($region_id);

		$result = $db->fetchRow($query);

		return $result;
	}
}