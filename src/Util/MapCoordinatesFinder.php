<?php
declare(strict_types=1);

namespace Util;
use Api\Map\Coordinates\MapCoordinatesAPIContainer;

class MapCoordinatesFinder {
	/**
     * Container with APIs for map coordinates search.
     *
     * @var MapCoordinatesAPIContainer
     */
	private MapCoordinatesAPIContainer $mapCoordinatesAPIContainer;
	
	/**
     * The result of the API request for coordinates searching by address
     *
     * @var array
     */
	private array $results;

	/**
     * Class constructor.
     *
     * @param MapCoordinatesAPIContainer $mapCoordinatesAPIContainer
     */
	function __construct(MapCoordinatesAPIContainer $mapCoordinatesAPIContainer) {
		$this->mapCoordinatesAPIContainer = $mapCoordinatesAPIContainer;
		// $this->results = array();
	}

	/**
     * Iterates through all APIs in the container.
	 *
     * Request for getting coordinates by address is made for every one of the APIs in the container
     *  
     * Stores the result of every request if it is successful and the response is successfully parsed
     *
     */
	function findCoordinatesOf(string $address): void {
		$this->results = array();
		$this->results['apis'] = array();
		foreach($this->mapCoordinatesAPIContainer->getAPIs() as $mapCoordinatesAPI) {
			$result = $mapCoordinatesAPI->coordinatesGetRequest($address);
				array_push($this->results['apis'], $mapCoordinatesAPI->getName());
				$this->results[$mapCoordinatesAPI->getName()] = $result;
		}
	}

	/**
     * Returns the result of all API get coordiantes calls.
     *
     * @return array
     */
	function getResults(): ?array {
		return $this->results;
	}
}	
?>