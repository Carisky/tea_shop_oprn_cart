<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerExtensionModuleFeaturedProduct extends Controller {
	public function index($setting) {
		
		
		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
		
		$results = array();
		
		$this->load->model('catalog/cms');
		
			if (isset($this->request->get['manufacturer_id'])) {
					
					$filter_data = array(
						'manufacturer_id'  => $this->request->get['manufacturer_id'],
						'limit' => $setting['limit']
					);
					
					$results = $this->model_catalog_cms->getProductRelatedByManufacturer($filter_data);
				
			} else {
				
					$parts = explode('_', (string)$this->request->get['path']);
					
					if(!empty($parts) && is_array($parts)) {
					
						$filter_data = array(
							'category_id'  => array_pop($parts),
							'limit' => $setting['limit']
						);
						
					$results = $this->model_catalog_cms->getProductRelatedByCategory($filter_data);
								
					}
			}
		
		$this->load->language('extension/module/featured_product');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
		$data['products'] = array();
		
		if (!empty($results)) {
			
			foreach ($results as $product) {

				if ($product) {
					if ($product['image']) {
						$image = $this->model_tool_image->resize($product['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product['special']) {
						$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product['rating'];
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
						'product_id'  => $product['product_id'],
						'thumb'       => $image,

				#XML Mart Extensions
				'fb_status'       => $this->config->get('module_me_fb_events_status'),
				'fb_track_cart'       => $this->config->get('module_me_fb_events_track_cart'),
				'fb_track_wishlist' => $this->config->get('module_me_fb_events_track_wishlist'),
				#XML Mart Extensions
			
						'name'        => $product['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'])
					);
				}
			}
		}
		
		return $this->load->view('extension/module/featured_product', $data);

	}
	
}