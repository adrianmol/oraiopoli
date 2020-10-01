<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

		echo "<pre>";
		print_r($this->GetProducts());
		echo "</pre>";


	}




	function GetProducts(){

		$ProductData = $this->GetDataURL('GetProducts');
		$i=0;

		foreach($this->GetDataURL('GetProducts')->StoreDetails as $product){
			
			$data[$i]["id"]     =(string) $product->ItemId;
			$data[$i]["code"]	=(string)$product->ItemCode;
			$data[$i]["title"]	=(string) $product->ItemDescr;
			$data[$i]["weight"]	=(string) $product->ItemWeight;


			$i++;
		}
		return ($data);

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