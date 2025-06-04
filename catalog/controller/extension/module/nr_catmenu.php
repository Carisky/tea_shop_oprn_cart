<?php
class ControllerExtensionModuleNrCatmenu extends Controller {
	
	public function index() {
		$categories = $this->cache->get('menu.catalog.'.(int)$this->config->get('config_language_id').'.'.(int)$this->config->get('config_store_id'));
		if(empty($categories)) {
			$this->load->model('catalog/category');
			$data['categories'] = array();
			$categories = $this->model_catalog_category->getCategories(0);
			foreach ($categories as $category) {
				if ($category['top']) {
					$data['categories'][] = [
						'name'     => $category['name'],
						'image'	   => $category['image'],
						'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
					];
				}
			}
		} else {
			$data['categories'] = $categories['categories'];
		}
		
		return $this->load->view('extension/module/nr_catmenu', $data);
	}
	
}