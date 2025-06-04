<?php
class ControllerCommonMenu extends Controller { // Noir
	public function index() {
		
		$this->load->model('catalog/category');
		$data['categories'] = array();
		$categories = $this->model_catalog_category->getCategories(0);
		if(empty($this->request->get['path'])) {
			$path = array();
		} else {
			$path = explode('_', $this->request->get['path']);
		}
		foreach ($categories as $category) {
			if(!$category['top']) continue;
			$data['categories'][] = [
				'name'     => $category['name'],
				'href'     => $this->url->link('product/category', 'path=' . $category['category_id']),
				'active'   => in_array($category['category_id'], $path) ? 1 : 0
			];
		}
		return $this->load->view('common/footer_menu', $data);
	}
	
	public function mainmenu() { // Noir 
		$memu = $this->cache->get('menu.catalog.'.(int)$this->config->get('config_language_id').'.'.(int)$this->config->get('config_store_id'));
		//$memu = ''; //////////
		if(!empty($memu)) {
			$data['categories'] = $memu['categories'];
			$data['catmenu'] = $memu['catmenu'];
		} else {
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');

			$data['categories'] = array();
			$data['catmenu'] = array();
			
			$categories = $this->model_catalog_category->getCategories(0);

			foreach ($categories as $key => $category) {
				if ($category['top']) {
					// Level 2
					$children_data = array();

					$children = $this->model_catalog_category->getCategories($category['category_id']);

					foreach ($children as $child) {
						$filter_data = array(
							'filter_category_id'  => $child['category_id'],
							'filter_sub_category' => true
						);

						$children_data[] = [
							'name'  		=> $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
							'href'  		=> $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
							'category_id'	=>  $child['category_id']
						];
					}

					// Level 1
					$data['categories'][] = [
						'name'    		 => $category['name'],
						'image'	  		 => $category['image'],
						'children'		 => $children_data,
						'column'  		 => $category['column'] ? $category['column'] : 1,
						'href'   		 => $this->url->link('product/category', 'path=' . $category['category_id']),
						'category_id'	 => $category['category_id']
					];
				} else {
					$data['catmenu'][] = [
						'name'    		 => $category['name'],
						'href'   		 => $this->url->link('product/category', 'path=' . $category['category_id']),
						'category_id'	 => $category['category_id']
					];
				}
			}
			
			$memu = [
				'categories' => $data['categories'],
				'catmenu' => $data['catmenu']
			];
			
			$this->cache->set('menu.catalog.'.(int)$this->config->get('config_language_id').'.'.(int)$this->config->get('config_store_id'), $memu);
			
			if(!empty($this->request->get['path'])) {
				$path = explode('_', $this->request->get['path']);
				foreach($data['categories'] as $key => $category) {
					if($category['category_id'] == $path[0]) {
						//$data['categories'][$key]['active'] = 1;
						if(!isset($path[1])) break;
						foreach($category['children'] as $key1 => $child) {
							if($child['category_id'] == $path[1]) {
								$data['categories'][$key]['children'][$key1]['active'] = 1;
								break;
							}
						}
					}
				}
				foreach($data['catmenu'] as $key => $category) {
					if($category['category_id'] == $path[0]) $data['catmenu'][$key]['active'] = 1;
				}
			}
		}
		
		$this->load->model('catalog/information');
		$data['informations'] = array();
		$data['informations1'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'active' => (!empty($this->request->get['information_id']) and $this->request->get['information_id'] == $result['information_id']), //Noir
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			} else {
				$data['informations1'][] = array(
					'title' => $result['title'],
					'active' => (!empty($this->request->get['information_id']) and $this->request->get['information_id'] == $result['information_id']), //Noir
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}
		$data['blog'] = $this->load->controller('blog/menu/nrGetMain');
		$data['contact'] = $this->url->link('information/contact');
		$data['contact_active'] = $this->request->get['route'] == 'information/contact' ? 1 : 0;
		
		return $this->load->view('common/main_menu', $data);
	}
	
	public function get_products()
	{
		if(empty($this->request->get['category_id'])) exit();
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		
		$categories = $this->model_catalog_category->getChildrenIds($this->request->get['category_id']);
		if(!empty($categories)) {
			$categories[] = (int)$this->request->get['category_id'];
		} else {
			$categories = [(int)$this->request->get['category_id']];
		}
		
		$data['products'] = $this->model_catalog_product->getLatestInCategories($categories, 4);
		
		if($data['products']) {
			$this->load->model('tool/image');
			$w = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
			$h = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');
			foreach($data['products'] as &$product) {
				$image = $product['image'] ? $product['image'] : 'placeholder.png';
				$product['thumb'] = $this->model_tool_image->resize($image, $w, $h);
				$product['href'] =  $this->url->link('product/product', 'product_id=' . $product['product_id']);
				$product['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$product['special'] = $product['special'] > 0 ? $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']) : '';
			}
		}
		$this->response->setOutput($this->load->view('common/menu_products', $data));
	}
}
