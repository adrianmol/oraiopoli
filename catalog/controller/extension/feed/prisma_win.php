<?php
class ControllerExtensionfeedPrismawin extends Controller {


	public function index() {

		
#https://oraiomarket.gr/index.php?route=extension/feed/prisma_win


		if(isset($_REQUEST["update"])) {

			echo "Updated";
			$this->InsertProduct();
		}
		$today = date("m-d-Y");
		
		//$this->writelogs("Error","erros");
		//echo $_SERVER['DOCUMENT_ROOT'];
		
		//$this->GetDataURL('GetProducts','bs-gg183-352','10-30-2020');
		//$this->GetDataURL('GetItemsWithNoEshop','bs-gg183-352','10-27-2020');
	    //$this->GetDataUrlManufacturer('GetManufacturers','bs-gg183-352');

		//$this->InsertProduct();
		//$this->ItemsWithNoEshop();
		// $this->GetCategory();
		// $this->InsertPhoto();
		// $this->GetManufacturer();
		$this->GetProducts();	
	}

	public function writelogs($msg, $file) {
		date_default_timezone_set('Europe/Athens');
		$log_filename = $_SERVER['DOCUMENT_ROOT']."/logs";
		if (!file_exists($log_filename))
		{
			// create directory/folder
			mkdir($log_filename, 0777, true);
		}
	
		$getDate=date('d-M-Y');
		$log_file_data = "{$log_filename}/{$file}-{$getDate}.log";
		file_put_contents($log_file_data,date("h:i:s").":".$msg. "\n", FILE_APPEND);
	}
	

	function CallXML($url){
	try{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents

	$xmlsResponse = curl_exec($ch); // execute curl request
	echo $xmlsResponse;

	if (curl_errno($ch)) {
		$error_msg = curl_error($ch);
	}
	curl_close($ch);
	
		$xml = simplexml_load_string($xmlsResponse);


	}catch(Exception $e){

		$error_msg = $e->getMessage();

		$this->writelogs($error_msg, 'error_call_xml');
	}

	print_r($xml);
	return $xml;

	}

	function GetProducts(){

		//$ProductData = $this->GetDataURL('GetProducts','10-20-2020');
		//$ProductData = curl("https://oraiomarket.gr/prisma_win/products.xml") or die("<br>Error: Cannot open XML (Products)</br>");
		$ProductData = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/products.xml") or die("<br>Error: Cannot open XML (Products)</br>");
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

			$category[(int)$productID] = array(
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


			$sql = $this->db->query("SELECT cd.category_id FROM ". DB_PREFIX ."category_description cd ,". DB_PREFIX ."category c WHERE cd.name = '" . $category['level'.$level.''] . "'");
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
		$i = 0; $j=0;
		$products   = $this->GetProducts();
		$products = $products[0]; $numItems = count($products); $i=0;
		$output = "";
		// echo ($numItems);
		foreach($products as $product){

				if(++$i != $numItems){
					$output .= ('{ "storecode": "'.$product['code'].'" },');
				}else{
					$output .= ('{ "storecode": "'.$product['code'].'" }');	
				}

		}

		$photo = $this->GetPhotoPath($output);
		$photo[$product['id']]['itemtype'] = ($photo[$product['id']]['itemtype'] ? $photo[$product['id']]['itemtype'] : "JPG");
		$pathPhoto = ("catalog/products/".$product['code'].".".$photo[$product['id']]['itemtype']);

		echo ($pathPhoto);

	}

	function ItemsWithNoEshop(){


		$ProductData = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/productsNoEshop.xml") or die("<br>Error: Cannot open XML (Products No Eshop)</br>");
		$data = array(); $i = 0;
		
		foreach($ProductData->StoreItemsNoEshop as $product){
		$exits_item = $this->db->query("SELECT product_id FROM ". DB_PREFIX ."product WHERE product_id = '".(int)$product->storeid."' ");
		$exits_item = $exits_item->rows;
		if (empty($exits_item)){

			$this->db->query("UPDATE ".DB_PREFIX."product SET 
			status = 0 WHERE product_id = '".(int)$product->storeid."'");
				$i++;
			}
		}
		echo ("ProductNoEshop: ". $i."</br>");
	}


	function InsertProduct(){

		$products   = $this->GetProducts();
		$products   = $products[0];
		$categories = $this->GetCategory();

		$status = 1; $tax_class  = 0; $language_id = 2; $storeid =0; $minimum = 1.00; $itemOutStock = 5;
		$itemsUpdate = 0; $itemsAdded = 0;
		foreach($products as $product){

		if($product['itemStock']){
			$StockStatus = 7;
		}else {
			$StockStatus = 5;
		}

		if($product['mudescr'] == 'Κιλά'){
			$minimum = 0.20;

		}else{
			$minimum = 1.00;
		}


		//$this->GetPhotoPath();
		$pathPhoto = ("catalog/products/".$product['code'].".JPG");

		$sec = strtotime($product['datacreated']);
		$newdatacreated = date("Y/m/d H:i",$sec);

		$sec = strtotime($product['datamodified']);
		$newdatamodified = date("Y/m/d H:i",$sec);

		$exits_item = $this->db->query("SELECT product_id FROM ". DB_PREFIX ."product WHERE product_id = '".(int)$product['id']."' ");
		$exits_item = $exits_item->rows;
		if (empty($exits_item)){

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
									stock_status_id = '".(int)$StockStatus."',
									manufacturer_id = '".(int)$product['manufacturer_id']."',
									minimum = '".(float)$minimum."'
			");	


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
									stock_status_id = '".(int)$StockStatus."',
									manufacturer_id = '".(int)$product['manufacturer_id']."',
									minimum = '".(float)$minimum."'
			
			");	

			$insertproduct = $this->db->query("INSERT INTO ". DB_PREFIX ."product_description SET 
			product_id  = '".(int)$product['id']."',
			language_id = '".$language_id."',
			name = '".$this->db->escape($product['title']) . "',
			meta_title = '".$this->db->escape($product['title']) . "' 

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

		$itemsAdded++;

		}else{

			$this->db->query("UPDATE ".DB_PREFIX."product SET 

					product_id = '".(int)$product['id']."' ,
					quantity ='".(float)$product['itemStock']."',
					stock_status_id = '".(int)$itemOutStock."',
					in_stock_status_id = '".(int)$StockStatus."',
					price = '".$product['price_vat']."',
					manufacturer_id = '".(int)$product['manufacturer_id']."',
					date_modified ='".$newdatamodified ."'
					
					WHERE product_id = '".(int)$product['id']."'
					");
			

			$this->db->query("UPDATE ". DB_PREFIX ."product_description SET 

					name = '".$this->db->escape($product['title']) . "',
					meta_title = '".$this->db->escape($product['title']) . "' 

					WHERE product_id = '".(int)$product['id']."'
					");	

			$itemsUpdate++;
		}


		}
		$GMTtoday = date("Y-m-d H:i:s");
		$today = date("Y-m-d H:i:s",strtotime('+3 hour',strtotime($GMTtoday)));
		$this->db->query("INSERT INTO ".DB_PREFIX."prisma_win SET
			products_updated = '".$itemsUpdate."',
			products_added = '".$itemsAdded."',
			date_added = '".$today."'
		");				
		echo ("Update : ".$itemsUpdate. " product(s) </br>");
		echo ("Added : ".$itemsAdded. " product(s) </br>");
		echo ("Date : ".$today. "  </br>");
	}

	function GetManufacturer(){
	
		$manufacturers = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/manufacturer.xml") or die("<br>Error: Cannot open XML (manufacturer)</br>");
		//$manufacturers = $manufacturers->ManufacturerDetails;
		
		foreach($manufacturers->ManufacturerDetails as $manufacturer){

				$mymanufid = $manufacturer->ManufacturerID;
				$mymanufName = $manufacturer->ManufacturerName;
				// echo "<pre>";
				// echo ($mymanufid);
				// echo "<br>";
				// echo ($mymanufName);
				// echo "</pre>";	

			//echo ("SELECT manufacturer_id FROM ". DB_PREFIX ."manufacturer WHERE manufacturer_id ={$mymanufid}</br>");
			$manufacturersDB = $this->db->query("SELECT manufacturer_id FROM ". DB_PREFIX ."manufacturer WHERE manufacturer_id ={$mymanufid}");
			$manufacturerDB =$manufacturersDB->rows;

				if((count($manufacturerDB) == 0) && !empty($manuf)){ 
					$insertmanufacturer = $this->db->query("INSERT INTO ". DB_PREFIX ."manufacturer  SET 
							manufacturer_id  = {$mymanufid},
							name = '{$this->db->escape($mymanufName)}'");
					echo ("Manufacturer:{$mymanufName} ID = {$mymanufid}</br>");
				}
		}
	
}



	function GetItemsPhoto(){
		$ProductData = $this->GetDataURL('GetItemsPhotoInfo','bs-gg183-352','1/1/2020');

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

	function GetDataUrlManufacturer($path,$sitekey) {
		$today = date('h-i-s_j-m-y');
		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/'. $path;
		$data = 'SiteKey='.(string)$sitekey.'&StorageCode=000';
		
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

			$xml=simplexml_load_string($result) or die("Error: Cannot create manufacturer xml");	
			$xml->saveXML('/home/oraiomarket/public_html/prisma_win/manufacturer.xml');

			//return $xml;
	}



	function GetDataURL($path,$sitekey,$date) {
		$today = date('h-i-s_j-m-y');
		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/'. $path;
		$data = 'SiteKey='.(string)$sitekey.'&Date='.$date.'&StorageCode=000';
		
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

		//echo ("<br>{$result}</br>");
		try{
			if (!empty($result)) { 
				if($path == 'GetProducts'){
					$xml=simplexml_load_string($result) or $this->writelogs("Error: Cannot create xml (Get Products)", "error_prisma_xml \n");						
					$xml->saveXML('/home/oraiomarket/public_html/prisma_win/products.xml');
				}else if($path == 'GetItemsWithNoEshop'){
					$xml=simplexml_load_string($result) or $this->writelogs("Error: Cannot create xml (No Eshop)", "error_prisma_xml \n");;	
					$xml->saveXML('/home/oraiomarket/public_html/prisma_win/productsNoEshop.xml');
				}
			}
		}catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->writelogs("Error: Empty result", "error_prisma_xml");

		}
			return $xml;
	}

	function GetPhotoPath($ItemCode) {

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/UploadImageToFtp';
		$data = 'SiteKey=bs-gg183-352&JsonStrWeb={   "items": [ '.$ItemCode.' ]}';

		//use key 'http' even if you send the request to https://...
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
		$xml=simplexml_load_string($result) or die("Error: Cannot create image xml");

		foreach($xml->ItemImageUpload as $photoInfo){
			$photo[(int)$photoInfo->ItemCode] = array(

			'itemcode'  => (string)$photoInfo->ItemCode,
			'itemtype'  => (string)$photoInfo->ImageType
			);
		}
				
		return $photo;

	}

}