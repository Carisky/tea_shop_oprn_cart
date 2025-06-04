<?php
class ModelExtensionMefbevents extends Model {
	 public function getCategories() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "me_google_category` ORDER BY google_category ASC");

		return $query->rows;
    }
	
	public function getGCategorybyid($category_id) {
		$query = $this->db->query("SELECT google_category_id FROM `" . DB_PREFIX . "me_google_category` WHERE category_id = '" . (int)$category_id . "' ORDER BY google_category ASC");

		return isset($query->row['google_category_id']) ? $query->row['google_category_id'] : '';
    }	

   	public function getallCategories($categories = array()) {
   		$sql = "SELECT category_id FROM `" . DB_PREFIX . "category`";

   		if (!empty($categories)) {
			$implode = array();

			foreach ($categories as $category_id) {
				$implode[] = "category_id = '" . (int)$category_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		}

		$sql .= " ORDER BY category_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
    }
	public function getTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "me_google_category`");

		return $query->row['total'];
    }
	public function getFbCategories() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "me_facebook_category` ORDER BY facebook_category ASC");

		return $query->rows;
    }
	public function getFbCategorybyid($category_id) {
		$query = $this->db->query("SELECT facebook_category_id FROM `" . DB_PREFIX . "me_facebook_category` WHERE category_id = '" . (int)$category_id . "' ORDER BY facebook_category ASC");

		return isset($query->row['facebook_category_id']) ? $query->row['facebook_category_id'] : '';
    }

	public function getFbTotalCategories() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "me_facebook_category`");

		return $query->row['total'];
    }
	
	public function getStore($store_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store WHERE store_id = '" . (int)$store_id . "'");

		return $query->row;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$data['filter_language_id'] . "'";

		$sql .= " AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['filter_store_id'] . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getProduct($product_id,$data) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$data['customer_group_id'] . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$data['filter_language_id'] . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$data['filter_language_id'] . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$data['filter_language_id'] . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$data['filter_language_id'] . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['filter_store_id']."'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => $query->row['price'],
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round(($query->row['rating']===null) ? 0 : $query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

	public function getProducts($data = array()) {
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";

		if (!empty($data['filter_category_id']) || !empty($data['filter_category'])) {
			$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";

			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$data['filter_language_id'] . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['filter_store_id'] . "'";

		if(!empty($data['filter_category'])){
			$sql .= " AND p2c.category_id IN (" . implode(',', $data['filter_category']) . ")";

			if (!empty($data['filter_filter'])) {
				$implode = array();

				$filters = explode(',', $data['filter_filter']);

				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}

				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
			}
		}

		if (!empty($data['filter_manufacturer'])) {
			$sql .= " AND p.manufacturer_id IN (" . implode(',', $data['filter_manufacturer']) . ")";
		}
		
		if (!empty($data['filter_minqty'])) {
			$sql .= " AND p.quantity >= '" . (int)$data['filter_minqty'] . "'";
		}

		if (!empty($data['filter_minproductid']) && !empty($data['filter_minproductid'])) {
			$sql .= " AND p.product_id BETWEEN " . (int)$data['filter_minproductid'] . " AND " . (int)$data['filter_maxproductid'] . "";
		}elseif (!empty($data['filter_minproductid'])) {
			$sql .= " AND p.product_id >= '" . (int)$data['filter_minproductid'] . "'";
		}elseif (!empty($data['filter_maxproductid'])) {
			$sql .= " AND p.product_id < '" . (int)$data['filter_maxproductid'] . "'";
		}
		
		if (!empty($data['filter_maxqty'])) {
			$sql .= " AND p.quantity < '" . (int)$data['filter_maxqty'] . "'";
		}

		if (!empty($data['filter_maxprice'])) {
			$sql .= " AND p.price < '" . (int)$data['filter_maxprice'] . "'";
		}
		
		if (!empty($data['filter_minprice'])) {
			$sql .= " AND p.price >= '" . (int)$data['filter_minprice'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'p.product_id',
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
		}

		return $product_data;
	}
	
	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getPrice($product_id,$quantity=1){
		$price=0;
		$product_discount_query=$this->db->query("SELECT price FROM ".DB_PREFIX."product_discount WHERE product_id = '".(int)$product_id."' AND customer_group_id = '".(int)$this->config->get('config_customer_group_id')."' AND quantity <= '".(int)$quantity."' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");
		if($product_discount_query->num_rows){
			$price=$product_discount_query->row['price'];
		}
		return $price;
	}

	public function conversionapi($eventName, $data, $user_data, $eventID = '') {
		$current_url = $data['source_url'];
		$access_token = $this->config->get('module_me_fb_events_access_token');
		$pixel_id = $this->config->get('module_me_fb_events_pixel_id');

		$user_data = array_merge($user_data, [
	        "client_ip_address" => $_SERVER['REMOTE_ADDR'],
	        "client_user_agent" => $_SERVER['HTTP_USER_AGENT'],
	        "fbp" => isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : '',
	        "fbc" => isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : ''
	    ]);

		$graph_url = "https://graph.facebook.com/v15.0/{$pixel_id}/events?access_token={$access_token}";

		$custom_data = array(
			'currency' => $data['currency'],
		);
		if (isset($data['value'])) {
			$custom_data['value'] = $data['value'];
		}
		if (isset($data['product'])) {
			$custom_data['contents'] = $data['product'];
			$custom_data['content_type'] = 'product';
		}
		if (isset($data['content_name'])) {
			$custom_data['content_name'] = $data['content_name'];
		}
		if (isset($data['content_ids'])) {
			$custom_data['content_ids'] = $data['content_ids'];
			$custom_data['content_type'] = 'product';
		}
		if (isset($data['num_items'])) {
			$custom_data['num_items'] = $data['num_items'];
		}

		$postData = array( // main object
    	"data" => array( // data array
	        array(
	            "event_name" => $eventName,
	            "event_time" => time(),
	            "event_id" => $eventID,
	               "user_data" => $user_data,
	               	"custom_data" => $custom_data,
	                "action_source" => "website",
	                "event_source_url"  => $current_url,
	       ),
	    ),
	    "test_event_code" => $this->config->get('module_me_fb_events_test_event') ? $this->config->get('module_me_fb_events_test_event') : ''
	    ); 
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $graph_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
		    "cache-control: no-cache",
		    "Accept: application/json"  ),
		);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output = curl_exec($ch);

        curl_close($ch);

        //echo $output; die();
	}
}
