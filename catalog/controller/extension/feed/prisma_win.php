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
			
			$data[$product->ItemId]["id"]     	=(string) $product->ItemId;
			$data[$product->ItemId]["code"]		=(string) $product->ItemCode;
			$data[$product->ItemId]["title"]		=(string) $product->ItemDescr;
			$data[$product->ItemId]["weight"]		=(string) $product->ItemWeight;
			$data[$product->ItemId]["itemStock"]	=(string) $product->ItemStock;
			$data[$product->ItemId]["category"]		=(string) $product->ItemGroup1;
			$data[$product->ItemId]["category_1"]		=(string) $product->ItemGroup2;
			$data[$product->ItemId]["category_2"]		=(string) $product->ItemGroup3;
			$data[$product->ItemId]["category_3"]		=(string) $product->ItemGroup4;
			$data[$product->ItemId]["manufacturer"]	=(string) $product->ItemManufacturer;
			$data[$product->ItemId]["manufacturer_id"]	=(string) $product->ItemManufacturerId;
			$data[$product->ItemId]["price_wholesale"]	=(string) $product->ItemWholesale;
			$data[$product->ItemId]["price_vat"]			=(string) $product->ItemRetailVat;

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