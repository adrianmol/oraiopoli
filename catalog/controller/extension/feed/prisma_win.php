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
			
			$data["id"]    =(string) $product->ItemId;
			$data["id"]["code"]		=(string) $product->ItemCode;
			$data["id"]["title"]		=(string) $product->ItemDescr;
			$data["id"]["weight"]		=(string) $product->ItemWeight;
			$data["id"]["itemStock"]	=(string) $product->ItemStock;
			$data["id"]["category"]		=(string) $product->ItemGroup1;
			$data["id"]["category_1"]		=(string) $product->ItemGroup2;
			$data["id"]["category_2"]		=(string) $product->ItemGroup3;
			$data["id"]["category_3"]		=(string) $product->ItemGroup4;
			$data["id"]["manufacturer"]	=(string) $product->ItemManufacturer;
			$data["id"]["manufacturer_id"]	=(string) $product->ItemManufacturerId;
			$data["id"]["price_wholesale"]	=(string) $product->ItemWholesale;
			$data["id"]["price_vat"]			=(string) $product->ItemRetailVat;

			$i++;
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
		
		return $xml;
	

	}



}