<?php
class ControllerCommonNrCart extends Controller {
	public function index() 
	{
		$this->load->language('common/cart');
		$data['items'] = $this->cart->countProducts();
		return $this->load->view('common/cart', $data);
	}
		
	public function product_list($tpl = true) 
	{
		$data['products'] = array();
		$this->load->model('setting/extension');
		$this->load->model('tool/image');
		$this->load->model('tool/upload');
		$this->load->model('catalog/product');

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			}
			$product_info = $this->model_catalog_product->getProduct($product['product_id']);
			

			
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
				
				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$option_data = [];
			if (!empty($product['option'])) {
				foreach ($product['option'] as $opt) {
					if (isset($opt['type']) && $opt['type'] === 'radio') {
						$option_data = [[
							'name'  => $opt['name'],
							'value' => $opt['value']
						]];
						break;
					}
				}
			}

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'option'    => $option_data,
				'quantity'  => $product['quantity'],
				'price'     => $price,
				'total'     => $total,
				'minimum'   => $product_info['minimum'],
				'stock'     => $product_info['quantity'],
				'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		if($tpl) {
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
				
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
				);
			}

			$data['cart'] = $this->url->link('checkout/cart');
			$data['checkout'] = $this->url->link('checkout/buy', '', true);
			$data['total'] = $data['totals'][array_key_last($data['totals'])]['text'];
		} 

		return $data;
	}
	
	public function cart_list() 
	{
		$this->response->setOutput($this->load->view('common/cart_list', $this->product_list()));
	}
	
	public function checkout_cart()
	{
		return $this->load->view('checkout/cart', $this->product_list(false));
	}
	
	public function cart_pannel() 
	{
		$data['cart_list'] = $this->load->view('common/cart_list', $this->product_list());
		return $this->load->view('common/cart_pannel', $data);
	}
	
	public function edit() {
		if (empty($this->request->post['cart_id']) or empty($this->request->post['quantity'])) exit();
		$this->cart->update($this->request->post['cart_id'], $this->request->post['quantity']);
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		echo $this->cart->countProducts();
		exit();
	}
	
	public function add() {
		$this->load->language('checkout/cart');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
			//if ($product_id==676) { //AG 14.05.2024
				if (isset($this->request->post['txtcard']) && $this->request->post['txtcard']!='') {
					$this->session->data['txtcard'] = 'Tekst do kartki: '.$this->request->post['txtcard'];
				}
			//}
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			if (isset($this->request->post['recurring_id'])) {
				$recurring_id = $this->request->post['recurring_id'];
			} else {
				$recurring_id = 0;
			}

			if (empty($json['error'])) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			}
		}
		
		$json['number_products'] = $this->cart->countProducts();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function remove() {
		$this->load->language('checkout/cart');

		$json = array();
		if (isset($this->request->post['key'])) {
			$this->cart->remove($this->request->post['key']);
			$json['success'] = $this->language->get('text_remove');

			unset($this->session->data['vouchers'][$this->request->post['key']]);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);
			
			$json['number_products'] = $this->cart->countProducts();
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}