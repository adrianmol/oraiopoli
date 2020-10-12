<?php
class ControllerExtensionfeedPrismawin extends Controller {


	

	public function index() {

		
#https://oraiomarket.gr/index.php?route=extension/feed/prisma_win


		// $data = $this->GetCategory();

		if(isset($_REQUEST["update"])) {
			echo "Updated";
		}





		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		// echo ($data[18]['ItemPhotoName']);

		// $data = $this->GetProducts();

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";

		//$data  = $this->GetCategory();
		//$this->GetPhotoPath('00028950');

		$this->InsertProduct();
		//sleep(60);
		// $this->InsertPhoto();

		// $this->GetManufacturer();



		// echo "<pre>";
		// print_r($data);
		// print_r($data1[0]);
		// echo "</pre>";

	}


	function GetProducts(){

		global $data;

		$ProductData = $this->GetDataURL('GetProducts','10-5-2020');
		$i=0;

		foreach($ProductData->StoreDetails as $product){
			
			if($product->ItemStock <= 0 ) $product->ItemStock = 0;
			

			(int)$productID = $product->ItemId;
			$data[(int)$productID] = array(
			'id'				=>(string) $product->ItemId,		
			'code'				=>(string) $product->ItemCode,
			'title'				=>(string) $product->ItemDescr,
			'weight'			=>(string) $product->ItemWeight,
			'itemStock'			=>(string) $product->ItemStock,
			'category'			=>(string) $product->ItemGroup1,
			'category_1'		=>(string) $product->ItemGroup2,
			'category_2'		=>(string) $product->ItemGroup3,
			'category_3'		=>(string) $product->ItemGroup4,
			'manufacturer'		=>(string) $product->ItemManufacturer,
			'manufacturer_id'	=>(string) $product->ItemManufacturerId,
			'price_wholesale'	=>(string) $product->ItemWholesale,
			'price_vat'			=>(string) $product->ItemRetailVat,
			'mudescr'			=>(string) $product->ItemMUDescr,
			'datacreated'		=>(string) $product->ItemDateCreated,
			'datamodified'		=>(string) $product->ItemDateModified,
			); 

			$category[$i] = array(
				'ID'     =>(string) $product->ItemId,
				'parent' =>(string) $product->ItemGroup1,
				'level1' =>(string) $product->ItemGroup2,
				'level2' =>(string) $product->ItemGroup3,
				'level3' =>(string) $product->ItemGroup4,
			);
			$i++;
		}

		return array ( $data , $category);

	}


	function GetCategory(){

		$data = $this->GetProducts();
		$i=0; $catID = 0;
		foreach($data[1] as $category){
		

			if($category['level3']){
				$level = 3;

			}else if ($category['level2']){
				$level = 2;

			}else if ($category['level1']){
				$level = 1;
			}

			$sql = $this->db->query("SELECT cd.category_id FROM ". DB_PREFIX ."category_description cd WHERE name = '" . $category['level'.$level.''] . "'" );

			$catID = $sql->rows[0];
			
			$CategoryPath[$category['ID']] =  array (

				'productID'   => $category['ID'],
				'cat1' 	      => $category['level'.$level.''],
				'level'       => $level,
				'categoryID'  => (int)$catID['category_id']
			);
	 
		}

		return $CategoryPath;

	}

	function InsertPhoto(){
		$i = 0;
		$products   = $this->GetProducts();
		$products = $products[0];
		foreach($products as $product){

		$filename = DIR_IMAGE."/catalog/products/".$product['code'].".JPG";
		if(file_exists($filename))	{	

		$this->GetPhotoPath($product['code']);
				echo ($product['code']);
				echo "</br>";
			$i++;
			}

			
		}

	}




	function InsertProduct(){


		$products   = $this->GetProducts();
		$products   = $products[0];
		$categories = $this->GetCategory();

		$status = 1; $tax_class  = 0; $language_id = 2; $storeid =0; $minimum = 1.00; $itemOutStock = 5;

		foreach($products as $product){

			
		// echo "<pre>";
		// print_r($product);
		// echo "</pre>";
		
		

		if($product['itemStock']){
			$StockStatus = 7;
		}else {
			$StockStatus = 5;
		}

		if($product['mudescr'] == 'Κιλά'){
			$minimum = 0.20;

		}


		
		$title = str_replace('\'', ' ', $product['title']);	


		// $this->GetPhotoPath($product['code']);
		$pathPhoto = ("catalog/products/".$product['code'].".JPG");

		$sec = strtotime($product['datacreated']);
		$newdatacreated = date("Y/m/d H:i",$sec);

		$sec = strtotime($product['datamodified']);
		$newdatamodified = date("Y/m/d H:i",$sec);

		

		$insertproduct = $this->db->query("INSERT INTO ". DB_PREFIX ."product SET 
							product_id = '".(int)$product['id']."' ,
							model = '".(int)$product['code']."',
							quantity ='".(float)$product['itemStock']."',
							stock_status_id = '".(int)$itemOutStock."',
							in_stock_status_id = '".(int)$StockStatus."',
							image ='". $pathPhoto ."',
							shipping = '".(int)$status."',
							price = '".$product['price_vat']."',
							tax_class_id = '".(int)$tax_class."',
							manufacturer_id = '".(int)$product['manufacturer_id']."',
							status = '".(int)$status."',
							minimum = '".(float)$minimum."',
							date_added ='". $newdatacreated ."',
							date_modified ='".$newdatamodified ."'

							ON DUPLICATE KEY UPDATE product_id = '".(int)$product['id']."', 
													price = '".$product['price_vat']."',
													quantity ='".(float)$product['itemStock']."',
													image ='". $pathPhoto ."',
													stock_status_id = '".(int)$StockStatus."'
							
							");	

		$insertproduct = $this->db->query("INSERT INTO ". DB_PREFIX ."product_description SET 
							product_id  = '".(int)$product['id']."',
							language_id = '".$language_id."',
							name = '".(string)$title . "',
							meta_title = '".(string)$title . "' 

							ON DUPLICATE KEY UPDATE product_id = '".(int)$product['id']."'
							");	

		$insertproduct = $this->db->query("INSERT INTO ". DB_PREFIX ."product_to_store SET 

							product_id = '".(int)$product['id']."' ,
							store_id = '".$storeid."'

							ON DUPLICATE KEY UPDATE product_id = '".(int)$product['id']."'
							");	



		$insertproduct = $this->db->query("INSERT INTO ". DB_PREFIX ."product_to_category  SET 

							product_id  = '".(int)$product['id']."' ,
							category_id = '".(int)$categories[$product['id']]['categoryID']."' 

							ON DUPLICATE KEY UPDATE product_id  = '".(int)$product['id']."', 
													category_id = '".(int)$categories[$product['id']]['categoryID']."'

							");	

						}
		echo ("Update : ".$insertproduct. " product(s) </br>");
	}

	function GetManufacturer(){
	
		$manufacturers = $this->GetProducts();
		$manufacturers = $manufacturers[0];

		foreach($manufacturers as $manufacturer){

		$manufacturersDB = $this->db->query("SELECT manufacturer_id FROM ". DB_PREFIX ."manufacturer WHERE manufacturer_id ='".(int)$manufacturer['manufacturer_id']."'");
		$manufacturerDB =$manufacturersDB->rows;
		$manuf = str_replace('\'', ' ', $manufacturer['manufacturer']);	

		if(empty($manufacturerDB) && !empty($manufacturer['manufacturer'])){ 
		
		$insertmanufacturer = $this->db->query("INSERT INTO ". DB_PREFIX ."manufacturer  SET 

				manufacturer_id  = '".(int)$manufacturer['manufacturer_id']."',
				name = '".(string)$manuf."',
				sort_order = 0 

				ON DUPLICATE KEY UPDATE manufacturer_id = '".(int)$manufacturer['manufacturer_id']."', 
										name = '".(string)$manuf."'

		");	
	

		}

	}
	
}



	function GetItemsPhoto(){
		$ProductData = $this->GetDataURL('GetItemsPhotoInfo','1/1/2020');

		foreach($ProductData->ItemsPhotoInfo as $ItemsPhoto){
			
			(int)$productID = $ItemsPhoto->ItemCode;

			$data[(int)$productID] = array(
			'ItemCode'			=>(string) $ItemsPhoto->ItemCode,
			'ItemDesc'			=>(string) $ItemsPhoto->ItemDescription,
			'ItemPhotoName'		=>(string) $ItemsPhoto->ItemPhotoName,
			'PhotoPath'			=>(string) $ItemsPhoto->ItemPhotoPath,
			);

		}

	return  $data;

	}


	function GetDataURL($path,$date) {

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/'. $path;
		$data = 'SiteKey=bs-gg183-352&Date='.$date.'&StorageCode=000';
		
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
		echo "<br>";
		$xml->saveXML(' /home/oraiomarket/public_html/products.xml');
		echo "<br>";
		//echo "<pre>";
		//print_r($xml);
		//echo "</pre>";
		return $xml;
	
	}

	function GetPhotoPath($ItemCode) {

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/UploadImageToFtp';
		$data = 'SiteKey=bs-gg183-352&JsonStrWeb={   "items": [ { "storecode": "'.$ItemCode.'"  }]}';
		
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
		//echo $result;
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		// echo "<pre>";
		// print_r($xml);
		// echo "</pre>";
		return $result;

	}

}