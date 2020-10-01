<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

		
		print_r($this->GetProducts());



	}




	function GetProducts(){

		$ProductData = $this->GetDataURL('GetProducts');

		foreach($ProductData->StoreDetails as $product){

			$data["id"]     = $product->ItemId;
			$data["code"]	= $product->ItemCode;
			$data["title"]	= $product->ItemDescr;
			$data["weight"]	= $product->ItemWeight;



		}
		return $data;

	}



	function GetDataURL($path) {

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/'. $path;
		$data = 'SiteKey=bs-gg183-352&Date=1-9-2020&StorageCode=000';
		
		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => $data
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { /* Handle error */ }
		
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		
		return ($xml);
	

	}



}