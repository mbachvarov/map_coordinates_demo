<?php
declare(strict_types=1);

namespace Api\Map\Coordinates;

use Model\MapCoordinates;
use Util\Parser\MapCoordinatesAPIResponseParser;

interface MapCoordinatesAPI {
    /**
     * Returns the API name.
     *
     * @return string
     */
    function getName() : string;

     /**
     * Returns the URL for coordinates get request with added request parameters.
     *
     * @param string $address The address which coordinates we are looking for.
     * @return string
     */
    function generateGetCoordinatesRequestUrl(string $address): string;

    /**
     * Makes API request to get coordinates of the given address.
     *
     * Returns the request result.
     *
     * @param string $address The address which coordinates we need to find.
     * @return MapCoordinates
     */
    function coordinatesGetRequest(string $address) : ?MapCoordinates;

    /**
     * Sets the parser
     *
     * @param MapCoordinatesAPIResponseParser $parser The parser we want to set
     * @return void
     */
    public function setParser(MapCoordinatesAPIResponseParser $parser): void;
}
?>