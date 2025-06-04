<?php

	class ControllerExtensionModuleLoadFormatPagination extends Controller {
			public function index() {
				$this->load->language('extension/module/load_format_pagination');

				$this->load->model('catalog/category');

				$this->load->model('catalog/product');

				$this->load->model('tool/image');

				//get data from ajax request
				if (isset($this->request->post['number'])) {
					$number = $this->request->post['number'];
				} else {
					$number = '';
				}

				if (isset($this->request->post['category'])) {
					$category = $this->request->post['category'];
				} else {
					$category = '';
				}

				if (isset($this->request->post['path'])) {
					$path = $this->request->post['path'];
				} else {
					$path = '';
				}

				if (isset($this->request->post['sort'])) {
					$sort = $this->request->post['sort'];
				} else {
					$sort = 'p.sort_order';
				}

				if (isset($this->request->post['filter'])) {
					$filter = $this->request->post['filter'];
				} else {
					$filter = '';
				}

				if (isset($this->request->post['order'])) {
					$order = $this->request->post['order'];
				} else {
					$order = 'ASC';
				}

				if (isset($this->request->post['step'])) {
					$step = $this->request->post['step'];
				} else {
					$step = 1;
				}

				if (isset($this->request->post['tax'])) {
					$tax = $this->request->post['tax'];
				} else {
					$tax = 0;
				}

				if (isset($this->request->post['review_status'])) {
					$review_status = $this->request->post['review_status'];
				} else {
					$review_status = 0;
				}

				if (isset($this->request->post['display'])) {
					$display = $this->request->post['display'];
				} else {
					$display = 'grid';
				}

				if (isset($this->request->post['cols'])) {
					$cols = $this->request->post['cols'];
				} else {
					$cols = '';
				}


				$data['text_tax'] = $this->language->get('text_points');
				$data['button_cart'] = $this->language->get('button_cart');
				$data['display'] = $display;
				$data['cols'] = $cols;


				


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
				$data['products'] = array();

				$filter_data = array(
					'filter_category_id' => $category,
					'filter_filter'      => $filter,
					'sort'               => $sort,
					'order'              => $order,
					'start'              => ($step) * $number,
					'limit'              => $number
				);

				$data['step'] = $step++;

				$product_total = $this->model_catalog_product->getTotalProducts($filter_data);


				$results = $this->model_catalog_product->getProducts($filter_data);


				foreach ($results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
					}
					if ($result['image1']) { // Noir
						$image1 = $this->model_tool_image->resize($result['image1'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), true);
					} else {
						$image1 = '';
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = (int)$result['rating'];
					} else {
						$rating = false;
					}

					$url = '';

					if (isset($this->request->get['filter'])) {
						$url .= '&filter=' . $this->request->get['filter'];
					}

					if (isset($this->request->get['sort'])) {
						$url .= '&sort=' . $this->request->get['sort'];
					}

					if (isset($this->request->get['order'])) {
						$url .= '&order=' . $this->request->get['order'];
					}

					if (isset($this->request->get['limit'])) {
						$url .= '&limit=' . $this->request->get['limit'];
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
			
						'thumb1'       => $image1, //Noir
						'name'        => $result['name'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'minimum'     => ($result['minimum'] > 0) ? $result['minimum'] : 1,
						'rating'      => $result['rating'],
						'quantity' => $result['quantity'], // Noir
						'intro' => $result['meta_h1'], // Noir	
						'href'        => $this->url->link('product/product', 'path=' . $path . '&product_id=' . $result['product_id'] . $url)
					);
				}


				$this->response->setOutput($this->load->view('extension/module/load_format_pagination', $data));
		}

		public function NumberProducts() {
			echo 'NUMBER';
		}

	}
