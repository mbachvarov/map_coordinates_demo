<?php
	return [
		'name' => 'osm_nominatim',
		'coordinates_get_url' => 'https://nominatim.openstreetmap.org/',
		'params' => [
			'format' => 'json',
			'addressdetails' => 1,
			'q' => '{{address_to_search}}',
			'format' => 'json',
			'limit' => 1
		],
		'request_opts' => [
			'http' => [
				'header'=>"User-Agent: StevesCleverAddressScript 3.7.6\r\n"
			]
		]
	];
?>