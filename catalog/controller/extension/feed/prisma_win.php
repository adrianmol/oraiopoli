<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

		GetProducts();

	}



	private function GetProducts {

		$url = "http://ecommercews.megasoft.gr/eCommerceWebService.asmx/GetProducts";


			$xml = @simplexml_load_file($url);
			print_r($http_response_header);


	}

	
}




