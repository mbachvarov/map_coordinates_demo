<?php
	use Util\Parser\GoogleMapsCoordinatesAPIParser;
	use Util\Parser\OSMNominatimCoordinatesAPIParser;

	return	[
	    		[
		    		'config_dir' => __DIR__.'/google_maps_api_config.php',
		    		'parser' => new GoogleMapsCoordinatesAPIParser()
		    	],
		    	[
		    		'config_dir' => __DIR__.'/osm_nominatim_api_config.php',
		    		'parser' => new OSMNominatimCoordinatesAPIParser()
		    	]
			];
?>