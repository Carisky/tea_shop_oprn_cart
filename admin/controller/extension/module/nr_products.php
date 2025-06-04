<?php
class ControllerExtensionModuleNrProducts extends Controller {
	
	public function index() 
	{
		$this->load->language('extension/module/nr_products');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->load->model('setting/setting');
			$post = $this->request->post;
			if(isset($post['products'])) {
				$sort_order = array();
				foreach ($post['products'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
				array_multisort($sort_order, SORT_ASC, SORT_NUMERIC, $post['products']);
				$post['module_nr_products_products'] = $post['products'];
				unset($post['products']);
			}
			$this->model_setting_setting->editSetting('module_nr_products', $post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/nr_products', 'user_token=' . $this->session->data['user_token'], true));
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
			'href' => $this->url->link('extension/module/nr_products', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		if(!empty($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['action'] = $this->url->link('extension/module/nr_products', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['module_nr_products_status'])) {
			$data['status'] = $this->request->post['module_nr_products_status'];
		} else {
			$data['status'] = $this->config->get('module_nr_products_status');
		}
				
		if (isset($this->request->post['products'])) {
			$products = $this->request->post['products'];
		} else {
			$products = $this->config->get('module_nr_products_products');
		}
		if(!empty($products)) {
			$this->load->model('catalog/product');
			foreach($products as &$product) {
				if(!$product['product_id']) continue;
				$info = $this->model_catalog_product->getProduct($product['product_id']);
				$product['name'] = $info['name'];
			}
			$data['products'] = $products;
			$sort_order = array();
			foreach ($data['products'] as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
			array_multisort($sort_order, SORT_ASC, SORT_NUMERIC, $data['products']);
			
		} else {
			$data['products'] = array();
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('extension/module/nr_products', $data));
	}
	
}