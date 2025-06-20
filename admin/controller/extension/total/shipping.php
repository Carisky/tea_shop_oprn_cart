<?php
class ControllerExtensionTotalShipping extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/shipping');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_shipping', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/shipping', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/total/shipping', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);

		if (isset($this->request->post['total_shipping_estimator'])) {
			$data['total_shipping_estimator'] = $this->request->post['total_shipping_estimator'];
		} else {
			$data['total_shipping_estimator'] = $this->config->get('total_shipping_estimator');
		}

		if (isset($this->request->post['total_shipping_status'])) {
			$data['total_shipping_status'] = $this->request->post['total_shipping_status'];
		} else {
			$data['total_shipping_status'] = $this->config->get('total_shipping_status');
		}

		if (isset($this->request->post['total_shipping_sort_order'])) {
			$data['total_shipping_sort_order'] = $this->request->post['total_shipping_sort_order'];
		} else {
			$data['total_shipping_sort_order'] = $this->config->get('total_shipping_sort_order');
		}
		
		if (isset($this->request->post['total_shipping_free_sum'])) { //Noir
			$data['total_shipping_free_sum'] = $this->request->post['total_shipping_free_sum'];
		} else {
			$data['total_shipping_free_sum'] = $this->config->get('total_shipping_free_sum');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/shipping', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/shipping')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}