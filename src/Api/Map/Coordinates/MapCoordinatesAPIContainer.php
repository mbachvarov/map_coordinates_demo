<?php 
declare(strict_types=1);

namespace Api\Map\Coordinates;

class MapCoordinatesAPIContainer {
    /**
     * Array with successfully initilized APIs for map coordinates search.
     *
     * @var array
     */
    private array $apis;


    /**
     * Class constructor.
     *
     */
    public function __construct() {
        $this->apis = array();
    }

    /**
     *
     * Adds an API to the container.
     *
     * Returns status whether the API is added in the container.
     *
     * @param MapCoordinatesAPI $api An API we want to add in the container.
     * @return void
     */
    public function add(MapCoordinatesAPI $api): void {
        array_push($this->apis, $api);
    }    

    /**
     * Returns array with all APIS which are currently added in the container
     *
     * @return array
     */
    public function getAPIs(): array {
        return $this->apis;
    }
}
?>