<?php
declare(strict_types=1);

namespace Util\Parser;

use Model\MapCoordinates;

interface MapCoordinatesAPIResponseParser {
	/**
     * Ties to parse the API response and if successfull returns Object containing the coordinates else null is returned
     *
     * @return MapCoordinates
     */
	function parseCoordinatesGetResponse(array $responseToParse) : ?MapCoordinates;
}
?>