<?php
	require __DIR__.'/../vendor/autoload.php';

	use Api\Map\Coordinates\MapCoordinatesAPIContainer;
	use Api\Map\Coordinates\BaseMapCoordinatesAPI;
	use Util\MapCoordinatesFinder;
	use Util\Parser\GoogleMapsCoordinatesAPIParser;
	use Util\Parser\OSMNominatimCoordinatesAPIParser;

	$address = "София";

    $apiContainer = new MapCoordinatesAPIContainer;
    
    // create google maps api object and add it to the map coordinates api container
	$googleMapsCoordinatesAPIConfig = include(__DIR__.'/../config/map_coordinates_api/google_maps_api_config.php');
	$googleMapsCoordinatesAPIParser = new GoogleMapsCoordinatesAPIParser();
	try{
		$googleMapsCoordinatesAPI = new BaseMapCoordinatesAPI($googleMapsCoordinatesAPIConfig);
		$googleMapsCoordinatesAPI->setParser($googleMapsCoordinatesAPIParser);
	    $apiContainer->add($googleMapsCoordinatesAPI);
	}catch(\InvalidArgumentException $e){
		echo json_encode(['error'=>'GoogleMapsCoordinatesAPI ERROR: ' .$e->getMessage()]);
		exit;
	}
    
    // create osm nominatim api object and add it to the map coordinates api container
    $osmMapsCoordinatesAPIConfig = include(__DIR__.'/../config/map_coordinates_api/osm_nominatim_api_config.php');
	$osmMapsCoordinatesAPIParser = new OSMNominatimCoordinatesAPIParser();
    try{
		$osmMapsCoordinatesAPI = new BaseMapCoordinatesAPI($osmMapsCoordinatesAPIConfig);
		$osmMapsCoordinatesAPI->setParser($osmMapsCoordinatesAPIParser);
	    $apiContainer->add($osmMapsCoordinatesAPI);
	}catch(InvalidArgumentException $e){
		echo json_encode(['error'=>'OSMNominatimCoordinatesAPI ERROR: ' .$e->getMessage()]);
		exit;
	}
    // create map coordinates finder class and find the address
    $mapCoordinatesFinder = new MapCoordinatesFinder($apiContainer);
    $mapCoordinatesFinder->findCoordinatesOf($address);

    echo json_encode($mapCoordinatesFinder->getResults());    
?>
