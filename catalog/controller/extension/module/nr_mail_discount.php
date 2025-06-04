<?php
class ControllerExtensionModuleNrMailDiscount extends Controller {
	
	public function index() 
	{
		$this->load->model('extension/module/nr_mail_discount');
		
		if(!$this->config->get('module_nr_mail_discount_status')) return;
		if(!$this->config->get('module_nr_mail_discount_coupon')) return;
		
		$data['coupon'] = $this->model_extension_module_nr_mail_discount->getCoupon($this->config->get('module_nr_mail_discount_coupon'));
		if(!$data['coupon']) return;
		
		$data['discount'] = (float)$data['coupon']['discount'].($data['coupon']['type'] == 'P' ? '%' : ' '.$this->session->data['currency']);
		
		$data['text'] = html_entity_decode(sprintf($this->config->get('module_nr_mail_discount_desc'), $data['discount']));
		
		return $this->load->view('extension/module/nr_mail_discount', $data);
	}
	
	public function send() 
	{
		$this->load->language('extension/module/nr_mail_discount');
		$email = $this->request->post['email'];
		if(!$email) $this->sendJSON(['error' => $this->language->get('error_noemail')]);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $this->sendJSON(['error' => $this->language->get('error_email')]);
		
		$this->load->model('extension/module/nr_mail_discount');
		$coupon = $this->model_extension_module_nr_mail_discount->getCoupon($this->config->get('module_nr_mail_discount_coupon'));
		$this->sendJSON(['coupon' => $coupon['code']]);
	}
	
	public function sendJSON($data)
	{
		header("Content-type: application/json");
		header('Content-Type: charset=utf-8');
		echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		exit();
	}
}