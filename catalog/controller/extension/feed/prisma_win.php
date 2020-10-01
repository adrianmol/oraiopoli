<?php
class ControllerExtensionfeedPrismawin extends Controller {
	public function index() {

		$originalXML = "
		POST /eCommerceWebService.asmx HTTP/1.1
		Host: ecommercews.megasoft.gr
		Content-Type: text/xml; charset=utf-8
		Content-Length: length
		SOAPAction: 'http://tempuri.org/GetProducts'
		
		<?xml version='1.0' encoding='utf-8'?>
		<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/'>
		  <soap:Body>
			<GetProducts xmlns='http://tempuri.org/'>
			  <SiteKey></SiteKey>
			  <Date></Date>
			  <StorageCode>001</StorageCode>
			</GetProducts>
		  </soap:Body>
		</soap:Envelope>";
		


		$someURL = "http://ecommercews.megasoft.gr/eCommerceWebService.asmx?op=GetProducts";
		//Translate the XML above in a array, like PHP SOAP function requires
		$myParams = array('firstClient' => array('SiteKey' => 'bs-gg183-352',
										  'Date' => '1/9/2020'),
										  'StorageCode' => '001');
					
		$webService = new SoapClient($someURL);
		$result = $webService->someWebServiceFunction($myParams);

		print_r ($result);
		echo ($result);
	}





	
}