<?php
declare(strict_types=1);

namespace Model;

class MapCoordinates implements \JsonSerializable{
    /**
     * Longitude
     *
     * @var float
     */
    private float $longitude; 

    /**
     * Latitude
     *
     * @var float
     */
    private float $latitude;

    /**
     * Class constructor.
     *
     */
    function __construct($latitude, $longitude) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Returns the longitude.
     *
     * @return float
     */
    function getLongitude(): float {
        return $this->longitude;
    }

    /**
     * Returns the latitude.
     *
     * @return float
     */
    function getLatitude(): float {
        return $this->latitude;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}

?>

