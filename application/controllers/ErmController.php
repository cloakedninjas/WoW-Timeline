<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction() {

    	// get Halbu achieves

    	//$json = json_decode(file_get_contents('http://eu.battle.net/api/wow/character/lightbringer/halbu?fields=achievements'));

		//$cross_ref = Model_Achievement::loadCrossReference();

		//var_dump($cross_ref);

    	$achv = new Model_Achievement();
    	$current = $achv->fetchAll();

    	//var_dump($list);
    	//exit;


    	$json = json_decode(file_get_contents('http://eu.battle.net/api/wow/data/character/achievements'));

    	echo "<pre>";
    	//var_dump($json);

    	$found = $added = 0;

    	foreach ($json->achievements as $a) {
    		if (isset($a->achievements)) {
    			foreach($a->achievements as $b) {
    				$exists = false;

    				$found++;

    				foreach ($current as $cur) {
    					//var_dump($cur->id);
    					//var_dump($b->id);
    					//exit;

    					if ($cur->id == $b->id) {
    						$exists = true;
    						break;
    					}
    				}

    				if (!$exists) {
    					$added++;
    					$new_ach = new Model_Achievement();
    					$new_ach->insert($b, $a->id);
    				}
    			}
    		}

    		if (isset($a->categories)) {

    			foreach($a->categories as $c) {
	    			if (isset($c->achievements)) {


		    			foreach($c->achievements as $d) {
		    				$exists = false;

		    				$found++;

		    				foreach ($current as $cur) {
		    					if ($cur->id == $d->id) {
		    						//echo "<p>$found : $cur->id == $b->id</p>";
		    						$exists = true;
		    						break;
		    					}
		    				}

		    				if (!$exists) {
		    					$added++;
		    					$new_ach = new Model_Achievement();
		    					$new_ach->insert($d, $c->id);
		    				}

		    				if (isset($c->categories)) {
		    					echo "erm? morrrre?";
		    					exit;
		    				}
		    			}

		    		}
    			}

    		}

    	}


    	echo "foound $found<Br />added $added";

    	exit;

	}




}

