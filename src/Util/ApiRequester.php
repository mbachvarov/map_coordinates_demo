<?php
declare(strict_types=1);

namespace Util;

class ApiRequester {
    /**
     * Executes get request to given URL and returns the response as array
     *  
     * If exception occures, null is returned
     *
     * @param string $url The URL to call
     * @param array $requestOptions Specific request options(nullable)
     * @return array
     */
    static function get(string $url, array $requestOptions = null): ?array {
    	try{
            if($requestOptions == null) {
                return json_decode(file_get_contents($url), true);
            }

    		return json_decode(file_get_contents($url, false, stream_context_create($requestOptions)), true);	
    	} catch(\TypeError $e) {
    		return null;
    	}
    }
}
?>