<?php
class ControllerExtensionModuleNrShippingBanner extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/nr_shipping_banner');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') and $this->validate()) {
			$this->model_setting_setting->editSetting('module_nr_shipping_banner', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			$this->response->redirect($this->url->link('extension/module/nr_shipping_banner', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href' => $this->url->link('extension/module/nr_shipping_banner', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['action'] = $this->url->link('extension/module/nr_shipping_banner', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_nr_shipping_banner_status'])) {
			$data['module_nr_shipping_banner_status'] = $this->request->post['module_nr_shipping_banner_status'];
		} else {
			$data['module_nr_shipping_banner_status'] = $this->config->get('module_nr_shipping_banner_status');
		}
		
		if (isset($this->request->post['module_nr_shipping_banner_text'])) {
			$data['module_nr_shipping_banner_text'] = $this->request->post['module_nr_shipping_banner_text'];
		} else {
			$data['module_nr_shipping_banner_text'] = $this->config->get('module_nr_shipping_banner_text');
		}
		
		if (isset($this->request->post['module_nr_shipping_banner_link'])) {
			$data['module_nr_shipping_banner_link'] = $this->request->post['module_nr_shipping_banner_link'];
		} else {
			$data['module_nr_shipping_banner_link'] = $this->config->get('module_nr_shipping_banner_link');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/nr_shipping_banner', $data));
	}
		
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/nr_shipping_banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}