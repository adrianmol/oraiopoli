<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {


		$data = $this->GetProducts();

		echo "<pre>";
			print_r($data);
		echo "</pre>";


	}




	function GetProducts(){

		$ProductData = $this->GetDataURL('GetProducts');
		$i=0;

		foreach($this->GetDataURL('GetProducts')->StoreDetails as $product){
			
			$data[$i]["id"]     	=(string) $product->ItemId;
			$data[$i]["code"]		=(string) $product->ItemCode;
			$data[$i]["title"]		=(string) $product->ItemDescr;
			$data[$i]["weight"]		=(string) $product->ItemWeight;
			$data[$i]["itemStock"]	=(string) $product->ItemStock;
			$data[$i]["category"]		=(string) $product->ItemGroup1;
			$data[$i]["category_1"]		=(string) $product->ItemGroup2;
			$data[$i]["category_2"]		=(string) $product->ItemGroup3;
			$data[$i]["category_3"]		=(string) $product->ItemGroup4;
			$data[$i]["manufacturer"]	=(string) $product->ItemManufacturer;
			$data[$i]["manufacturer_id"]	=(string) $product->ItemManufacturerId;
			$data[$i]["price_wholesale"]	=(string) $product->ItemWholesale;
			$data[$i]["price_vat"]			=(string) $product->ItemRetailVat;

			$i++;



			$data_query=$this->db->query("SELECT * FROM product");


		}
		return  $data_query;

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