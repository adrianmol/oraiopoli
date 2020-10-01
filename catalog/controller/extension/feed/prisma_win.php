<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {


		$GetProducts = $this->GetDataURL('GetProducts');

		
		foreach($GetProducts->StoreDetails as $product){

			$data["id"] = $product->ItemId;
			echo "<br>";
			echo ($data["id"]);

		}
		


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