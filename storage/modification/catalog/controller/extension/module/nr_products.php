<?php
class ControllerExtensionModuleNrProducts extends Controller {
	public function index() 
	{
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$width = $this->config->get('theme_default_image_popup_width');
		$height = $this->config->get('theme_default_image_popup_height');
		
		$products = $this->config->get('module_nr_products_products');

				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
		$data['products'] = array();
		if(!empty($products)) {
			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			foreach($products as $key => $product) {
				$info = $this->model_catalog_product->getProduct($product['product_id']);
				if(empty($info)) continue;
				$image = empty($info['image']) ? 'placeholder.png' : $info['image'];
				$price = empty($info['special']) ? $info['price'] : $info['special'];
				
				$data['products'][$product['sort_order']] = [
					'product_id' => $info['product_id'],
					'name' => $info['name'],
					'short_desc' => $product['short_desc'],
					'description' => html_entity_decode($product['desc']),
					'href' => $this->url->link('product/product', 'product_id=' . $info['product_id']),
					'image' => $this->model_tool_image->resize($image, $width, $height),
					//'price' => $this->currency->format($this->tax->calculate($price, $info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
					'number' => str_pad(($key+1), 2, '0', STR_PAD_LEFT)
				];
			}
			$data['total'] = str_pad(count($products), 2, '0', STR_PAD_LEFT);
		}
		
		return $this->load->view('extension/module/nr_products', $data);
	}
	
}