<?php
	require __DIR__.'/../vendor/autoload.php';
	
	use Util\MapCoordinatesFinder;
	use Api\Map\Coordinates\MapCoordinatesAPIContainer;
	use Api\Map\Coordinates\BaseMapCoordinatesAPI;
	
	if(isset($_GET['address'])) {
		echo findCoordinatesOf($_GET['address']);
	} else {
		echo json_encode(['error'=>'Missing address param']);
	}	

	function findCoordinatesOf($address) {	
		try {
			$apiContainer = initializeApiContainer();
		} catch(\InvalidArgumentException | \TypeError $e) {
			return json_encode(['error'=>$e->getMessage()]);
		}
	
	    $mapCoordinatesFinder = new MapCoordinatesFinder($apiContainer);
	    $mapCoordinatesFinder->findCoordinatesOf($address);

	    return json_encode($mapCoordinatesFinder->getResults());    	
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