<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use Api\Map\Coordinates\BaseMapCoordinatesAPI;
use Util\Parser\GoogleMapsCoordinatesAPIParser;
use Util\ApiRequester;
use Model\MapCoordinates;

final class BaseMapCoordinatesAPITest extends TestCase {
    private static ?string $COORDINATES_GET_URL;
    private static ?array $VALID_CONFIG;
    private static ?array $INVALID_CONFIG;
    private static ?GoogleMapsCoordinatesAPIParser $PARSER;
    private static ?string $ADDRESS;


    public static function setUpBeforeClass(): void {
        self::$COORDINATES_GET_URL = 'http://test_maps_api.com';
        self::$VALID_CONFIG = [
            'name' => '',
            'coordinates_get_url' => BaseMapCoordinatesAPITest::$COORDINATES_GET_URL,
            'params' => [
                'address' => '{address_to_search}'
            ],
        ];
        self::$INVALID_CONFIG = [];
        self::$PARSER = new GoogleMapsCoordinatesAPIParser();
        self::$ADDRESS = "Sofia";
    }

    public static function tearDownAfterClass(): void {
        self::$COORDINATES_GET_URL = null;
        self::$VALID_CONFIG = null;
        self::$INVALID_CONFIG = null;
        self::$PARSER = null;
        self::$ADDRESS = null;
    }


    /**
    * Tested method BaseMapCoordinatesAPI::__construct
    *
    * Tests if BaseMapCoordinatesAPI class can be created from valid configuration file
    */
    public function testCanBeCreatedFromValidConfig(): void {
        $this->assertInstanceOf(
            BaseMapCoordinatesAPI::class,
            new BaseMapCoordinatesAPI(self::$VALID_CONFIG)
        );
    }


    /**
    * Tested method BaseMapCoordinatesAPI::__construct
    *
    * Tests if throwing InvalidArgumentException when tryong to create BaseMapCoordinatesAPI from invalid configuration file
    */
    public function testCannotBeCreatedFromInvalidConfig(): void {
        $this->expectException(InvalidArgumentException::class);

        new BaseMapCoordinatesAPI(self::$INVALID_CONFIG);
    }

    /**
    * Tested method BaseMapCoordinatesAPI::generateGetCoordinatesRequestUrl
    *
    * Tests if get coordination request url is properly generated
    */
    public function testGenerateGetCoordinatesRequestUrl(): void {
        $coordinatesAPI = new BaseMapCoordinatesAPI(self::$VALID_CONFIG);
       
        $this->assertEquals(
            self::$COORDINATES_GET_URL."?address=".self::$ADDRESS,
            $coordinatesAPI->generateGetCoordinatesRequestUrl(self::$ADDRESS)
        );
    }

    /**
    * Tested method BaseMapCoordinatesAPI::coordinatesGetRequest
    *
    * Tests if null is returned if parsing the result of the get coordinates request is unsuccessful
    */
    public function testCoordinatesGetRequestReturnsNull(): void {
        $baseMapCoordinatesAPIMock = $this->getMockBuilder(BaseMapCoordinatesAPI::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateGetCoordinatesRequestUrl', 'makeGetRequest'])  
            ->getMock();

        $mapsCoordinatesAPIParserMock = $this->createMock(GoogleMapsCoordinatesAPIParser::class);
        $mapsCoordinatesAPIParserMock->method('parseCoordinatesGetResponse')
            ->with(null)
            ->willReturn(null);
        $baseMapCoordinatesAPIMock->setParser($mapsCoordinatesAPIParserMock);

        $this->assertNull($baseMapCoordinatesAPIMock->coordinatesGetRequest(""));
    }

    /**
    * Tested method BaseMapCoordinatesAPI::coordinatesGetRequest
    *
    * Tests if null is returned if no parser is set
    */
    public function testCoordinatesGetRequestReturnsNullIfNoParser(): void {
        $baseMapCoordinatesAPIMock = $this->getMockBuilder(BaseMapCoordinatesAPI::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateGetCoordinatesRequestUrl', 'makeGetRequest'])  
            ->getMock();

        $this->assertNull($baseMapCoordinatesAPIMock->coordinatesGetRequest(""));
    }

    /**
    * Tested method BaseMapCoordinatesAPI::coordinatesGetRequest
    *
    * Tests if MapCoordinates is returned if parsing the result of the get coordinates request is successful
    */
    public function testCoordinatesGetRequestReturnsTrue(): void {
        $baseMapCoordinatesAPIMock = $this->getMockBuilder(BaseMapCoordinatesAPI::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateGetCoordinatesRequestUrl', 'makeGetRequest'])  
            ->getMock();

        $mapsCoordinatesAPIParserMock = $this->createMock(GoogleMapsCoordinatesAPIParser::class);
        $result = new MapCoordinates(10.2, 20.1);
        $mapsCoordinatesAPIParserMock->method('parseCoordinatesGetResponse')
            ->with(null)
            ->willReturn($result);        
        $baseMapCoordinatesAPIMock->setParser($mapsCoordinatesAPIParserMock);

        $this->assertEquals($result, $baseMapCoordinatesAPIMock->coordinatesGetRequest(""));
    }
}