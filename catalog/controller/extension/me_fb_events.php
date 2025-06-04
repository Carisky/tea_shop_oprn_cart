<?php
class ControllerExtensionMefbevents extends Controller {
	public function index() {
		$json=array();
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		if(isset($this->request->get['product_id'])){
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			$json['name'] = $product_info['name'];
			if($product_info['special']){
				$json['price'] = $this->currency->format($product_info['special'], $this->session->data['currency'], '', false);
			}else{
				$json['price'] = $this->currency->format($product_info['price'], $this->session->data['currency'], '', false);
			}
		
			$getcategories = $this->model_catalog_product->getCategories($this->request->get['product_id']);
			$categories = array();
			if($getcategories){
				foreach($getcategories as $cate){
					$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
					if($getcategory){
						$categories[] = $getcategory['name'];
					}
				}
			}
			$categories = implode(", ", $categories);
			$json['categories'] = $categories;
			$json['manufacturer'] = $product_info['manufacturer'];
			$json['currencycode'] = $this->session->data['currency'];
			$json['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
			$json['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
			$json['me_fb_events_pixel_id'] = $this->config->get('module_me_fb_events_pixel_id');
			$json['me_fb_events_access_token'] = $this->config->get('module_me_fb_events_access_token');

			if ($this->config->get('module_me_fb_events_access_token')) {
				$this->load->model('extension/me_fb_events');
			    if ($this->request->server['HTTPS']) {
	    			$server = $this->config->get('config_ssl');
	    		} else {
	    			$server = $this->config->get('config_url');
	    		}
				
				$custom_data = array(
			        'content_name' => $json['name'],
			        'currency' => $this->session->data['currency'],
			        'content_ids' => [$product_info['product_id']],
			        'value' => $json['price'],
			        'source_url' => $server . $_SERVER["REQUEST_URI"]
			    );
			    $user_data = array();
			    if ($this->customer->isLogged()) {
			       $user_data = array(
				        'fn' => hash('sha256', $this->customer->getFirstName()),
				        'ln' => hash('sha256', $this->customer->getLastName()),
				        'em' => hash('sha256', $this->customer->getEmail()),
				        'ph' => hash('sha256', $this->customer->getTelephone())
				    );
			    }
				if ($this->request->get['type'] == 'addtocart' && $this->config->get('module_me_fb_events_track_cart')) {
					$json['event_id'] = 'AddToCart.'.time();
					$this->model_extension_me_fb_events->conversionapi('AddToCart', $custom_data, $user_data, $json['event_id']);
				}
				if ($this->request->get['type'] == 'addtowishlist' && $this->config->get('module_me_fb_events_track_wishlist')) {
					$json['event_id'] = 'AddToWishlist.'.time();
					$this->model_extension_me_fb_events->conversionapi('AddToWishlist', $custom_data, $user_data, $json['event_id']);
				}
			}
		}
		print_r(json_encode($json));
	}
	
	public function feed(){
		$me_fb_events_feeds = $this->config->get('module_me_fb_events_feed');
		if(isset($this->request->get['feed'])){
			foreach($me_fb_events_feeds as $feeds){
				$name = preg_replace('/[0-9\@\.\;\" "]+/', '', str_replace(' ','',$feeds['name'])).'.xml';
				if($name == $this->request->get['feed']){
					$this->facebookfeed($feeds);
				}
			}
		}
	}
	
	protected function getPath($parent_id, $current_path = '') {
		$category_info = $this->model_catalog_category->getCategory($parent_id);

		if ($category_info) {
			if (!$current_path) {
				$new_path = $category_info['category_id'];
			} else {
				$new_path = $category_info['category_id'] . '_' . $current_path;
			}

			$path = $this->getPath($category_info['parent_id'], $new_path);

			if ($path) {
				return $path;
			} else {
				return $new_path;
			}
		}
	}
	
	public function facebookfeed($feedsetting){
		$this->load->model('extension/me_fb_events');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$this->load->model('localisation/currency');
		$results = $this->model_localisation_currency->getCurrencies();

		$currencies = array();
		foreach ($results as $result) {
			$currencies[] = $result['code'];
		}
		
		$output  = '<?xml version="1.0" encoding="UTF-8" ?>';
		$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
		$output .= '  <channel>';
		
		$store_info = $this->model_extension_me_fb_events->getStore($feedsetting['store_id']);

		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = $this->config->get('config_url');
		}

		if (is_array($this->config->get('config_meta_description'))) {
			$meta_description = $this->config->get('config_meta_description')[$this->config->get('config_language_id')];
		} else {
			$meta_description = $this->config->get('config_meta_description');
		}
		
		$output .= '  <title>' . $store_name . '</title>';
		$output .= '  <description>' . $meta_description . '</description>';
		$output .= '  <link>' . $store_url . '</link>';

		$product_data = array();
		$filter_data = array(
			'filter_language_id' => $feedsetting['language_id'],
			'filter_store_id' => $feedsetting['store_id'],
		);

		$total_products = $this->model_extension_me_fb_events->getTotalProducts($filter_data);
	
		$products = [];
		$filter_data = array(
			'filter_category' => isset($feedsetting['category']) ? $feedsetting['category'] : '',
			'filter_language_id' => $feedsetting['language_id'],
			'filter_store_id' => $feedsetting['store_id'],
			'customer_group_id' => $feedsetting['customer_cgroup'],
			'filter_manufacturer' => isset($feedsetting['manufacturer']) ? $feedsetting['manufacturer'] : '',
			'filter_minprice' => $feedsetting['minprice'],
			'filter_maxprice' => $feedsetting['maxprice'],
			'filter_minqty' => $feedsetting['minqty'],
			'filter_maxqty' => $feedsetting['maxqty'],
			'filter_minproductid' => isset($feedsetting['minproductid']) ? $feedsetting['minproductid'] : '',
			'filter_maxproductid' => isset($feedsetting['maxproductid']) ? $feedsetting['maxproductid'] : '',
			'start' => !empty($feedsetting['fromlimit']) ? $feedsetting['fromlimit'] : 0,
			'limit' => !empty($feedsetting['tolimit']) ? $feedsetting['tolimit'] : $total_products,
			'sort' => isset($feedsetting['sortby']) ? $feedsetting['sortby'] : '',
			'order' => isset($feedsetting['orderby']) ? $feedsetting['orderby'] : '',
			'filter_filter'      => false
		);

		$products = $this->model_extension_me_fb_events->getProducts($filter_data);

		if($products){
			foreach ($products as $product) {
				if (strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) == '') {
					$product['description'] = $product['name'];
				}

				if (!in_array($product['product_id'], $product_data)) {
					
					$product_data[] = $product['product_id'];
					
					$output .= '<item>';					
					if($feedsetting['gid_tag'] == 'product_id'){
						$output .= '<g:id>' . $product['product_id'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'model'){
						$output .= '<g:id>' . $product['model'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'sku'){
						$output .= '<g:id>' . $product['sku'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'upc'){
						$output .= '<g:id>' . $product['upc'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'ean'){
						$output .= '<g:id>' . $product['ean'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'jan'){
						$output .= '<g:id>' . $product['jan'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'isbn'){
						$output .= '<g:id>' . $product['isbn'] . '</g:id>';
					}elseif($feedsetting['gid_tag'] == 'mpn'){
						$output .= '<g:id>' . $product['mpn'] . '</g:id>';
					}
					
					$output .= '<g:title><![CDATA[' . $product['name'] . ']]></g:title>';
					$output .= '<g:description><![CDATA[' . strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) . ']]></g:description>';
					$output .= '<g:link><![CDATA[' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . ']]></g:link>';
					if ($product['image'] && is_file(DIR_IMAGE . $product['image'])) {
						$output .= '  <g:image_link><![CDATA[' . $this->model_tool_image->resize($product['image'], 600, 600) . ']]></g:image_link>';
					} else {
						$output .= '  <g:image_link></g:image_link>';
					}
					$output .= '  <g:availability><![CDATA[' . ($product['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>';
					
					$output .= '<g:condition>new</g:condition>';

					if (in_array($feedsetting['currency'], $currencies)) {
						$currency_code = $feedsetting['currency'];
						$currency_value = $this->currency->getValue($feedsetting['currency']);
					} else {
						$currency_code = 'USD';
						$currency_value = $this->currency->getValue('USD');
					}

					if($feedsetting['pricetax']){
						$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:price>';
					}else{
						$output .= '  <g:price>' .  $this->currency->format($product['price'], $currency_code, $currency_value, false) . '</g:price>';
					}

					if(!empty($feedsetting['sale_price_status'])){
						if ((float)$product['special']) {
							if($feedsetting['pricetax']){
								$output .= '  <g:sale_price>' .  $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:sale_price>';
							}else{
								$output .= '  <g:sale_price>' .  $this->currency->format($product['special'], $currency_code, $currency_value, false) . '</g:sale_price>';
							}

							if ($feedsetting['sale_from'] && $feedsetting['sale_to']) {
								$output .= '  <g:sale_price_effective_date>' .  $feedsetting['sale_from'] . 'T00:00/' .  $feedsetting['sale_to'] . 'T00:00</g:sale_price_effective_date>';
							}
						} else {
							$discountprice = $this->model_extension_me_fb_events->getPrice($product['product_id']);
							if ($discountprice) {
								if($feedsetting['pricetax']){
									$output .= '  <g:sale_price>' . $this->currency->format($this->tax->calculate($discountprice, $product['tax_class_id']), $currency_code, $currency_value, false) . '</g:sale_price>';
								}else{
									$output .= '  <g:sale_price>' .  $this->currency->format($discountprice, $currency_code, $currency_value, false) . '</g:sale_price>';
								}

								if ($feedsetting['sale_from'] && $feedsetting['sale_to']) {
									$output .= '  <g:sale_price_effective_date>' .  $feedsetting['sale_from'] . 'T00:00/' .  $feedsetting['sale_to'] . 'T00:00</g:sale_price_effective_date>';
								}
							}
						}
					}
					
					if ($product['mpn']) {
						$output .= '  <g:mpn><![CDATA[' . $product['mpn'] . ']]></g:mpn>' ;
					} else {
						$output .= '  <g:identifier_exists>false</g:identifier_exists>';
					}

					if ($product['upc']) {
						$output .= '  <g:upc>' . $product['upc'] . '</g:upc>';
					}

					if ($product['ean']) {
						$output .= '  <g:ean>' . $product['ean'] . '</g:ean>';
					}
					
					$output .= '<g:brand><![CDATA[' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>';
					
					$categories = $this->model_catalog_product->getCategories($product['product_id']);

					$google_category_id = '';
					$facebook_category_id = '';
					foreach ($categories as $cate) {
						if (!$google_category_id) {
							$google_category_id = $this->model_extension_me_fb_events->getGCategorybyid($cate['category_id']);
						}
						if (!$facebook_category_id) {
							$facebook_category_id = $this->model_extension_me_fb_events->getFbCategorybyid($cate['category_id']);
						}
						$path = $this->getPath($cate['category_id']);

						if ($path) {
							$string = '';

							foreach (explode('_', $path) as $path_id) {
								$category_info = $this->model_catalog_category->getCategory($path_id);

								if ($category_info) {
									if (!$string) {
										$string = $category_info['name'];
									} else {
										$string .= ' &gt; ' . $category_info['name'];
									}
								}
							}

							$output .= '<g:product_type><![CDATA[' . $string . ']]></g:product_type>';
						}
					}

					if(!empty($feedsetting['fbcategory_status']) && $facebook_category_id){
						$output .= '  <g:fb_product_category>' . $facebook_category_id . '</g:fb_product_category>';
					}
					
					if(!empty($feedsetting['gcategory_status']) && $google_category_id){
						$output .= '  <g:google_product_category>' . $google_category_id . '</g:google_product_category>';
					}
					
					
					$output .= '  <g:quantity>' . $product['quantity'] . '</g:quantity>';
					if(!empty($product['length']) && $product['length'] != '0.00'){
						$output .= '  <g:product_length>' . $this->length->format($product['length'], $product['length_class_id']) . '</g:product_length>';
					}
					if(!empty($product['width']) && $product['width'] != '0.00'){
						$output .= '  <g:product_width>' . $this->length->format($product['width'], $product['length_class_id']) . '</g:product_width>';
					}
					if(!empty($product['height']) && $product['height'] != '0.00'){
						$output .= '  <g:product_height>' . $this->length->format($product['height'], $product['length_class_id']) . '</g:product_height>';
					}
					if($product['weight']){
						$output .= '  <g:product_weight>' . $this->weight->format($product['weight'], $product['weight_class_id']) . '</g:product_weight>';
					}
					
					$output .= '</item>';
				}
			}
		}

		$output .= '  </channel>';
		$output .= '</rss>';
		
		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
	}

	public function fb_custom(){
		$this->load->model('extension/me_fb_events');
		$trace = $this->request->post['trace'];
		$event_id = isset($this->request->post['event_id']) ? $this->request->post['event_id']: "";
		$data = array(
			'source_url' => $this->request->post['source_url'],
			'currency' => $this->session->data['currency'],
		);

		$user_data = array();

		$this->model_extension_me_fb_events->conversionapi($trace, $data, $user_data, $event_id);
	}
}