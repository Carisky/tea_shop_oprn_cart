<?php
class ControllerExtensionModuleNrAboutBanner extends Controller {
	public function index() 
	{
		$data['rows'] = $this->config->get('module_nr_about_banner_rows');
		return $this->load->view('extension/module/nr_about_banner', $data);
	}
}