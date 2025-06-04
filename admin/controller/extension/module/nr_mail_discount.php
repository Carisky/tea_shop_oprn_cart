<?php
class ControllerExtensionModuleNrMailDiscount extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->model('setting/setting');

		$this->load->language('extension/module/nr_mail_discount');
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_nr_mail_discount', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			$this->response->redirect($this->url->link('extension/module/nr_mail_discount', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/nr_mail_discount', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		if (!empty($this->error)) {
			foreach($this->error as $key => $error) {
				$data['error_'.$key] = $error;
			}
		}

		$data['action'] = $this->url->link('extension/module/nr_mail_discount', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_nr_mail_discount_status'])) {
			$data['status'] = $this->request->post['module_nr_mail_discount_status'];
		} else {
			$data['status'] = $this->config->get('module_nr_mail_discount_status');
		}
		
		if (isset($this->request->post['module_nr_mail_discount_coupon'])) {
			$data['coupon'] = $this->request->post['module_nr_mail_discount_coupon'];
		} else {
			$data['coupon'] = $this->config->get('module_nr_mail_discount_coupon');
		}
		
		if (isset($this->request->post['module_nr_mail_discount_desc'])) {
			$data['desc'] = $this->request->post['module_nr_mail_discount_desc'];
		} else {
			$data['desc'] = $this->config->get('module_nr_mail_discount_desc');
		}
		
		$this->load->model('marketing/coupon');
		$data['coupons'] = $this->model_marketing_coupon->getActiveCoupons();
		if(!empty($data['coupons'])) {
			foreach($data['coupons'] as &$coupon) {
				$coupon['discount'] = (float)$coupon['discount'];
			}
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/nr_mail_discount', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/nr_mail_discount')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if(!isset($this->request->post['module_nr_mail_discount_coupon'])) {
			$this->error['coupon'] = $this->language->get('error_choose');
		}

		return !$this->error;
	}
}