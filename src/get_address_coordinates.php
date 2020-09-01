<?php
	require __DIR__.'/../vendor/autoload.php';
	
	use Util\MapCoordinatesFinder;
	use Api\Map\Coordinates\MapCoordinatesAPIContainer;
	use Api\Map\Coordinates\BaseMapCoordinatesAPI;
	
	/**
	* returns json with the result of address coordinates searching.
	* if no address is provided or map API are not initialized because of invalid config files, error message is returned
	* Response examples: 
	*	{"apis":["google_maps","osm_nominatim"],"google_maps":{"longitude":23.3456723,"latitude":42.4753951},"osm_nominatim":{"longitude":23.3332341,"latitude":42.6528464}}
	*   {"apis":["google_maps","osm_nominatim"],"google_maps":{"longitude":null,"latitude":null},"osm_nominatim":{"longitude":null,"latitude":null}
	*	{"error": "Missing address param"}
	*/
	if(isset($_GET['address'])) {
		try {
			echo json_encode(findCoordinatesOf($_GET['address']));
		} catch(\InvalidArgumentException | \TypeError $e) {
			echo json_encode(['error' => $e->getMessage()]);
		}
	} else {
		echo json_encode(['error'=>'Missing address param']);
	}	

	/**
	*
	* Uses the API container to fid coordinates of given address
	* 
	*/
	function findCoordinatesOf($address) {	
		$apiContainer = initializeApiContainer();

	    $mapCoordinatesFinder = new MapCoordinatesFinder($apiContainer);
	    $mapCoordinatesFinder->findCoordinatesOf($address);

	    return $mapCoordinatesFinder->getResults();    	
	}

	/**
	*
	* Initializes map APIs defined into the configuration file and adds them to the API container
	* 
	*/
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