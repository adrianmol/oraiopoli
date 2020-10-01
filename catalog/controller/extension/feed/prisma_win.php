<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

			$url = "http://ecommercews.megasoft.gr/eCommerceWebService.asmx/GetProducts";
	
	
				$xml = @simplexml_load_file($url);
				echo "<pre>";
				print_r($http_response_header);
				echo "</pre>";
	}





	
}