<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

        /** Load Pagination Format **/
		$data['load_format_pagination'] = $this->load->controller('common/load_format_pagination');

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['robots'] = $this->document->getRobots();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$this->load->language('common/header');
		
		
		$host = isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')) ? HTTPS_SERVER : HTTP_SERVER;
		if ($this->request->server['REQUEST_URI'] == '/') {
			$data['og_url'] = $this->url->link('common/home');
		} else {
			$data['og_url'] = $host . substr($this->request->server['REQUEST_URI'], 1, (strlen($this->request->server['REQUEST_URI'])-1));
		}
		
		$data['og_image'] = $this->document->getOgImage();
		


		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			//$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			$data['text_wishlist'] = $this->model_account_wishlist->getTotalWishlist(); // Noir
		} else {
			//$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			$data['text_wishlist'] = isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0; //Noir
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		//$data['account'] = $this->url->link('account/account', '', true);
		$data['account'] = $this->url->link('account/edit', '', true); // Noir
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		
		if ($this->config->get('configblog_blog_menu')) {
			$data['blog_menu'] = $this->load->controller('blog/menu');
		} else {
			$data['blog_menu'] = '';
		}
		$data['search'] = $this->load->controller('common/search');
		//$data['cart'] = $this->load->controller('common/cart');
		//< Noir
		$data['cart'] = $this->load->controller('common/nr_cart'); 
		$data['cart_pannel'] = $this->load->controller('common/nr_cart/cart_pannel');
		$data['group'] = $this->customer->getGroupId();
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['email'] = $this->config->get('config_email'); 
				
		$data['facebook'] = $this->config->get('config_facebook');
		$data['instagram'] = $this->config->get('config_instagram');
		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		$data['menu'] = $this->load->controller('common/menu/mainmenu');

		$telephones = array_map('trim', explode("\n", $this->config->get('config_telephone')));
		$social    = [];
		if ($this->config->get('config_facebook')) {
			$social[] = $this->config->get('config_facebook');
		}
		if ($this->config->get('config_youtube')) {
			$social[] = $this->config->get('config_youtube');
		}
		$schema = [
			'@context' => 'http://schema.org',
			'@type'    => 'Store',
			'name'     => $this->config->get('config_name'),
			'image'    => ($this->config->get('config_url')) . 'image/' . $this->config->get('config_logo'),
			'telephone'=> $telephones,
			'priceRange'=> $this->config->get('config_price_range'),
			'sameAs'   => $social,
			'openingHoursSpecification' => [[
				'@type'    => 'OpeningHoursSpecification',
				'dayOfWeek'=> ['Monday','Tuesday','Wednesday','Thursday','Friday'],
				'opens'    => $this->config->get('config_open_time'),
				'closes'   => $this->config->get('config_close_time'),
			]],
			'address'  => [
				'@type'           => 'PostalAddress',
				'streetAddress'   => $this->config->get('config_address'),
				'addressLocality' => $this->config->get('config_city'),
				'addressCountry'  => [
					'@type' => 'Country',
					'name'  => $this->config->get('config_country'),
				],
			],
		];


		$this->document->setSchema($schema);

		//Noir >
		
		//$data['menu'] = $this->load->controller('common/menu');

		return $this->load->view('common/header', $data);
	}
}
