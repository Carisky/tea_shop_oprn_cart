<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();
		$data['informations1'] = array(); //Noir

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'active' => (!empty($this->request->get['information_id']) and $this->request->get['information_id'] == $result['information_id']), //Noir
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			} else { // Noir
				$data['informations1'][] = array(
					'title' => $result['title'],
					'active' => (!empty($this->request->get['information_id']) and $this->request->get['information_id'] == $result['information_id']), //Noir
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		//$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		/* Noir
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}
		*/
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		// < Noir
		$data['is_home'] = $this->request->get['route'] == 'common/home';
		$data['informations'][] = $this->load->controller('blog/menu/nrGetMain');
		$data['menu'] = $this->load->controller('common/menu');
		$data['facebook'] = $this->config->get('config_facebook');
		$data['instagram'] = $this->config->get('config_instagram');
		$data['group'] = $this->customer->getGroupId();
		$data['logged'] = $this->customer->isLogged();
		$data['route'] = $this->request->get['route'];
		$data['address'] = $this->config->get('config_address');
		$data['phone'] = $this->config->get('config_telephone');
		$data['email'] = $this->config->get('config_email');
		
		$data['account_order'] = $this->url->link('account/order', '', true);
		$data['account_edit'] = $this->url->link('account/edit', '', true);
		$data['account_address'] = $this->url->link('account/address', '', true);
		$data['account_return'] = $this->url->link('account/return', '', true);
		$data['account_transaction'] = $this->url->link('account/transaction', '', true);
		$data['account_recurring'] = $this->url->link('account/recurring', '', true);
		
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error_warning']);
		}
		//Noir >
		$data['scripts'] = $this->document->getScripts('footer');
		$data['styles'] = $this->document->getStyles('footer');
		

			$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
		$data['me_fb_events_pixel_id'] = $this->config->get('module_me_fb_events_pixel_id');
		$data['me_fb_events_track_search'] = $this->config->get('module_me_fb_events_track_search');
		if($this->customer->isLogged()){
			$data['customer_id'] = $this->customer->getId();
		}else{
			$data['customer_id'] = 0;
		}
			if($this->config->get('module_me_fb_events_status')){
					$this->document->addScript('catalog/view/javascript/me_fb_events/common.js');
				}
			

			$data['schemas_org'] = $this->document->getSchema();
			
		return $this->load->view('common/footer', $data);
	}
}
