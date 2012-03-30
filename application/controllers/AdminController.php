<?php

class AdminController extends Zend_Controller_Action {

	public function init() {
		$auth = Zend_Auth::getInstance();

		if (!$auth->hasIdentity()) {
			$this->_helper->redirector('index', 'login');
		}

		$this->_helper->_layout->setLayout('admin-layout');
	}

    public function indexAction() {
    }

    public function syncRealmsAction() {

    	set_time_limit(0);

    	$armory = new App_Model_Armory();
    	$realms = $armory->getRealmList();

    	$db = Zend_Registry::get('db');
    	$existing_realms = $db->fetchAll('SELECT * FROM realms ORDER by region, name');

    	$results = array('delete'=>array(), 'add'=>array(), 'found'=>0);

    	foreach ($existing_realms as $existing) {
    		$exists = false;

    		$region_code = $armory->region_list[$existing->region];

    		foreach ($realms[$region_code]->realms as $realm) {
				if ($realm->name == $existing->name) {
					$exists = true;
					break;
				}
    		}

    		if (!$exists) {
    			$results['delete'][] = $existing->name;

    			// TODO: add delete / flag for deletion
    		}
    	}

    	foreach ($realms as $region=>$realms_object) {
    		foreach ($realms_object->realms as $realm) {
    			$results['found']++;

	    		$exists = false;

	    		foreach ($existing_realms as $existing) {
	    			if ($realm->name == $existing->name) {
						$exists = true;
						break;
					}
	    		}

	    		if (!$exists) {
	    			$results['add'][] = $realm->name;

	    			$r = new App_Model_Realm();
	    			$r->name = $realm->name;
	    			$r->slug = $realm->slug;
	    			$r->region = array_search($region, $armory->region_list);
	    			$r->save();

	    		}
    		}
    	}

    	$this->view->results = $results;


    }

	public function syncAchievementsAction() {
		set_time_limit(0);

		//TODO: localization of achievement names

		$results = array(
			'category_add'=>array(),
			'category_remove'=>array(),
			'category_update'=>array(),
			'category_check'=>0,
			'achv_add'=>array(),
			'achv_update'=>array(),
			'achv_check'=>0
		);

    	$armory = new App_Model_Armory();
    	$achievements = $armory->getAchievementResource()->achievements;

    	$db = Zend_Registry::get('db');

    	foreach($achievements as $category) {

    		// does the category exist?
    		$exists = $db->query('SELECT id FROM achievement_categories WHERE id = ' . intval($category->id));

    		if ($exists->rowCount() == 0) {
    			// create new category
    			$ac = new App_Model_AchievementCategory();
    			$ac->insert($category);

    			$results['category_add'][] = $category->name;
			}
			else {
				$ac = new App_Model_AchievementCategory($exists->fetch()->id);
				$update = $ac->checkMatchesArmory($category, 0);

				if ($update) {
					$results['category_update'][] = $category->name;
				}
			}
			$results['category_check']++;

			// go through each achievement at the root level

			if (isset($category->achievements)) {
				foreach ($category->achievements as $achievement) {
					$exists = $db->query('SELECT id FROM achievements WHERE id = ' . intval($achievement->id));

					if ($exists->rowCount() == 0) {
		    			// create new achievement
		    			$a = new App_Model_Achievement();
		    			$a->insert($achievement, $category->id);
		    			$results['achv_add'][] = $achievement->name;
					}
					else {
						$a = new App_Model_Achievement($exists->fetch()->id);
						$update = $a->checkMatchesArmory($achievement, $category->id);

						if ($update) {
							$results['achv_update'][] = $achievement->name;
						}
					}
					$results['achv_check']++;
				}
			}

			if (isset($category->categories)) {

				foreach($category->categories as $sub_category) {

					// does the sub category exist?
					$exists = $db->query('SELECT id FROM achievement_categories WHERE id = ' . intval($sub_category->id));

					if ($exists->rowCount() == 0) {
		    			// create new category
		    			$ac = new App_Model_AchievementCategory();
    					$ac->insert($sub_category, $category->id);
		    			$results['category_add'][] = $category->name . ' : ' . $sub_category->name;
					}
					else {
						$ac = new App_Model_AchievementCategory($exists->fetch()->id);
						$update = $ac->checkMatchesArmory($sub_category, $category->id);

						if ($update) {
							$results['category_update'][] = $category->name . ' : ' . $sub_category->name;
						}
					}
					$results['category_check']++;

					// go through each achievement at the sub category level

					if (isset($sub_category->achievements)) {
						foreach ($sub_category->achievements as $achievement) {
							$exists = $db->query('SELECT id FROM achievements WHERE id = ' . intval($achievement->id));

							if ($exists->rowCount() == 0) {
				    			// create new achievement
				    			$a = new App_Model_Achievement();
				    			$a->insert($achievement, $sub_category->id);

				    			$results['achv_add'][] = $category->name . ' : ' . $achievement->name;
							}
							else {
								$a = new App_Model_Achievement($exists->fetch()->id);
								$update = $a->checkMatchesArmory($achievement, $sub_category->id);

								if ($update) {
									$results['achv_update'][] = $category->name . ' : ' . $achievement->name;
								}
							}
							$results['achv_check']++;
						}
					}
				}
			}

    	}

    	$this->view->results = $results;

    	//$existing_achievements = $db->fetchAll('SELECT * FROM achievements');
    }


}
