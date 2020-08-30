<?php
declare(strict_types=1);

namespace Util\Parser;

use Model\MapCoordinates;

/**
 * @implements MapCoordinatesAPI
 */
class GoogleMapsCoordinatesAPIParser implements MapCoordinatesAPIResponseParser {
    /**
     * Returns the result of the API get coordiantes call.
     *
     * @return MapCoordinates
     */
	public function parseCoordinatesGetResponse(?array $responseToParse) : ?MapCoordinates {
		if(!isset($responseToParse['status']) || $responseToParse['status'] != 'OK'){
			return null;
		}

		if(!isset($responseToParse['results'][0]['geometry']['location']['lat']) || !isset($responseToParse['results'][0]['geometry']['location']['lng'])) {
			
		}

        $latitude = (float)$responseToParse['results'][0]['geometry']['location']['lat'];
        $longitude = (float)$responseToParse['results'][0]['geometry']['location']['lng']; 

    	return new MapCoordinates($latitude, $longitude);
  	}
}
?>