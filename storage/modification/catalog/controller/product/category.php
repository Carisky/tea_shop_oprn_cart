<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerProductCategory extends Controller {
	public function index() {
		$this->load->language('product/category');

        /* Custom Pagination */
        $this->document->addStyle('catalog/view/css/load-format-pagination.css');

        $this->document->addScript('catalog/view/javascript/load-format-pagination.js');
      

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');


		$data['text_empty'] = $this->language->get('text_empty');

        if ($this->config->get('config_noindex_disallow_params')) {
            $params = explode ("\r\n", $this->config->get('config_noindex_disallow_params'));
            if(!empty($params)) {
                $disallow_params = $params;
            }
        }

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
			if (!in_array('filter', $disallow_params, true) && $this->config->get('config_noindex_status')){
                $this->document->setRobots('noindex,follow');
            }
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
            if (!in_array('sort', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
            if (!in_array('order', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
            if (!in_array('page', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
            if (!in_array('limit', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}


		// OCFilter start
    if (isset($this->request->get['filter_ocfilter'])) {
      $filter_ocfilter = $this->request->get['filter_ocfilter'];
    } else {
      $filter_ocfilter = '';
    }
		// OCFilter end
      
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

						// 1. Определяем корневой ID для карусели
			$path_arr = isset($this->request->get['path'])
				? explode('_', (string)$this->request->get['path'])
				: [];
			$root_id = !empty($path_arr)
				? (int)$path_arr[0]
				: $category_id;

			// 2. Загружаем подкатегории корня
			$carousel_raw = $this->model_catalog_category->getCategories($root_id);

			// 3. Фильтруем текущую категорию
			$data['carousel_categories'] = [];
			foreach ($carousel_raw as $cat) {
				if ($cat['category_id'] == $category_id) {
					continue;
				}
				$data['carousel_categories'][] = [
					'name' => $cat['name'],
					'href' => $this->url->link(
						'product/category',
						'path=' . $root_id . '_' . $cat['category_id']
					),
					'thumb' => $cat['image']
						? $this->model_tool_image->resize(
							$cat['image'],
							$this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),
							$this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')
						)
						: ''
				];
			}


			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {

			if ($category_info['meta_title']) {
				$this->document->setTitle($category_info['meta_title']);
			} else {
				$this->document->setTitle($category_info['name']);
			}

			if ($category_info['noindex'] <= 0 && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
			/* Noir
			if ($category_info['meta_h1']) {
				$data['heading_title'] = $category_info['meta_h1'];
			} else {
				$data['heading_title'] = $category_info['name'];
			}
			*/
			$data['heading_title'] = $category_info['name']; //Noir
			$data['title'] = $category_info['meta_h1']; //Noir

			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if ($category_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['compare'] = $this->url->link('product/compare');

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

			$data['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);


  		// OCFilter start
  		$filter_data['filter_ocfilter'] = $filter_ocfilter;

      if ($this->config->get('module_ocfilter_sub_category')) {
        if (empty($filter_data['filter_sub_category'])) {
          $filter_data['filter_sub_category'] = true;
        }

        if (isset($this->request->get['filter_ocfilter'])) {
          $data['categories'] = array();
        }
      }
  		// OCFilter end
      
			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);


        /** Load Format Pagination **/
        $data['ttl'] = $product_total;
        $data['config_catalog_limit'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        $data['category_data'] = $category_id;
        $data['path'] = $this->request->get['path'];
        $data['url_category'] = $this->url->link('extension/module/load_format_pagination');
        $data['page'] = $page;
        $data['filter'] = $filter;
      
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
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				// 1) Получаем все опции-радио для фасовки
				$options = [];
				foreach ($this->model_catalog_product->getProductOptions($result['product_id']) as $option) {
					if ($option['type'] === 'radio') {
						$values = [];
						foreach ($option['product_option_value'] as $ov) {
							if (!$ov['subtract'] || $ov['quantity'] > 0) {
								$values[] = [
									'product_option_value_id' => $ov['product_option_value_id'],
									'name'                    => $ov['name'],
								];
							}
						}
						if ($values) {
							$options = $values;
						}
						break; // если только одна опция фасовки
					}
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
					//'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					//'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'quantity' => $result['quantity'], // Noir
					'intro' => $result['meta_h1'], // Noir
					'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url),
					'options'    => $options,
				);
			}	

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
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

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

            if (!$this->config->get('config_canonical_method')) {
                // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
                if ($page == 1) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
                } elseif ($page == 2) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'prev');
                } else {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page - 1)), 'prev');
                }

                if ($limit && ceil($product_total / $limit) > $page) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page + 1)), 'next');
                }
            } else {

                if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                    $server = $this->config->get('config_ssl');
                } else {
                    $server = $this->config->get('config_url');
                };

                $request_url = rtrim($server, '/') . $this->request->server['REQUEST_URI'];
                $canonical_url = $this->url->link('product/category', 'path=' . $category_info['category_id']);

                if (($request_url != $canonical_url) || $this->config->get('config_canonical_self')) {
                    $this->document->addLink($canonical_url, 'canonical');
                }

                if ($this->config->get('config_add_prevnext')) {

                    if ($page == 2) {
                        $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'prev');
                    } elseif ($page > 2)  {
                        $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page - 1)), 'prev');
                    }

                    if ($limit && ceil($product_total / $limit) > $page) {
                        $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page + 1)), 'next');
                    }
                }
            }
			// получаем «сырые» записи из БД
			$data['faqs'] = $this->model_catalog_category->getCategoryFaq($category_id);

			// декодируем HTML-сущности обратно в теги и эмоджи
			foreach ($data['faqs'] as &$faq) {
				$faq['question'] = html_entity_decode(
					$faq['question'],
					ENT_QUOTES,
					'UTF-8'
				);
				$faq['answer'] = html_entity_decode(
					$faq['answer'],
					ENT_QUOTES,
					'UTF-8'
				);
			}
			unset($faq);

			// дальше вы собираете HTML-шаблон
			$data['faq'] = $this->load->view('common/faq', $data);
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

      // OCFilter Start
      if ($this->ocfilter->getParams()) {
        if (isset($product_total) && !$product_total) {
      	  $this->response->redirect($this->url->link('product/category', 'path=' . $this->request->get['path']));
        }

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = '';
        }

        if (isset($data['description_2'])) {
          $data['description_2'] = '';
        }

        if (isset($data['description'])) {
          $data['description'] = '';
        }

        if (isset($data['ext_description'])) {
          $data['ext_description'] = '';
        }

        $this->document->setTitle($this->ocfilter->getPageMetaTitle($this->document->getTitle()));
			  $this->document->setDescription($this->ocfilter->getPageMetaDescription($this->document->getDescription()));
        $this->document->setKeywords($this->ocfilter->getPageMetaKeywords($this->document->getKeywords()));

        $data['heading_title'] = $data['seo_h1'] = $this->ocfilter->getPageHeadingTitle($data['heading_title']);

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['description_2'])) {
          $data['description_2'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['description'])) {
          $data['description'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['ext_description'])) {
          $data['ext_description'] = $this->ocfilter->getPageDescription();
        }

        if (!trim(strip_tags(html_entity_decode($data['description'], ENT_QUOTES, 'UTF-8')))) {
        	$data['thumb'] = '';
        }

        $breadcrumb = $this->ocfilter->getPageBreadCrumb();

        if ($breadcrumb) {
  			  $data['breadcrumbs'][] = $breadcrumb;
        }

        $this->document->deleteLink('canonical');
        $this->document->deleteLink('prev');
        $this->document->deleteLink('next');

        if ($page > 1) {
          $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'canonical');
        }

  			if ($page == 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'prev');
  			} else if ($page > 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page - 1), true), 'prev');
  			}

  			if ($limit && ceil($product_total / $limit) > $page) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page + 1), true), 'next');
  			}
      }
      // OCFilter End
      
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

      // OCFilter Start
      if ($this->ocfilter->getParams()) {
        if (isset($product_total) && !$product_total) {
      	  $this->response->redirect($this->url->link('product/category', 'path=' . $this->request->get['path']));
        }

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = '';
        }

        if (isset($data['description_2'])) {
          $data['description_2'] = '';
        }

        if (isset($data['description'])) {
          $data['description'] = '';
        }

        if (isset($data['ext_description'])) {
          $data['ext_description'] = '';
        }

        $this->document->setTitle($this->ocfilter->getPageMetaTitle($this->document->getTitle()));
			  $this->document->setDescription($this->ocfilter->getPageMetaDescription($this->document->getDescription()));
        $this->document->setKeywords($this->ocfilter->getPageMetaKeywords($this->document->getKeywords()));

        $data['heading_title'] = $data['seo_h1'] = $this->ocfilter->getPageHeadingTitle($data['heading_title']);

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['description_2'])) {
          $data['description_2'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['description'])) {
          $data['description'] = $this->ocfilter->getPageDescription();
        } else if (isset($data['ext_description'])) {
          $data['ext_description'] = $this->ocfilter->getPageDescription();
        }

        if (!trim(strip_tags(html_entity_decode($data['description'], ENT_QUOTES, 'UTF-8')))) {
        	$data['thumb'] = '';
        }

        $breadcrumb = $this->ocfilter->getPageBreadCrumb();

        if ($breadcrumb) {
  			  $data['breadcrumbs'][] = $breadcrumb;
        }

        $this->document->deleteLink('canonical');
        $this->document->deleteLink('prev');
        $this->document->deleteLink('next');

        if ($page > 1) {
          $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'canonical');
        }

  			if ($page == 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'prev');
  			} else if ($page > 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page - 1), true), 'prev');
  			}

  			if ($limit && ceil($product_total / $limit) > $page) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page + 1), true), 'next');
  			}
      }
      // OCFilter End
      

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');

$schema = array('@context' => 'http://schema.org');
$schema['@type'] = 'BreadcrumbList';
$number = 1;
foreach ($data['breadcrumbs'] as $breadcrumb) {
    if ($number == 1) {
        $text = 'Home';
    } else {
        $text = $breadcrumb['text'];
    }
    $schema['itemListElement'][] = array(
        '@type'    => 'ListItem',
        'position' => $number,
        'item'     => array('@id' => $breadcrumb['href'], 'name' => $text)
    );
    $number++;
}
$this->document->setSchema($schema);
            
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			$data['wishlist'] = $this->model_catalog_product->getWishlist(); // Noir
			$data['filter_selected'] = $this->load->controller('extension/module/ocfilter/nrGetSelection'); //Noir
			

        /** Load Format Pagination **/
        $this->load->language('extension/module/load_format_pagination');
        $data['load_more'] = $this->language->get('load_more');
        $data['show_product'] = $this->language->get('show_product');
      
			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');

$schema = array('@context' => 'http://schema.org');
$schema['@type'] = 'BreadcrumbList';
$number = 1;
foreach ($data['breadcrumbs'] as $breadcrumb) {
    if ($number == 1) {
        $text = 'Home';
    } else {
        $text = $breadcrumb['text'];
    }
    $schema['itemListElement'][] = array(
        '@type'    => 'ListItem',
        'position' => $number,
        'item'     => array('@id' => $breadcrumb['href'], 'name' => $text)
    );
    $number++;
}
$this->document->setSchema($schema);
            
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
