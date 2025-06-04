<?php
class ControllerExtensionModuleBestSeller extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/bestseller');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
		$data['products'] = array();

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}
	
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}


				if(isset($result) && isset($result['product_id'])) {
					$mefb = $result;
				}elseif (isset($product_info) && isset($product_info['product_id'])) {
					$mefb = $product_info;
				}
				if(isset($mefb)){
					if($mefb['special']){
						$wprice = $this->currency->format($mefb['special'], $this->session->data['currency'], '', false);
					}else{
						$wprice = $this->currency->format($mefb['price'], $this->session->data['currency'], '', false);
					}
					
					$getcategories = $this->model_catalog_product->getCategories($mefb['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? html_entity_decode($getcategory['name']) : '';
						}
					}
					$categories = implode(", ", $categories);

					$data['fbproduct'][] = array(
						'manufacturer' => html_entity_decode($mefb['manufacturer']),
						'categories'   => $categories,
						'price'	  	   => $wprice,
						'product_id'   => $mefb['product_id'],
						'name'         => html_entity_decode($mefb['name'])
					);
				}
			
				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,

				#XML Mart Extensions
				'fb_status'       => $this->config->get('module_me_fb_events_status'),
				'fb_track_cart'       => $this->config->get('module_me_fb_events_track_cart'),
				'fb_track_wishlist' => $this->config->get('module_me_fb_events_track_wishlist'),
				#XML Mart Extensions
			
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			return $this->load->view('extension/module/bestseller', $data);
		}
	}
}
