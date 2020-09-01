<?php
	require __DIR__.'/../vendor/autoload.php';
	
	use Util\MapCoordinatesFinder;
	use Api\Map\Coordinates\MapCoordinatesAPIContainer;
	use Api\Map\Coordinates\BaseMapCoordinatesAPI;
	
	if(isset($_GET['address'])) {
		try {
			echo json_encode(findCoordinatesOf($_GET['address']));
		} catch(\InvalidArgumentException | \TypeError $e) {
			echo json_encode(['error' => $e->getMessage()]);
		}
	} else {
		echo json_encode(['error'=>'Missing address param']);
	}	

	function findCoordinatesOf($address) {	
		$apiContainer = initializeApiContainer();

	    $mapCoordinatesFinder = new MapCoordinatesFinder($apiContainer);
	    $mapCoordinatesFinder->findCoordinatesOf($address);

	    return $mapCoordinatesFinder->getResults();    	
	}

	function initializeAPIContainer() {
		$apiContainer = new MapCoordinatesAPIContainer;
	    $defaultAPIsConfigs =  include(__DIR__.'/../config/map_coordinates_api/config.php');
	    foreach ($defaultAPIsConfigs as $apiConfig) {
			$api = new BaseMapCoordinatesAPI(include($apiConfig['config_dir']));
			$api->setParser($apiConfig['parser']);
		    $apiContainer->add($api);
	    }

	    return $apiContainer;
	}
?>