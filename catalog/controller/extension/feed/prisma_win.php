<?php

class ControllerExtensionfeedPrismawin extends Controller
{


	public function index()
	{
		$today = date("m-d-Y");

		#https://oraiomarket.gr/index.php?route=extension/feed/prisma_win


		#This link is for update products
		#https://oraiomarket.gr/index.php?route=extension/feed/prisma_win&data=products

		#This link is for update photo
		#https://oraiomarket.gr/index.php?route=extension/feed/prisma_win&data=photos





		if (isset($_GET["data"])) {

			$request =	$_GET["data"];
			switch ($request) {
				case "products":

					$this->GetDataURL('GetProducts', SiteKey_Megasoft, $today);
					$this->GetDataURL('GetItemsWithNoEshop', SiteKey_Megasoft, $today);
					$this->InsertProduct();
					$this->ItemsWithNoEshop();
					$this->GetManufacturer();


					break;
				case "photos":
					$this->InsertPhoto();
					break;

				case "options":

					$this->GetCustom();
					$this->managementCustomFields();
					break;
			}
		}

		//$data = $this->GetCustomers(SiteKey_Megasoft);



		// foreach($data->CustomerDetails as $customerData){
		// 	if(!empty($customerData->CustomerEmail)){
		// 	echo ("ID : {$customerData->CustomerId} Email: {$customerData->CustomerEmail} \n");
		// 	}
		// }


	}

	public function writelogs($msg, $file)
	{
		date_default_timezone_set('Europe/Athens');
		$log_filename = $_SERVER['DOCUMENT_ROOT'] . "/logs";
		if (!file_exists($log_filename)) {
			// create directory/folder
			mkdir($log_filename, 0777, true);
		}

		$getDate = date('d-M-Y');
		$log_file_data = "{$log_filename}/{$file}-{$getDate}.log";
		file_put_contents($log_file_data, date("h:i:s") . ":" . $msg . "\n", FILE_APPEND);
	}


	function CallXML($url)
	{
		try {
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
		} catch (Exception $e) {

			$error_msg = $e->getMessage();

			$this->writelogs($error_msg, 'error_call_xml');
		}

		print_r($xml);
		return $xml;
	}

	function GetProducts()
	{

		//$ProductData = $this->GetDataURL('GetProducts','10-20-2020');
		//$ProductData = curl("https://oraiomarket.gr/prisma_win/products.xml") or die("<br>Error: Cannot open XML (Products)</br>");
		$ProductData = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/products.xml") or die("<br>Error: Cannot open XML (Products)</br>");

		$ID = 0;

		foreach ($ProductData->StoreDetails as $product) {

			if ($product->ItemStock <= 0) $product->ItemStock = 0;
			$ID = (int)$productID = $product->ItemId;

			$data[$ID] = array(
				'id'				=> (string) $product->ItemId,
				'code'				=> (string) $product->ItemCode,
				'title'				=> (string) $product->ItemDescr,
				'weight'			=> (string) $product->ItemWeight,
				'itemStock'			=> (string) $product->ItemStock,
				'category'			=> (string) $product->ItemGroup1,
				'category_1'		=> (string) $product->ItemGroup2,
				'category_2'		=> (string) $product->ItemGroup3,
				'category_3'		=> (string) $product->ItemGroup4,
				'manufacturer'		=> (string) $product->ItemManufacturer,
				'manufacturer_id'	=> (string) $product->ItemManufacturerId,
				'price_wholesale'	=> (string) $product->ItemWholesale,
				'price_vat'			=> (string) $product->ItemRetailVat,
				'mudescr'			=> (string) $product->ItemMUDescr,
				'datacreated'		=> (string) $product->ItemDateCreated,
				'datamodified'		=> (string) $product->ItemDateModified
			);



			$category[$ID] = array();

			$category[$ID]["productID"] = (string)$product->ItemId;

			$category[$ID]["top_category"] = (string)$product->ItemGroup1;


			if (!empty((string) $product->ItemGroup2)) {

				$category[$ID]["level1"] = (string)$product->ItemGroup2;
			}

			if (!empty((string) $product->ItemGroup3)) {

				$category[$ID]["level2"] = (string)$product->ItemGroup3;
			}

			if (!empty((string) $product->ItemGroup4)) {

				$category[$ID]["level3"] = (string)$product->ItemGroup4;
			}
		}

		return array($data, $category);
	}


	function GetCategory()
	{

		$data = $this->GetProducts();
		$i = 0;
		$catID = 0;
		$parent_level = 0;

		foreach ($data[1] as $category) {


			// echo "Main Category: ".end($category) . " Parent Category: " .prev($category). " Number: " . count($category);
			// echo "</br>";

			$main_category = end($category);
			$parent_category = prev($category);

			$productID = $category['productID'];

			$my_category = $this->db->query("select  s.product_id, cp.path_id, cp.`level`,z.`name` from oc_product s 
			LEFT JOIN oc_product_description d ON(d.product_id= s.product_id and d.language_id=2)
			LEFT JOIN oc_product_to_category r ON(r.product_id= s.product_id)
			LEFT JOIN oc_category f ON(f.category_id= r.category_id)
			LEFT JOIN oc_category_path cp ON(cp.category_id = f.category_id)
			LEFT JOIN oc_category_description z ON(z.category_id= cp.path_id and z.language_id=2 )
			where s.product_id={$productID} order by cp.path_id");

			// $my_category = $my_category->rows;

			$category_field = $this->db->query("SELECT cd.name AS top_category ,c.category_id, c.parent_id ,q.name AS parent_category FROM oc_category_description cd
			LEFT JOIN oc_category c ON (cd.category_id = c.category_id)
			LEFT JOIN oc_category w ON (w.category_id = c.category_id)
			LEFT JOIN oc_category_description q ON( q.category_id = w.parent_id)
			WHERE  cd.name = '{$main_category}' AND q.name = '{$parent_category}'");

			$category_field = $category_field->rows;
			//$categoryID = $category_field['category_id'];



			//$insert_product_to_category = $this->db->query("INSERT INTO ". DB_PREFIX ."product_to_category (category_id,product_id) VALUES ({$category_field[0]['category_id']},{$productID})");

			// if($insert_product_to_category){
			// 	echo ("Status = {$insert_product_to_category} ProductID : {$productID} CategoryID :{$category_field[0]['category_id']} </br>");
			// }

			$CategoryPath[$productID] =  array(

				'productID'   => $productID,
				'categoryID'  => (int)$category_field[0]['category_id'],
				'main_category' => $main_category,
				'parent_category' => $parent_category

			);
		}

		// echo "<pre>";
		// print_r($CategoryPath);
		// echo "</pre>";

		return $CategoryPath;
	}

	function InsertPhoto()
	{

		$output = "";
		$products   = $this->GetProducts();
		$products = $products[0];
		$numItems = count($products);
		$i = 0;
		$num_request = 0;
		// echo ($numItems);
		foreach ($products as $product) {

			if ($i != $numItems) {
				$i++;
				if ($i < 100) {
					$output .= ('{ "storecode": "' . $product['code'] . '" },');
				}
				// if (($i % 30) == 0) {

				// 	$output .= ('{ "storecode": "' . $product['code'] . '" }');
				// 	$photo = $this->GetPhotoPath($output, $i);
				// 	$output = "";
				// } else {
				// 	$output .= ('{ "storecode": "' . $product['code'] . '" },');
				// }
			} else {

				$output .= ('{ "storecode": "' . $product['code'] . '" }');
			}
		}
		$photo = $this->GetPhotoPath($output, $i);
		$photo[$product['id']]['itemtype'] = ($photo[$product['id']]['itemtype'] ? $photo[$product['id']]['itemtype'] : "JPG");
		$pathPhoto[] = ("catalog/products/" . $product['code'] . "." . $photo[$product['id']]['itemtype']);
	}

	function ItemsWithNoEshop()
	{
		$product_no_eshop = 0;
		try {

			$ProductData = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/productsNoEshop.xml"); //or die("<br>Error: Cannot open XML (Products No Eshop)</br>");
			foreach ($ProductData->StoreItemsNoEshop as $product) {

				$product_id = $product->storeid;
				$exits_product = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE product_id = '{$product_id}' ");
				$exits_product = $exits_product->rows;
				if (count($exits_product) != 0) {

					$this->db->query("UPDATE " . DB_PREFIX . "product SET 
				status = 0 WHERE product_id = {$product_id}");
					$product_no_eshop++;
				}
			}
		} catch (Exception $e) {

			$error_msg = $e->getMessage();
			$this->writelogs($error_msg, 'error_open_xml-no-eshop');
		}

		$msg = "Total disable: {$product_no_eshop} product(s)";

		$this->writelogs($msg, "ItemsWithNoEshop");
		return $product_no_eshop;
	}


	function InsertProduct()
	{

		$products   = $this->GetProducts();
		$products   = $products[0];
		$categories = $this->GetCategory();

		$status = 1;
		$tax_class  = 0;
		$language_id = 2;
		$storeid = 0;
		$minimum = 1.00;
		$itemOutStock = 5;
		$itemsUpdate = 0;
		$itemsAdded = 0;
		foreach ($products as $product) {

			if ($product['itemStock']) {
				$StockStatus = 7;
			} else {
				$StockStatus = 5;
			}

			if ($product['mudescr'] == 'Κιλά') {
				$minimum = 0.20;
			} else {
				$minimum = 1.00;
			}


			//$this->GetPhotoPath();
			$pathPhoto = ("catalog/products/" . $product['code'] . ".JPG");

			$sec = strtotime($product['datacreated']);
			$newdatacreated = date("Y-m-d H:i", $sec);

			$sec = strtotime($product['datamodified']);
			$newdatamodified = date("Y-m-d H:i", $sec);


			$productID = (int)$product['id'];
			$productMODEL = (int)$product['code'];
			$prodcutItemStock = (float)$product['itemStock'];
			$productPriceVat = $product['price_vat'];
			$productManufacturer = (int)$product['manufacturer_id'];
			$productName = $this->db->escape($product['title']);
			$productMetaTitle = $this->db->escape($product['title']);
			$productCategoryID = (int)$categories[$product['id']]['categoryID'];


			$exits_item = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE product_id = {$productID} ");
			$exits_item = $exits_item->rows;


			if (empty($exits_item)) {
				echo "ID: " . $productID . "</br>";
				$added_product_id = $productID;


				$insertproduct = $this->db->query("INSERT INTO " . DB_PREFIX . "product 
			       (`product_id`,`model`,`quantity`,`stock_status_id`,`in_stock_status_id`,`image`,`shipping`,`price`,`tax_class_id`,`manufacturer_id`,`status`,`minimum`,`date_added`,`date_modified`) 
			VALUES ({$productID},{$productMODEL},{$prodcutItemStock},{$itemOutStock},{$StockStatus},'{$pathPhoto}',{$status},{$productPriceVat},{$tax_class},{$productManufacturer},{$status},{$minimum},'{$newdatacreated}','{$newdatamodified}')");



				$insertproduct = $this->db->query("INSERT INTO " . DB_PREFIX . "product_description (`product_id`,`language_id`,`name`, `meta_title` ) 
																					   VALUES ({$productID},{$language_id},'{$productName}','{$productMetaTitle}') ON DUPLICATE KEY UPDATE `name`='{$productName}',`meta_title`='{$productMetaTitle}'");


				$insertproduct = $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store (product_id,store_id ) 
																					   VALUES ({$productID},{$storeid}) ON DUPLICATE KEY UPDATE `store_id` = {$storeid}");


				$insertproduct = $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id,category_id ) 
																					   VALUES ({$productID},{$productCategoryID}) ON DUPLICATE KEY UPDATE `product_id`={$productID},`category_id`= {$productCategoryID}");


				$itemsAdded++;
			} else {

				$this->db->query("UPDATE " . DB_PREFIX . "product SET 

					product_id = '{$productID}' ,
					quantity ={$prodcutItemStock},
					stock_status_id = {$itemOutStock},
					in_stock_status_id = {$StockStatus},
					price = {$productPriceVat},
					manufacturer_id = {$productManufacturer},
					date_modified ='{$newdatamodified}'
					
					WHERE product_id = {$productID}
					");


				$this->db->query("UPDATE " . DB_PREFIX . "product_description SET name = '{$productMetaTitle}', meta_title = '{$productMetaTitle}' WHERE product_id = {$productID}");

				$itemsUpdate++;
			}
		}
		date_default_timezone_set('Europe/Athens');

		if ($this->ItemsWithNoEshop()) {
			$product_no_eshop = $this->ItemsWithNoEshop();
		} else {
			$product_no_eshop = 0;
		}
		$today = date("Y-m-d H:i:s");
		//$today = date("Y-m-d H:i:s",strtotime('+2 hour',strtotime($GMTtoday)));
		$this->db->query("INSERT INTO " . DB_PREFIX . "prisma_win SET
			products_updated = {$itemsUpdate},
			products_deleted = {$product_no_eshop},
			products_added = {$itemsAdded},
			date_added = '{$today}'
		");

		$msg = "";
		$msg .= "Updated : {$itemsUpdate} product(s) \n";
		$msg .= "Deleted : {$product_no_eshop} product(s) \n";
		$msg .= "Added : {$itemsAdded} product(s) \n";
		$msg .= "Date : {$today} \n";

		//echo $msg;

		$this->writelogs($msg, "productUpdated");
	}



	function GetManufacturer()
	{

		$manufacturers = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/manufacturer.xml") or die("<br>Error: Cannot open XML (manufacturer)</br>");
		//$manufacturers = $manufacturers->ManufacturerDetails;

		foreach ($manufacturers->ManufacturerDetails as $manufacturer) {
			$msg = "";
			$mymanufid = $manufacturer->ManufacturerID;
			$mymanufName = $manufacturer->ManufacturerName;

			//echo ("SELECT manufacturer_id FROM ". DB_PREFIX ."manufacturer WHERE manufacturer_id ={$mymanufid}</br>");
			$manufacturersDB = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id ={$mymanufid}");
			$manufacturerDB = $manufacturersDB->rows;

			if ((count($manufacturerDB) == 0) && !empty($manuf)) {
				$insertmanufacturer = $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer (manufacturer_id,name) VALUES ($mymanufid,'$this->db->escape($mymanufName')");
				$msg .= "Manufacturer:{$mymanufName} ID = {$mymanufid}";
				$this->writelogs($msg, "GetManufacturer");
			}
		}
	}



	function GetItemsPhoto()
	{
		$ProductData = $this->GetDataURL('GetItemsPhotoInfo', 'bs-gg183-352', '1/1/2020');

		foreach ($ProductData->ItemsPhotoInfo as $ItemsPhoto) {

			(int)$productID = $ItemsPhoto->ItemCode;

			$data[(int)$productID] = array(
				'ItemCode'			=> (string) $ItemsPhoto->ItemCode,
				'ItemDesc'			=> (string) $ItemsPhoto->ItemDescription,
				'ItemPhotoName'		=> (string) $ItemsPhoto->ItemPhotoName,
				'PhotoPath'			=> (string) $ItemsPhoto->ItemPhotoPath,
			);
		}
		return  $data;
	}

	function GetDataUrlManufacturer($path, $sitekey)
	{
		$today = date('h-i-s_j-m-y');
		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/' . $path;
		$data = 'SiteKey=' . (string)$sitekey . '&StorageCode=000';

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
		if ($result === FALSE) { /* Handle error */
		}

		$xml = simplexml_load_string($result) or die("Error: Cannot create manufacturer xml");
		$xml->saveXML('/home/oraiomarket/public_html/prisma_win/manufacturer.xml');

		//return $xml;
	}



	function GetDataURL($path, $sitekey, $date)
	{
		$today = date('h-i-s_j-m-y');
		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/' . $path;
		$data = 'SiteKey=' . (string)$sitekey . '&Date=' . $date . '&StorageCode=000';

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
		try {
			if (!empty($result)) {
				if ($path == 'GetProducts') {
					$xml = simplexml_load_string($result); //or $this->writelogs("Error: Cannot create xml (Get Products)", "error_prisma_xml \n");						
					$xml->saveXML('/home/oraiomarket/public_html/prisma_win/products.xml');
				} else if ($path == 'GetItemsWithNoEshop') {
					$xml = simplexml_load_string($result); //or $this->writelogs("Error: Cannot create xml (No Eshop)", "error_prisma_xml \n");;	
					$xml->saveXML('/home/oraiomarket/public_html/prisma_win/productsNoEshop.xml');
				}
			}
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->writelogs("Error: Empty result", "error_prisma_xml");
		}
		return $xml;
	}

	function GetPhotoPath($ItemCode, $count)
	{

		$url = 'http://ecommercews.megasoft.gr/eCommerceWebService.asmx/UploadImageToFtp';
		$data = 'SiteKey=bs-gg183-352&JsonStrWeb={   "items": [ ' . $ItemCode . ' ]}';
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
		if ($result === FALSE) { /* Handle error */
		}
		$xml = simplexml_load_string($result) or die("Error: Cannot create image xml");
		$xml->saveXML('/home/oraiomarket/public_html/prisma_win/image_json' . $count . '.xml');
		foreach ($xml->ItemImageUpload as $photoInfo) {
			$photo[(int)$photoInfo->ItemCode] = array(

				'itemcode'  => (string)$photoInfo->ItemCode,
				'itemtype'  => (string)$photoInfo->ImageType
			);
		}

		$this->writelogs(implode(" , ", $photo[(int)$photoInfo->ItemCode]), "GetPhotoPath");
		return $photo;
	}


	function SendOrderJson()
	{

		$list_data_order = $this->db->query("SELECT DISTINCT  os.order_status_id, os.name,  o.email AS CustUsername , o.order_id AS OrderNo, 
		'000' AS StorageCode,o.payment_method AS TroposPliromis, 
		o.shipping_method AS TroposApostolhs, 
		'ΠΩΛΗΣΗ' AS SkoposDiak,'ΕΔΡΑΜΑΣ' AS ToposFortoshs, 
		o.`comment`AS Comments,'' AS Comments2, 
		'' AS Comments3, CONCAT (o.firstname,' ', o.lastname) AS CustName,
		'' AS CustAfm, o.shipping_city AS ShippingCity, o.shipping_address_1 AS ShippingAddress,
		o.shipping_postcode AS ShippingZip, o.shipping_city AS CustCity,
		o.payment_address_1 AS CustAddress, o.payment_postcode AS CustZip,
		o.email AS CustEmail, o.telephone AS CustMobile , o.telephone AS CustTel,
		'' AS CustTimok, o.fax AS CustFax, '' AS CustDOY, o.date_added AS ShippingDate,'' AS InvoiceCode
		FROM oc_order o	LEFT JOIN oc_order_status os ON ( os.order_status_id = o.order_status_id)
		WHERE os.`name` IS NOT NULL AND o.order_status_id=1");


		$data = $list_data_order->rows;
		//$orderID = $data['OrderNo'][0];




		$data = $list_data_order->rows;

		foreach ($data as $row) {
			$insertorder[] =  array(
				'CustUsername' => $row['CustUsername'],
				'OrderNo' => $row['OrderNo'],
				'StorageCode' => $row['StorageCode'],
				'TroposPliromis' => $row['TroposPliromis'],
				'TroposApostolhs' => $row['TroposApostolhs'],
				'SkoposDiak' => $row['SkoposDiak'],
				'ToposFortoshs' => $row['ToposFortoshs'],
				'Comments' => $row['Comments'],
				'Comments2' => $row['Comments2'],
				'Comments3' => $row['Comments3'],
				'CustName' => $row['CustName'],
				'CustAfm' => $row['CustAfm'],
				'ShippingCity' => $row['ShippingCity'],
				'ShippingAddress' => $row['ShippingAddress'],
				'ShippingZip' => $row['ShippingZip'],
				'CustCity' => $row['CustCity'],
				'CustAddress' => $row['CustAddress'],
				'CustZip' => $row['CustZip'],
				'CustEmail' => $row['CustEmail'],
				'CustMobile' => $row['CustMobile'],
				'CustTel' => $row['CustTel'],
				'CustTimok' => $row['CustTimok'],
				'CustFax' => $row['CustFax'],
				'CustDOY' => $row['CustDOY'],
				'ShippingDate' => $row['ShippingDate'],
				'InvoiceCode' => $row['InvoiceCode']

			);


			$listProducts = $this->GetOrderProductLists($row['OrderNo']);
			foreach ($listProducts as $item) {
				$insertorder['items'] =  array(
					'storecode' => $item['storecode'],
					'pricefpa' => $item['pricefpa'],
					'qty' => $item['qty'],
					'itemcomment' => $item['itemcomment'],
					'discount' => $item['discount'],
					'tax' => $item['tax']

				);
			}
		}
		//'items'[] =$this->jsonRemoveUnicodeSequences($this->GetOrderProductLists($row['OrderNo']))

		//array_push($insertorder, array($this->GetOrderProductLists(10)));


		$list_store = $this->jsonRemoveUnicodeSequences($insertorder);

		echo "{\"Store\":\n {\"items\":" . $list_store . "}\n}";

		// echo "<pre>";
		// echo json_encode($this->jsonRemoveUnicodeSequences($list_data_order));
		// echo "</pre>";

	}

	function jsonRemoveUnicodeSequences($struct)
	{
		try {
			return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
				return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
			}, json_encode($struct));
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}



	function GetOrderProductLists($orderID)
	{

		$list_data_order_product = $this->db->query("SELECT  op.product_id AS storecode, op.price AS pricefpa,
		op.quantity AS qty, '' AS itemcomment, '' AS discount, '' AS tax
		 FROM oc_order_product op
		LEFT JOIN oc_order o ON (o.order_id = op.order_id)
		WHERE  op.order_id ={$orderID}");

		return $list_data_order_product->rows;
	}



	function GetCustomers($sitekey)
	{

		$url = 'https://ecommercews.megasoft.gr/eCommerceWebService.asmx/GetCustomers';
		$data = "SiteKey={$sitekey}&Date=1-1-2010";
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
		if ($result === FALSE) { /* Handle error */
		}
		$xml = simplexml_load_string($result) or die("Error: Cannot create image xml");
		$xml->saveXML('/home/oraiomarket/public_html/prisma_win/getCustomers.xml');

		return $xml;
	}




	function GetCustom()
	{
		$sitekey = "bs-gg183-352";
		$Date = date('m-d-Y');

		$url = "http://ecommercews.megasoft.gr/eCommerceWebService.asmx/GetCustomFields";
		$data = "SiteKey={$sitekey}&Date='{$Date}'&StorageCode=000";
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

		//echo $result;
		$count = 0;
		try {
			if (!empty($result)) {

				$xml = simplexml_load_string($result);
				foreach ($xml->CustomFields as $node) {
					if (isset($node->CustomField_4)) {
						$count++;
					}
				}
				if ($count > 0) {

					$xml->saveXML('/home/oraiomarket/public_html/prisma_win/getCustomFields.xml');
				}
			}
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->writelogs("Error: Empty result", "error_getCustomFields_xml");
		}
		return $xml;
	}

	function managementCustomFields()
	{

		$result = "";

		//$optionsData = $this->GetCustom();


		$optionsData = simplexml_load_file("/home/oraiomarket/public_html/prisma_win/getCustomFields.xml") or die("<br>Error: Cannot open XML (Products)</br>");

		$lastCount = $this->getLastProductOptionID();


		$sql_option = "INSERT INTO `" . DB_PREFIX . "product_option` (`product_option_id`, `product_id`, `option_id`, `value`, `required`) VALUES ";

		$sql_option_values_qy = "INSERT INTO `" . DB_PREFIX . "product_option_value` ( `product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix`, `points`, `points_prefix`, `weight`, `weight_prefix`) VALUES ";

		$data = array();
		$sql_values = array();
		$sql_options_values = array();

		$findme   = ',';

		try {

			foreach ($optionsData->CustomFields as $node) {
				//echo $node;
				if (isset($node->CustomField_4)) {

					$productID = (int)$node->ApoId;

					$quantity = (float)$this->getLastProductQuantity($productID);

					$getID = $this->db->query("SELECT p.product_id FROM `" . DB_PREFIX . "product_option` p WHERE p.product_id = {$productID}");

					if (empty($getID->row['product_id'])) {

						$sql_values[] = "({$lastCount}, {$productID}, 13, '', 1)";
					}

					$pos = strpos($node->CustomField_4, $findme);

					if ($pos == false) {
						$option_value_id = (int)$this->getLastProductOptionValueID($node->CustomField_4);

						$sql_options_values[] = "( {$lastCount}, {$productID}, 13, {$option_value_id}, {$quantity}, 1, 0.0000, '+', 0, '+', 0.00000000, '+')";
					} else {
						$args = explode(',', $node->CustomField_4);
						foreach ($args as $item) {

							$option_value_id = (int)$this->getLastProductOptionValueID($item);

							$sql_options_values[] = "( {$lastCount}, {$productID}, 13, {$option_value_id}, {$quantity}, 1, 0.0000, '+', 0, '+', 0.00000000, '+')";
						}
					}
				}

				$lastCount++;
			}

			$sql_option .= implode(',', $sql_values) . ";";

			$sql_option_values_qy .= implode(',', $sql_options_values) . ";";

			if (count($sql_values) > 0) {

				$this->db->query($sql_option);
			}

			if (count($sql_options_values) > 0) {

				$this->db->query($sql_option_values_qy);
			}

			//$result = $sql_option . "\n\n" . $sql_option_values_qy;
			$this->writelogs($sql_option . "\n\n" . $sql_option_values_qy, "getCustomQueries");
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->writelogs("Error: Empty result", "error_getCustomFields_xml");
		}

		//return  $result;
	}

	function getLastProductOptionID()
	{
		$query = $this->db->query("SELECT MAX(c.product_option_id) AS last_id FROM " . DB_PREFIX . "product_option c ");

		if ($query->row['last_id']) {

			$last_id = $query->row['last_id'] + 1;
		} else {

			$last_id = 1;
		}

		return $last_id;
	}


	function getLastProductOptionValueID($name)
	{
		$query = $this->db->query("SELECT z.option_value_id from " . DB_PREFIX . "option_value_description z WHERE z.language_id=2 and z.name like '%{$name}%'");
		return $query->row['option_value_id'];
	}


	function getLastProductQuantity($id)
	{
		$query = $this->db->query("SELECT z.quantity  from " . DB_PREFIX . "product z WHERE  z.product_id={$id}");
		return $query->row['quantity'];
	}
}