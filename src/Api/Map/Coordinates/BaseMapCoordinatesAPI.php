<?php
declare(strict_types=1);

namespace Api\Map\Coordinates;

use Model\MapCoordinates;
use Util\Parser\MapCoordinatesAPIResponseParser;
use Util\ApiRequester;

/**
 * @implements MapCoordinatesAPI
 */
class BaseMapCoordinatesAPI implements MapCoordinatesAPI {
    private static string $ADDRESS_PARAM_VALUE_PLACEHOLDER = '{address_to_search}';
    /**
     * API Name.
     *
     * @var string
     */
    private string $name;

    /**
     * API coordinates URL.
     *
     * @var string
     */
    private string $coordinatesGetUrl;

    /**
     * API Parameters.
     *
     * @var string
     */
    private array $params;
 
     /**
     * API request options
     *
     * @var array
     */
    private ?array $requestOpts = null;
    
    /**
     * The result parser of the API get coordiantes call.
     *
     * @var MapCoordinatesAPIResponseParser
     */
    private MapCoordinatesAPIResponseParser $parser;

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->ensureIsValidConfig($config);

        $this->name = $config['name'];
        $this->coordinatesGetUrl = $config['coordinates_get_url'];
        $this->params = $config['params'];        
        if(array_key_exists('request_opts', $config)) {
            $this->requestOpts = $config['request_opts']; 
        }
    }

    /**
     * Checks if given config is invalid throws InvalidArgumentException
     *
     * @throws InvalidArgumentException
     * @return void
     */
    private function ensureIsValidConfig(array $config): void {
        if(!array_key_exists('name', $config) 
            || !array_key_exists('coordinates_get_url', $config) 
            || !array_key_exists('params', $config)){
            
            throw new \InvalidArgumentException(
                sprintf('Invalid config. Unable to create BaseMapCoordinatesAPI instance.')
            );
        }
    }

    /**
     * Makes a get request to given url with available request options
     *
     * @return array
     */
    public function makeGetRequest(string $url): ?array {
        return ApiRequester::get($url, $this->requestOpts);
    }

    /**
     * Adds requested address to the API url params
     *
     * Returns the API url params with filled address.
     *
     * @return array
     */
    private function getRequestParamsWithAddress(string $address): array {
        $params = [];
        foreach ($this->params as $key => $value) {
            $params[$key] = str_replace(BaseMapCoordinatesAPI::$ADDRESS_PARAM_VALUE_PLACEHOLDER, $address, $value);
        }

        return $params;
    }

    /**
     * @inherit
     * {@inherit}
     * {@inheritdoc}
     */
    public function setParser(MapCoordinatesAPIResponseParser $parser): void {
        $this->parser = $parser;
    }

    /**
     * @inherit
     * {@inherit}
     * {@inheritdoc}
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @inherit
     * {@inherit}
     * {@inheritdoc}
     */
    public function generateGetCoordinatesRequestUrl(string $address): string {
        $params = $this->getRequestParamsWithAddress($address);
        $url = $this->coordinatesGetUrl.(count($params)>0 ? "?":"").http_build_query($params);
        
        return $url;
    }

    /**
     * @inherit
     * {@inherit}
     * {@inheritdoc}
     */
    public function coordinatesGetRequest(string $address): ?MapCoordinates {
        if(!isset($this->parser)){
            return null;
        }
        
        $url = $this->generateGetCoordinatesRequestUrl($address);
        $response = $this->makeGetRequest($url);

        return $this->parser->parseCoordinatesGetResponse($response);
    }
}
?>