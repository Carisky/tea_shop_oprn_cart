<?php

	class ControllerExtensionModuleLoadFormatPagination extends Controller
	{
		private $error = array();

		public function index()
		{
			$this->load->language('extension/module/load_format_pagination');

			$this->document->addStyle('view/stylesheet/load-format-pagination.css');

			$this->document->setTitle($this->language->get('heading_title'));

			$this->load->model('setting/setting');

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$this->model_setting_setting->editSetting('load_format_pagination', $this->request->post);

				/*if (!isset($this->request->get['module_id'])) {
					$this->model_setting_module->addModule('load_format_pagination', $this->request->post);
				} else {
					$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
				}*/

				if (isset($this->request->post['save']) && strcasecmp($this->request->post['save'], 'stay') == 0) {
					$this->session->data['success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link('extension/module/load_format_pagination', 'user_token=' . $this->session->data['user_token'], 'SSL'));
				}

				$this->session->data['success'] = $this->language->get('text_success');


				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}


			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_edit'] = $this->language->get('text_edit');
			$data['text_enabled'] = $this->language->get('text_enabled');
			$data['text_disabled'] = $this->language->get('text_disabled');

			$data['entry_status'] = $this->language->get('entry_status');

			$data['button_stay'] = $this->language->get('button_stay');
			$data['button_save'] = $this->language->get('button_save');
			$data['button_cancel'] = $this->language->get('button_cancel');
			$data['entry_name'] = $this->language->get('entry_name');
			$data['entry_count'] = $this->language->get('entry_count');
			$data['error_name'] = $this->language->get('error_name');
			$data['error_count'] = $this->language->get('error_count');
			$data['input_backgroundcolor'] = $this->language->get('input_backgroundcolor');    
			$data['border_style'] = $this->language->get('border_style');    
			$data['input_borderwidth'] = $this->language->get('input_borderwidth');    
			$data['text_borderwidth'] = $this->language->get('text_borderwidth');    
			$data['input_borderround'] = $this->language->get('input_borderround');    
			$data['text_borderround'] = $this->language->get('text_borderround');    
			$data['input_bordercolor'] = $this->language->get('input_bordercolor');    
			$data['button_style'] = $this->language->get('button_style');    
			$data['input_buttoncolor'] = $this->language->get('input_buttoncolor');    
			$data['input_button_backgroundcolor'] = $this->language->get('input_button_backgroundcolor');    
			$data['hover_button_style'] = $this->language->get('hover_button_style');    
			$data['hover_input_buttoncolor'] = $this->language->get('hover_input_buttoncolor');    
			$data['hover_input_button_backgroundcolor'] = $this->language->get('hover_input_button_backgroundcolor');    

			if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];
				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if (isset($this->error['name'])) {
				$data['error_name'] = $this->error['name'];
			} else {
				$data['error_name'] = '';
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


			if (!isset($this->request->get['module_id'])) {
				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('heading_title'),
					'href' => $this->url->link('extension/module/load_format_pagination', 'user_token=' . $this->session->data['user_token'], true)
				);
			} else {
				$data['breadcrumbs'][] = array(
					'text' => $this->language->get('heading_title'),
					'href' => $this->url->link('extension/module/load_format_pagination', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
				);
			}


			if (!isset($this->request->get['module_id'])) {
				$data['action'] = $this->url->link('extension/module/load_format_pagination', 'user_token=' . $this->session->data['user_token'], true);
			} else {
				$data['action'] = $this->url->link('extension/module/load_format_pagination', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
			}


			$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

			if (isset($this->request->post['load_format_pagination_status'])) {
				$data['load_format_pagination_status'] = $this->request->post['load_format_pagination_status'];
			} else {
				$data['load_format_pagination_status'] = $this->config->get('load_format_pagination_status');
			}


			if (isset($this->request->post['load_format_pagination_name'])) {
				$data['load_format_pagination_name'] = $this->request->post['load_format_pagination_name'];
			} else {
				$data['load_format_pagination_name'] = $this->config->get('load_format_pagination_name');
			}


			if (isset($this->request->post['load_format_pagination_borderwidth'])) {
				$data['load_format_pagination_borderwidth'] = $this->request->post['load_format_pagination_borderwidth'];
			} elseif ($this->config->get('load_format_pagination_borderwidth')) {
				$data['load_format_pagination_borderwidth'] = $this->config->get('load_format_pagination_borderwidth');
			} else {
				$data['load_format_pagination_borderwidth'] = 1;
			}

			if (isset($this->request->post['load_format_pagination_borderround'])) {
				$data['load_format_pagination_borderround'] = $this->request->post['load_format_pagination_borderround'];
			} elseif($this->config->get('load_format_pagination_borderround')) {
				$data['load_format_pagination_borderround'] = $this->config->get('load_format_pagination_borderround');
			} else {
				$data['load_format_pagination_borderround'] = 4;
			}

			if (isset($this->request->post['load_format_pagination_bordercolor'])) {
				$data['load_format_pagination_bordercolor'] = $this->request->post['load_format_pagination_bordercolor'];
			} elseif($this->config->get('load_format_pagination_bordercolor')) {
				$data['load_format_pagination_bordercolor'] = $this->config->get('load_format_pagination_bordercolor');
			} else {
				$data['load_format_pagination_bordercolor'] = '#CCCCCC';
			}

			if (isset($this->request->post['load_format_pagination_buttoncolor'])) {
				$data['load_format_pagination_buttoncolor'] = $this->request->post['load_format_pagination_buttoncolor'];
			} elseif($this->config->get('load_format_pagination_buttoncolor')) {
				$data['load_format_pagination_buttoncolor'] = $this->config->get('load_format_pagination_buttoncolor');
			} else {
				$data['load_format_pagination_buttoncolor'] = '#333333';
			}

			if (isset($this->request->post['load_format_pagination_backgroundcolor'])) {
				$data['load_format_pagination_backgroundcolor'] = $this->request->post['load_format_pagination_backgroundcolor'];
			} elseif($this->config->get('load_format_pagination_backgroundcolor')) {
				$data['load_format_pagination_backgroundcolor'] = $this->config->get('load_format_pagination_backgroundcolor');
			} else {
				$data['load_format_pagination_backgroundcolor'] = '#FFFFFF';
			}

			if (isset($this->request->post['load_format_pagination_hover_buttoncolor'])) {
				$data['load_format_pagination_hover_buttoncolor'] = $this->request->post['load_format_pagination_hover_buttoncolor'];
			} elseif($this->config->get('load_format_pagination_hover_buttoncolor')) {
				$data['load_format_pagination_hover_buttoncolor'] = $this->config->get('load_format_pagination_hover_buttoncolor');
			} else {
				$data['load_format_pagination_hover_buttoncolor'] = '#333333';
			}

			if (isset($this->request->post['load_format_pagination_hover_backgroundcolor'])) {
				$data['load_format_pagination_hover_backgroundcolor'] = $this->request->post['load_format_pagination_hover_backgroundcolor'];
			} elseif($this->config->get('load_format_pagination_hover_backgroundcolor')) {
				$data['load_format_pagination_hover_backgroundcolor'] = $this->config->get('load_format_pagination_hover_backgroundcolor');
			} else {
				$data['load_format_pagination_hover_backgroundcolor'] = '#DDDDDD';
			}


			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('extension/module/load_format_pagination', $data));
		
		}


			protected function validate()
			{
			
			if (!$this->request->post['load_format_pagination_name']) {
				$this->error['load_format_pagination_name'] = $this->language->get('error_name');
			}


			return !$this->error;
		}

	}