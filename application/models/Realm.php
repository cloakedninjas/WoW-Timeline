<?php
class App_Model_Realm extends App_Model_Base {
	protected $_dbTableName = 'realms';

	public function validateName($slug, $region_id) {
		$db = Zend_Registry::get('db');
		
		$query = "SELECT id, name, slug FROM realms WHERE slug = " . $db->quote($slug) . " AND region = " . intval($region_id);

		$result = $db->fetchRow($query);

		return $result;
	}
}