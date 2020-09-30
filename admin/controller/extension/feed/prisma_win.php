<?php
class ControllerExtensionfeedPrismawin extends Controller {
    
    private $error = array();

	public function index() {
		$this->document->setTitle('Prisma Win');
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/feed/prisma_win', $data));
	}
	
	public function install() {
	    
	    $this->load->model('setting/setting');
	    
	    $this->model_setting_setting->editSetting('prisma_win', ['prisma_win_status'=>1]);
	    
	}

	public function uninstall() {
	    
	     $this->load->model('setting/setting');
         $this->model_setting_setting->deleteSetting('prisma_win');
 
	}
	
	protected function validate() {
		return true;
	}
}


