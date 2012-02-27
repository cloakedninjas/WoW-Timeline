<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {

    	//echo "<pre>";

    	// get Halbu achieves
    	$char = new Model_Character();
    	$char->load(array(
    			'region' => 'eu',
    			'realm' => 'Lightbringer',
    			'name' => 'Halbu'
    			));

    	//var_dump($char);

    	$achvs = new Model_Achievement();
    	$all_achievements = $achvs->fetchAll();

    	foreach ($char->achievements_by_day as $day=>$achvs) {
    		$achvs = explode(',', $achvs);

    		echo "<h1>" . date("M d Y", $day) . "</h1>";

    		foreach ($achvs as $a) {
    			echo "<p>" . $all_achievements[$a]->name . "</p>";
    		}

    		echo "<hr />";
    		//exit;
    	}



    	exit;

	}




}