<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

		$url = 'ecommercews.megasoft.gr/eCommerceWebService.asmx/GetProducts';
		$data = 'SiteKey=bs-gg183-352&Date=1-9-2020&StorageCode=000';
		
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'host'    => "ecommercews.megasoft.gr",	
				'Content-Length: ' => "length",
				'method'  => 'POST',
				'content' => http_build_query($data)
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { /* Handle error */ }
		
		var_dump($result);
	}



}