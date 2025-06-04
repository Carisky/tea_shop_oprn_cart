<?php
class ControllerExtensionModuleNrAboutBanner extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/nr_about_banner');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') and $this->validate()) {
			$post = $this->request->post;
			if(!empty($post['module_nr_about_banner_rows'])) {
			$sort_order = array();
			foreach ($post['module_nr_about_banner_rows'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
			array_multisort($sort_order, SORT_ASC, SORT_NUMERIC, $post['module_nr_about_banner_rows']);
		}
			$this->model_setting_setting->editSetting('module_nr_about_banner', $post);
			$this->session->data['success'] = $this->language->get('text_success');
			//$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			$this->response->redirect($this->url->link('extension/module/nr_about_banner', 'user_token=' . $this->session->data['user_token'], true));
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
			'href' => $this->url->link('extension/module/nr_about_banner', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['action'] = $this->url->link('extension/module/nr_about_banner', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_nr_about_banner_status'])) {
			$data['module_nr_about_banner_status'] = $this->request->post['module_nr_about_banner_status'];
		} else {
			$data['module_nr_about_banner_status'] = $this->config->get('module_nr_about_banner_status');
		}
		
		if (isset($this->request->post['module_nr_about_banner_rows'])) {
			$data['module_nr_about_banner_rows'] = $this->request->post['module_nr_about_banner_rows'];
		} else {
			$data['module_nr_about_banner_rows'] = $this->config->get('module_nr_about_banner_rows');
		}
		if(!empty($data['module_nr_about_banner_rows'])) {
			$sort_order = array();
			foreach ($data['module_nr_about_banner_rows'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
			array_multisort($sort_order, SORT_ASC, SORT_NUMERIC, $data['module_nr_about_banner_rows']);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/nr_about_banner', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/nr_about_banner')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}