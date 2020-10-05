<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {


#https://oraiopoli.gr/index.php?route=extension/feed/prisma_win


		// $data = $this->GetCategory();
		

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";
		// echo ($data[18]['ItemPhotoName']);

		// $data = $this->GetProducts();

		// echo "<pre>";
		// print_r($data);
		// echo "</pre>";

		$this->CreateCategory();

	}


	function GetProducts(){

		$ProductData = $this->GetDataURL('GetProducts');
		$i=0;

		foreach($ProductData->StoreDetails as $product){
			
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

			);
			$category[$i] = array(
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
		$i=0;
		foreach($data[1] as $category){
		

			if($category['level3']){
				$level = 3;

			}else if ($category['level2']){
				$level = 2;

			}else if ($category['level1']){
				$level = 1;
			}


			$CategoryPath[$i++] =  array (

				'parent' => $category['parent'],
				'cat1' 	 => $category['level1'],
				'cat2'   => $category['level2'],
				'cat3'   => $category['level3'],
				'level'  => $level
			);
	 
		}

		return $CategoryPath;
	}


	function CreateCategory(){
		$categories =$this->GetCategory();
		
		

		$sql = $this->db->query("SELECT * FROM ". DB_PREFIX ."category");
		
		
		echo "<pre>";
		print_r($sql);
		echo "</pre>";

	}



	function GetItemsPhoto(){
		$ProductData = $this->GetDataURL('GetItemsPhotoInfo');

		// echo "<pre>";
		// print_r($ProductData);
		// echo "</pre>";


		foreach($ProductData->ItemsPhotoInfo as $ItemsPhoto){
			
			(int)$productID = $ItemsPhoto->ItemCode;
			$data[(int)$productID] = array(
			'ItemCode'			=>(string) $ItemsPhoto->ItemCode,
			'ItemDesc'			=>(string) $ItemsPhoto->ItemDescription,
			'ItemPhotoName'		=>(string) $ItemsPhoto->ItemPhotoName,
			'PhotoPath'			=>(string) $ItemsPhoto->ItemPhotoPath,
			);

		}

		$url_to_image = "https://www.oraiomarket.gr/cache/sj_revo/6c9c1a290c8f891527ec9959b62773eb.jpeg";
		$my_save_dir = DIR_IMAGE ."products/" ;
		$filename = basename($data[18]['ItemPhotoName']);
		$complete_save_loc = $my_save_dir.$filename.".jpg";
		file_put_contents($complete_save_loc,file_get_contents($url_to_image));

		return  $data;

	}

	


	


	function GetDataURL($path) {

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/'. $path;
		$data = 'SiteKey=bs-gg183-352&Date=4-10-2020&StorageCode=000';
		
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
		echo $result;
		$xml=simplexml_load_string($result) or die("Error: Cannot create object");
		
		echo "<pre>";
		// print_r($xml);
		// echo "</pre>";
		return $result;

	}

}