<?php
declare(strict_types=1);

namespace Util\Parser;

use Model\MapCoordinates;

/**
 * @implements MapCoordinatesAPI
 */
class OSMNominatimCoordinatesAPIParser implements MapCoordinatesAPIResponseParser {
    /**
     * Returns the result of the API get coordiantes call.
     *
     * @return MapCoordinates
     */
	public function parseCoordinatesGetResponse(?array $responseToParse) : ?MapCoordinates {
     	if($responseToParse==null || count($responseToParse) < 1) {
			return null;
		}

		if(!isset($responseToParse[0]['lat']) || !isset($responseToParse[0]['lon'])) {
			return null;
		}

        $latitude = (float)$responseToParse[0]['lat'];
        $longitude = (float)$responseToParse[0]['lon'];
        
        return new MapCoordinates($latitude, $longitude);
  	}
}
?>