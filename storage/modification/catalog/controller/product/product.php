<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

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
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

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

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			if ($product_info['meta_title']) {
				$this->document->setTitle($product_info['meta_title']);
			} else {
				$this->document->setTitle($product_info['name']);
			}
			
			if ($product_info['noindex'] <= 0 && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
			/*
			if ($product_info['meta_h1']) {
				$data['heading_title'] = $product_info['meta_h1'];
			} else {
				$data['heading_title'] = $product_info['name'];

$this->load->model('tool/image');
$this->load->model('catalog/review');
$schema = array('@context' => 'http://schema.org');
$schema['@type'] = 'Product';
$schema['name'] = $product_info['name'];
if ($product_info['meta_description']) {
    $schema['description'] = $product_info['meta_description'];
}
if ($product_info['image']) {
    $schema['image'] = $this->model_tool_image->resize(
        $product_info['image'],
        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'),
        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')
    );
    $this->document->setOgImage($schema['image']);
}
if ($product_info['sku']) {
    $schema['sku'] = $product_info['sku'];
}
if ($product_info['ean']) {
    $schema['gtin'] = $product_info['ean'];
}
if ($product_info['model']) {
    $schema['model'] = $product_info['model'];
}
$schema['offers'] = array('@type' => 'Offer');
if ($product_info['special']) {
    $schema['offers']['price'] = round(floatval($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))), 2);
} else {
    $schema['offers']['price'] = round(floatval($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))), 2);
}
$schema['offers']['priceCurrency'] = $this->config->get('config_currency');
$schema['offers']['url'] = $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']);
if ($product_info['quantity'] >= 0) {
    $schema['offers']['availability'] = 'http://schema.org/InStock';
} else {
    $schema['offers']['availability'] = 'http://schema.org/OutOfStock';
}
if ($product_info['special_date_end']) {
    $schema['offers']['priceValidUntil'] = $product_info['special_date_end'];
} else {
    $schema['offers']['priceValidUntil'] = date('Y-m-d', strtotime('+1 year'));
}
if ((int)$product_info['reviews'] > 0) {
    $schema['aggregateRating'] = array(
        '@type'       => 'AggregateRating',
        'ratingValue' => (int)$product_info['rating'],
        'reviewCount' => (int)$product_info['reviews']
    );
}
if ($product_info['manufacturer']) {
    $schema['brand'] = array(
        '@type' => 'Brand',
        'name'  => $product_info['manufacturer'],
        'url'   => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id'])
    );
}
$reviews = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], 0, 5);
if ($reviews) {
    foreach ($reviews as $review) {
        $schema['review'][] = array(
            '@type'         => 'Review',
            'author'        => array('@type' => 'Person', 'name' => $review['author']),
            'datePublished' => date($this->language->get('date_format_short'), strtotime($review['date_added'])),
            'description'   => strip_tags(html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'))
        );
    }
}
$this->document->setSchema($schema);
unset($schema);

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
            
			}
			*/
			$data['heading_title'] = $product_info['name']; // Noir

$this->load->model('tool/image');
$this->load->model('catalog/review');
$schema = array('@context' => 'http://schema.org');
$schema['@type'] = 'Product';
$schema['name'] = $product_info['name'];
if ($product_info['meta_description']) {
    $schema['description'] = $product_info['meta_description'];
}
if ($product_info['image']) {
    $schema['image'] = $this->model_tool_image->resize(
        $product_info['image'],
        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'),
        $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')
    );
    $this->document->setOgImage($schema['image']);
}
if ($product_info['sku']) {
    $schema['sku'] = $product_info['sku'];
}
if ($product_info['ean']) {
    $schema['gtin'] = $product_info['ean'];
}
if ($product_info['model']) {
    $schema['model'] = $product_info['model'];
}
$schema['offers'] = array('@type' => 'Offer');
if ($product_info['special']) {
    $schema['offers']['price'] = round(floatval($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))), 2);
} else {
    $schema['offers']['price'] = round(floatval($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'))), 2);
}
$schema['offers']['priceCurrency'] = $this->config->get('config_currency');
$schema['offers']['url'] = $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']);
if ($product_info['quantity'] >= 0) {
    $schema['offers']['availability'] = 'http://schema.org/InStock';
} else {
    $schema['offers']['availability'] = 'http://schema.org/OutOfStock';
}
if ($product_info['special_date_end']) {
    $schema['offers']['priceValidUntil'] = $product_info['special_date_end'];
} else {
    $schema['offers']['priceValidUntil'] = date('Y-m-d', strtotime('+1 year'));
}
if ((int)$product_info['reviews'] > 0) {
    $schema['aggregateRating'] = array(
        '@type'       => 'AggregateRating',
        'ratingValue' => (int)$product_info['rating'],
        'reviewCount' => (int)$product_info['reviews']
    );
}
if ($product_info['manufacturer']) {
    $schema['brand'] = array(
        '@type' => 'Brand',
        'name'  => $product_info['manufacturer'],
        'url'   => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id'])
    );
}
$reviews = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], 0, 5);
if ($reviews) {
    foreach ($reviews as $review) {
        $schema['review'][] = array(
            '@type'         => 'Review',
            'author'        => array('@type' => 'Person', 'name' => $review['author']),
            'datePublished' => date($this->language->get('date_format_short'), strtotime($review['date_added'])),
            'description'   => strip_tags(html_entity_decode($review['text'], ENT_QUOTES, 'UTF-8'))
        );
    }
}
$this->document->setSchema($schema);
unset($schema);

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
            
			
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			//$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			//$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			//$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			//$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css'); //Noir
			$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js', 'footer'); //Noir
			

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');
			/* Noir
			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}
			*/

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();
			if($data['thumb']) $data['images'][] = $data['thumb']; //Noir
			$data['desc_images'] = array(); //Noir

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				/* Noir
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
				*/
				// < Noir
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), 
					$this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'), true);
				$data['images'][] = $image;
				if($result['in_desc']) $data['desc_images'][] = $image;
				// Noir >
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);

			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_cart'] = $this->config->get('module_me_fb_events_track_cart');
				$data['me_fb_events_track_wishlist'] = $this->config->get('module_me_fb_events_track_wishlist');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$data['fbproduct'] = array();
				$this->load->model('catalog/category');
			
			$data['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}
				if ($result['image1']) { // Noir
					$image1 = $this->model_tool_image->resize($result['image1'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'), true);
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
					'rating'      => $rating,
					'quantity' => $result['quantity'], // Noir
					'intro' => $result['meta_h1'], // Noir
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();
			/* Noir
			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}
			*/
			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			//product variant
            $data['variantproducts'] = $this->model_catalog_product->getProductVariantproducts($this->request->get['product_id']);

            foreach ($data['variantproducts'] as $k => $variantproduct) {
                if ($variantproduct['products']) {
                    foreach ($variantproduct['products'] as $j => $product) {
                        if ($product['image']) {
                            $image = $this->model_tool_image->resize($product['image'], 60, 60);
                        } else {
                            $image = false;
                        }


                        $product['image'] = $image;
                     
						$product['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);						
                        $data['variantproducts'][$k]['products'][$j] = $product;
                    }
                }
            }
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/variants.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/variants.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/variants.css');
		}		
    //product variant
          $data['column_left'] = $this->load->controller('common/column_left');	
		
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			
			//< Noir
			$data['short_desc'] = html_entity_decode($product_info['tag']);
			$data['sku'] = $product_info['sku'];
			$data['wishlist'] = $this->model_catalog_product->getWishlist();
			$data['desc_images_count'] = count($data['desc_images']);
			$data['logged'] = $this->customer->isLogged();
			
			date_default_timezone_set('Europe/Madrid');
			$t1 = strtotime('today 15:00:00');
			$t2 = strtotime('tomorrow 09:00:00');
			$data['settimer'] = 1;
			// $t0 =  strtotime('today 00:00:01');
			$now = time(); //strtotime("+2 hours"); //$t0
			if($now <= $t1) {
				$data['timer'] = $t1 - $now;
				$data['today'] = 1;
			} else {
				$data['timer'] = $t2 - $now;
				$data['today'] = 0;
				if ( intval(Date("N", time()))==5) $data['settimer'] = 0;
			}
			if ( intval(Date("N", time()))>=6) $data['settimer'] = 0;
			$data['time'] = date('g:i:s', $data['timer']);
			// Noir >


				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_track_content'] = $this->config->get('module_me_fb_events_track_content');
				$this->load->model('catalog/category');
				$data['curencysymbol'] = $this->session->data['currency'];
				if($product_info['special']){
					$wprice = $this->currency->format($product_info['special'], $this->session->data['currency'], '', false);
				}else{
					$wprice = $this->currency->format($product_info['price'], $this->session->data['currency'], '', false);
				}
				
				$getcategories = $this->model_catalog_product->getCategories($product_info['product_id']);
				$categories = array();
				if($getcategories){
					foreach($getcategories as $cate){
						$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
						$categories[] = isset($getcategory['name']) ? html_entity_decode($getcategory['name']) : '';
					}
				}
				$categories = implode(", ", $categories);

				$data['fbproduct_product'] = array(
					'manufacturer' => html_entity_decode($product_info['manufacturer']),
					'categories'   => $categories,
					'price'	  	   => $wprice,
					'product_id'   => $product_info['product_id'],
					'name'         => html_entity_decode($product_info['name'])
				);
				
				$data['getcurrencycode'] = $this->session->data['currency'];
				$data['mefb_price'] = $product_info['price'];
				$data['mefb_special'] = $product_info['special'];
				$data['meta_title'] = html_entity_decode($product_info['meta_title']);
				$data['mefb_sku'] = $product_info['sku'];
				$data['mefb_mpn'] = $product_info['mpn'];
				$data['mefb_href'] = $this->url->link('product/product', 'product_id=' . $product_id);
				$mefb_date = date('y-m-d');
				$data['mefb_date'] = date("Y-m-d",strtotime(date("Y-m-d", strtotime($mefb_date)) . " +12 month"));
				$data['event_id'] = 'ViewContent.'.time();
				if ($this->config->get('module_me_fb_events_status') && $this->config->get('module_me_fb_events_pixel_id') && $this->config->get('module_me_fb_events_access_token') && $this->config->get('module_me_fb_events_track_content')) {
				    $this->load->model('extension/me_fb_events');
				    if ($this->request->server['HTTPS']) {
            			$server = $this->config->get('config_ssl');
            		} else {
            			$server = $this->config->get('config_url');
            		}
				    $custom_data = array(
				        'currency' => $data['getcurrencycode'],
				        'content_ids' => $product_info['product_id'],
				        'value' => $wprice,
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
				    
				    $this->model_extension_me_fb_events->conversionapi('ViewContent', $custom_data, $user_data, $data['event_id']);
				}
			
			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			//product variant
            $data['variantproducts'] = $this->model_catalog_product->getProductVariantproducts($this->request->get['product_id']);

            foreach ($data['variantproducts'] as $k => $variantproduct) {
                if ($variantproduct['products']) {
                    foreach ($variantproduct['products'] as $j => $product) {
                        if ($product['image']) {
                            $image = $this->model_tool_image->resize($product['image'], 60, 60);
                        } else {
                            $image = false;
                        }


                        $product['image'] = $image;
                     
						$product['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);						
                        $data['variantproducts'][$k]['products'][$j] = $product;
                    }
                }
            }
		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/variants.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/variants.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/variants.css');
		}		
    //product variant
          $data['column_left'] = $this->load->controller('common/column_left');	
		
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nr_write() // Noir
	{
		$this->load->language('product/product');

		$json = ['error' => []];

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) or (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error']['name'] = $this->language->get('error_name');
			}
			/*
			if ((utf8_strlen($this->request->post['text']) < 25) or (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error']['text'] = $this->language->get('error_text');
			}
			*/
			if (empty($this->request->post['rating']) or $this->request->post['rating'] < 0 or $this->request->post['rating'] > 5) {
				$json['error']['rating'] = $this->language->get('error_rating');
			}
			
			if (empty($this->request->post['rating1']) or $this->request->post['rating1'] < 0 or $this->request->post['rating1'] > 5) {
				$json['error']['rating1'] = $this->language->get('error_rating');
			}
			
			if (empty($this->request->post['rating2']) or $this->request->post['rating2'] < 0 or $this->request->post['rating2'] > 5) {
				$json['error']['rating2'] = $this->language->get('error_rating');
			}

			if (empty($json['error'])) {
				$this->load->model('catalog/review');
				$this->model_catalog_review->addReview($this->request->post['product_id'], $this->request->post);
				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nr_review() {
		$limit = 8;
		$this->load->model('catalog/review');
		if (isset($this->request->get['page'])) {
			$data['page'] = (int)$this->request->get['page'];
		} else {
			$data['page'] = 1;
		}
		if($data['page'] == 1) $this->load->language('product/product');

		$data['reviews'] = array();
		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);
		$results = $this->model_catalog_review->getNrReviews($this->request->get['product_id'], $data['page'], $limit);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'rating1'     => (int)$result['rating1'],
				'rating2'     => (int)$result['rating2'],
				'date_added' => date('Y-m-d', strtotime($result['date_added']))
			);
		}
		if($data['page'] == 1) {
			$data['more'] = $review_total > $limit;
			$this->response->setOutput($this->load->view('product/review', $data));
		} else {
			$json = [
				'html' => $this->load->view('product/review', $data),
				'more' => ($data['page'] - 1)*$limit + count($results) < $review_total
			];
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
}
