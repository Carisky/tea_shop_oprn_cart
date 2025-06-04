<?php
class ControllerCrmCrm extends Controller {
	private $error = array();
	private $schema = NULL;
	private $languages = array();
	private $limit = 10;
	private $load_images = 0;
	private $setting;
		
	public function __construct($registry)
	{
		parent::__construct($registry);
		$this->load->model('setting/setting');
		$this->setting = $this->model_setting_setting->getSetting('nr_crm');
	}
	
	public function sendJson($data)
	{
		header('Content-Type:"application/json;charset=utf-8"');
		echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit();
	}
	
	public function index() 
	{
		$this->load->language('crm/crm');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['user_token'] = $this->session->data['user_token'];
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') and $this->validateForm()) {
			$this->model_setting_setting->editSetting('nr_crm', $this->request->post);
			$this->session->data['text_success'] = $this->language->get('text_settings_success');
			$this->response->redirect($this->url->link('crm/crm', 'user_token=' . $this->session->data['user_token'], true));
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('crm/crm', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['error'] = $this->error;
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		
		if (!function_exists('curl_init')) {
			$data['error_warning'] = $this->language->get('error_curl');
			$data['allowed'] = 0;
		} else {
			$data['allowed'] = 1;
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' and !empty($this->request->post)) {
			$data['settings'] = $this->request->post;
		} else {
			$data['settings'] = $this->setting;
		}
		
		if(empty($this->setting['nr_crm_url']) or empty($this->setting['nr_crm_token']) or !trim($this->setting['nr_crm_url']) or !trim($this->setting['nr_crm_token'])) {
			$data['allowed'] = 0;
			$data['error_warning'] = $this->language->get('error_settings');
		}
		
		$data['schemas'] = $this->getSchemas();
		
		$data['import_process'] = isset($this->session->data['crm_import']['process']) ? $this->session->data['crm_import']['process'] : 0;
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
		$data['action'] = $this->url->link('crm/crm', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('crm/crm', $data));
	}
	
	protected function validateForm() 
	{
		if (!$this->user->hasPermission('modify', 'crm/crm')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post as $key => $value) {
			if(!$value) $this->error[$key] =  $this->language->get('error_required');
		}
		return !$this->error;
	}
	
	public function getSchema()
	{
		if(!is_null($this->schema)) return $this->schema;
		$schema = $this->config->get('nr_crm_fields_schema');
		if($schema) $this->schema = parse_ini_file(DIR_SYSTEM.'nr_crm/'.$schema.'.ini', true);
		return $this->schema;
	}
	
	public function getSchemas()
	{
		$schemas = array();
		$path = DIR_SYSTEM.'nr_crm/';
		$files =  glob($path.'*.ini');
		if(!empty($files)) {
			foreach ($files as $key=>$file) {
				$filename = basename(str_replace("\\", "/", $file), '.ini');
				$content = parse_ini_file($path.$filename.'.ini');
				$schemas[] = array(
					'filename' => $filename,
					'name' => $content['shcena_name'],
				);
			}
		}
		return $schemas; 
	}
	
	public function prepare()
	{
		$invertory_id = $this->config->get('nr_crm_nventory_id');
		
		$result = $this->getProductsList($invertory_id);
		if(!is_array($result)) {
			$this->session->data['error'] = $result;
			$this->sendJson(['redirect' => 1]);
		} elseif(empty($result)) {
			$this->session->data['error'] = 'Inventory is empty';
			$this->sendJson(['redirect' => 1]);
		}
		
		$ids = array();
		foreach($result as $product) {
			$ids[] = $product['id'];
		}
		$products = array_chunk($ids, $this->limit);
		
		$this->session->data['crm_import'] = [
			'products' => $products,
			'invertory_id' => $invertory_id,
			'process' => 1,
			'added' => 0,
			'error' => 0,
			'updated' => 0,
			'errors' => [],
			'total' => count($ids)
		];
		$this->sendJson(['process' => 1]);
	}
	
	public function load()
	{
		$session_data = $this->session->data['crm_import'];
		$products = $session_data['products'];
		if(empty($products)) {
			$this->session->data['crm_import']['process'] = 0;
			$this->sendJson(['bar' => 100, 'process' => 0]);
		}
		
		$ids = array_shift($products);
		$this->session->data['crm_import']['products'] = $products;
				
		$this->load->model('localisation/language');
		$this->languages = $this->model_localisation_language->getLanguages();
		
		$result = $this->getProductsData($this->session->data['crm_import']['invertory_id'], $ids);
		if(!is_array($result)) {
			$this->session->data['error'] = $result;
			$this->sendJson(['redirect' => 1]);
		} elseif(empty($result)) {
			$this->session->data['crm_import']['process'] = 0;
			$this->sendJson(['bar' => 100, 'process' => 0]);
		}
		
		$this->getSchema();
		
		$this->saveProducts($result);
		$this->clearCache();
		
		if(empty($products)) {
			$this->session->data['crm_import']['process'] = 0;
			$this->sendJson(['bar' => 100, 'process' => 0]);
		}
		
		$bar = round((($session_data['added'] + $session_data['updated']) / $session_data['total']) * 100);
		$this->sendJson(['bar' => $bar, 'process' => 1]);
	}
	
	public function finalize()
	{
		$this->load->language('crm/crm');
		$session_data = $this->session->data['crm_import'];
		$this->session->data['crm_import'] = array();
		include (DIR_TEMPLATE.'crm/report.php');
		exit();
	}
		
	protected function sendRequest($method, $parameters = []) 
	{
		if (!function_exists('curl_init')) return 'cURL not exists';
		$this->load->language('crm/crm');
								
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, trim($this->setting['nr_crm_url']));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'method='.$method.'&parameters='.json_encode($parameters));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-BLToken: '.trim($this->setting['nr_crm_token'])]);
		$output = curl_exec($ch);
		curl_close($ch);
		if(!$output) return $this->language->get('error_api');
		$output = json_decode($output, true);
		if(!isset($output['status'])) return $this->language->get('error_api');
		if($output['status'] != 'SUCCESS') return $output['error_code'].': '.$output['error_message'];
		return $output;
	}
	
	protected function getProductsList($inventory_id)
	{
		$data = [
			'inventory_id' => (int)$inventory_id
		];
		
		$result = $this->sendRequest('getInventoryProductsList', $data);
		if(!is_array($result)) return $result;
		if(empty($result['products'])) return [];
		return $result['products'];
	}
	
	protected function getProductsData($inventory_id, $ids)
	{
		$data = [
			'inventory_id' => (int)$inventory_id,
			'products' => $ids
		];
		
		$result = $this->sendRequest('getInventoryProductsData', $data);
		if(!is_array($result)) return $result;
		if(empty($result['products'])) return [];
		return $result['products'];
	}
	
	protected function saveProducts($data)
	{
		$this->load->model('tool/crm');
		$this->load->model('catalog/product');
		if($this->load_images) $this->load->model('tool/image');
		$i = 0;
		foreach($data as $key => $item) {
			if(empty($item['text_fields']['name']) and !$item['ean']) continue;
			
			$product_id = $this->model_tool_crm->getProductId($key);
						
			$product = [
				'quantity' => (empty($item['stock']) ? 0 : array_sum($item['stock'])),
				'price' => (empty($item['prices']) ? 0 : array_shift($item['prices']))
			];
	
			if($product_id) {
				$this->model_tool_crm->updateProduct($product_id, $product);
				$this->session->data['crm_import']['updated'] += 1;
			} else {
				$product['weight_class_id'] = 1;
				$product['length_class_id'] = 1;
				$product['status'] = 1;
				$product['stock_status_id'] = 1;
				$product['manufacturer_id'] = 1;
				$product['tax_class_id'] = 1;
				$product['upc'] = '';
				$product['mpn'] = '';
				$product['jan'] = '';
				$product['isbn'] = '';
				$product['location'] = '';
				$product['minimum'] = 1;
				$product['subtract'] = 1;
				$product['date_available'] = '0000-00-00';
				$product['manufacturer_id'] = 1;
				$product['shipping'] = 1;
				$product['points'] = 0;
				$product['noindex'] = 1;
				$product['tax_class_id'] = 1;
				$product['sort_order'] = 0;
				$product['product_store'] = [0];
				
				$product['model'] = (int)$key;
				$product['weight'] = $item['weight'];
				$product['length'] = $item['length'];
				$product['width'] = $item['width'];
				$product['height'] = $item['height'];
				$product['ean'] = $item['ean'];
				$product['sku'] = $item['sku'];
				
				$product['product_description'] = array();
				$product['product_seo_url'] = array();
				
				$name = empty($item['text_fields']['name']) ? '' : $item['text_fields']['name'];
				if($name) $url = $this->urlTransliterate($name);
				$code = $this->config->get('config_language');
				foreach($this->languages as $language) {
					$product['product_description'][$language['language_id']] = [
						'name' => $name,
						'description' => '',
						'tag' => '',
						'meta_title' => '',
						'meta_description' => '',
						'meta_keyword' => '',
						'meta_h1' => '',
					];
					if($name) $product['product_seo_url'][0][$language['language_id']] = ($code != $language['code'] ? substr($language['code'], 0, 2).'-' : '').$url;
				}
				
				$categories_schema = array_flip($this->schema['category_id']);
				if(isset($categories_schema[$item['category_id']])) {
					$product['product_category'] = explode('_', $categories_schema[$item['category_id']]);
				} else {
					$product['product_category'] = [(int)$this->setting['nr_crm_default_category_id']];
				}
				if($this->load_images) {
					if(!empty($item['images'])) {
						$product['product_image'] = array();
						foreach($item['images'] as $key => $image) {
							$product['product_image'][] = [
								'sort_order' => $key,
								'image' => $this->loadImage($image) 
							];
						}
					}
				}
				
				$result = $this->model_catalog_product->addProduct($product);
				if($result) {
					$this->session->data['crm_import']['added'] += 1;
				} else {
					$this->session->data['crm_import']['error'] += 1;
					$this->session->data['crm_import']['errors'][] = $key;
				}
			}
			$i++;
		}
		return $i;
	}
	
	protected function loadImage($url) 
	{
		$folder = DIR_IMAGE.'catalog/products/';
		if(!file_exists($folder)) mkdir($folder, 0750);
		
		$segments = explode('/', $url);
		$img_name = str_replace(' ', '_', urldecode(end($segments)));
		if(strpos($img_name, '?') !== false) {
			$arr = explode('?', $img_name);
			$img_name = $arr[0];
		}
		$is_valide = 0;
		$img_ext = array('jpg','JPG','png','gif');
		if($img_name) {
			$arr = explode('.', $img_name);
			if(isset($arr[1]) and in_array($arr[1], $img_ext)) $is_valide = 1;
		}
		if($is_valide) {
			$img_name = $this->imgTransliterate($img_name);
			$image = 'catalog/products/'.$img_name;
			$file = curl_init($url);
			$fp = fopen($folder.$img_name, 'wb');
			curl_setopt($file, CURLOPT_HEADER, 0);  
			curl_setopt($file, CURLOPT_FILE, $fp);
			curl_exec($file);
			curl_close($file);
			fclose($fp);

			if(file_exists($folder.$img_name) and @is_array(getimagesize($folder.$img_name))) {
				$this->model_tool_image->resize($image, 100, 100);
				return $image;
			}
			return '';
		}
		
		return '';
	}
	
	protected function transliterate($str) 
	{
		static $map = null;
		if (is_null($map)) {
			$map = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 
			'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'x', 'ы' => 'y', 'э' => 'e', 'А' => 'A', 'Б' => 'B', 
			'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'X', 'Ы' => 'Y', 'Э' => 'E', 'ж' => 'zh', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 
			'щ' => 'sch', 'ь' => '', 'ъ' => '', 'ю' => 'yu', 'я' => 'ya', 'Ж' => 'Zh', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ъ' => '', 'Ю' => 'Yu', 
			'Я' => 'ya', 'ї' => 'i', 'Ї' => 'i', 'ґ' => 'g', 'Ґ' => 'g', 'є' => 'е', 'Є' => 'е', 'І' => 'i', 'і' => 'i', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c', 'ć' => 'c',
			'Ę' => 'E', 'ę' => 'e', 'Ł' => 'L', 'ł' => 'l', 'Ń' => 'N', 'ń' => 'n', 'Ó' => 'O', 'ó' => 'o', 'Ś' => 'S', 'ś' => 's', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z',
			'ż' => 'z', 'Ƶ' => 'Z', 'ƶ' => 'z'
			);
		}
		return strtr($str, $map);
		
	}
	
	protected function imgTransliterate($str)
	{
		$str = $this->transliterate($str);
		$str = str_replace([' ', '/'], '_', $str);
		return trim($str);
	}
	
	public  function urlTransliterate($str)
	{
		$str = strip_tags($str);
		$str = str_replace(array("\n", "\r"), '', $str);
		$str = $this->transliterate($str);
		$str = strtolower($str);
		$str = preg_replace("/[^0-9a-z-_\/ ]/i", '', $str);
		$str = preg_replace("/\s+/", ' ', $str);
		$str = str_replace([' ', '_', '/'], '-', $str);
		return trim($str, '-');
	}
	
	protected function clearCache()
	{
		$this->cache->delete('product');
		if($this->config->get('config_seo_pro')) $this->cache->delete('seopro');
	}
	
	
	
}