<?php
class ControllerExtensionModuleNrShippingBanner extends Controller {
	public function index() 
	{
		$data['text'] = str_replace(PHP_EOL, '<br>', $this->config->get('module_nr_shipping_banner_text'));
		$data['href'] = $this->config->get('module_nr_shipping_banner_link');
		return $this->load->view('extension/module/nr_shipping_banner', $data);
	}
}