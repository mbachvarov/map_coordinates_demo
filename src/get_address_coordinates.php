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
		$apiContainer = initializeApiContainer();

	    $mapCoordinatesFinder = new MapCoordinatesFinder($apiContainer);
	    $mapCoordinatesFinder->findCoordinatesOf($address);

	    return json_encode($mapCoordinatesFinder->getResults());    	
	}

	function initializeAPIContainer() {
		$apiContainer = new MapCoordinatesAPIContainer;
	    $defaultAPIsConfigs =  include(__DIR__.'/../config/map_coordinates_api/config.php');
	    foreach ($defaultAPIsConfigs as $apiConfig) {
	    	try {
				$api = new BaseMapCoordinatesAPI(include($apiConfig['config_dir']));
				$api->setParser($apiConfig['parser']);
			    $apiContainer->add($api);
			} catch(\InvalidArgumentException $e) {
				$configDir = $apiConfig['config_dir'];
				echo json_encode(['error'=>"Map Coordinates API invalid config: $configDir"]);
				exit;
			} catch(\TypeError $e) {
				$configDir = $apiConfig['config_dir'];
				echo json_encode(['error'=>"Map Coordinates API config not found: $configDir"]);
				exit;	
			}
	    }

	    return $apiContainer;
	}
?>