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
			
			$data[$product->ItemId] = array(
			$data["code"]				=(string) $product->ItemCode,
			$data["title"]				=(string) $product->ItemDescr,
			$data["weight"]				=(string) $product->ItemWeight,
			$data["itemStock"]			=(string) $product->ItemStock,
			$data["category"]			=(string) $product->ItemGroup1,
			$data["category_1"]			=(string) $product->ItemGroup2,
			$data["category_2"]			=(string) $product->ItemGroup3,
			$data["category_3"]			=(string) $product->ItemGroup4,
			$data["manufacturer"]		=(string) $product->ItemManufacturer,
			$data["manufacturer_id"]	=(string) $product->ItemManufacturerId,
			$data["price_wholesale"]	=(string) $product->ItemWholesale,
			$data["price_vat"]			=(string) $product->ItemRetailVat,
			);
			$i++;



			//$data_query=$this->db->query("SELECT * FROM product");
			//print_r($data);

		}
		return  $data;

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