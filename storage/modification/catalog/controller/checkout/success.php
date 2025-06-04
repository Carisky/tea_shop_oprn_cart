<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutSuccess extends Controller
{
	public function index()
	{
		$this->load->language('checkout/success');


				$data['order_id'] = '';
				$data['total'] = '';
				$data['fbproducts'] = array();
				$data['currency_code'] = $this->session->data['currency'];
				$data['tax'] = '';
				$data['store_name'] = $this->config->get('config_name'); 
			

				$data['me_fb_events_status'] = $this->config->get('module_me_fb_events_status');
				$data['me_fb_events_pixel_id'] = $this->config->get('module_me_fb_events_pixel_id');
				$data['me_fb_events_track_purchase'] = $this->config->get('module_me_fb_events_track_purchase');
				if (isset($this->session->data['order_id'])) {
			        $order_id = $this->session->data['order_id'];
		        }else if(isset($this->session->data['xsuccess_order_id'])){
			        $order_id = $this->session->data['xsuccess_order_id'];
		        }else{
			         $order_id = 0;
		        }
				$meorder_id = $order_id;
				$data['order_id'] = $order_id;
				$this->load->model('catalog/product');
				$this->load->model('catalog/category');
				$this->load->model('account/order');
				$this->load->model('checkout/order');
				if($this->customer->isLogged()){
					$order_info = $this->model_account_order->getOrder($meorder_id);
				}else{
					$order_info = $this->model_checkout_order->getOrder($meorder_id);
				}
				$data['total'] = $order_info['total'];
				$data['tax'] = 0;
				$taxes = $this->cart->getTaxes();
				foreach ($taxes as $key => $value) {
					if ($value > 0) {
						$data['tax'] += $value;
					}
				}
				$data['currency_code'] = $order_info['currency_code'];
				
				$data['fbproducts'] = array();
				$fbproducts = $this->model_account_order->getOrderProducts($meorder_id);
				foreach ($fbproducts as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					if ($product_info) {
						$reorder = $this->url->link('account/order/reorder', 'order_id=' . $meorder_id . '&order_product_id=' . $product['order_product_id'], true);
					} else {
						$reorder = '';
					}
					
					$getcategories = $this->model_catalog_product->getCategories($product['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? html_entity_decode($getcategory['name']) : '';
						}
					}
					$data['total_quantity'] = 0;
					$categories = implode(", ", $categories);
					$data['total_quantity'] += $product['quantity'];
					$data['fbproducts'][] = array(
						'id'       => $product['product_id'],
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value'], false),
					);
				}
				$data['event_id'] = 'Purchase.'.time();
				if ($this->config->get('module_me_fb_events_status') && $this->config->get('module_me_fb_events_pixel_id') && $this->config->get('module_me_fb_events_access_token') && $this->config->get('module_me_fb_events_track_purchase')) {
				    $this->load->model('extension/me_fb_events');
				    if ($this->request->server['HTTPS']) {
            			$server = $this->config->get('config_ssl');
            		} else {
            			$server = $this->config->get('config_url');
            		}
				    $custom_data = array(
				        'currency' => $data['currency_code'],
				        'value' => $data['total'],
				        'product' => $data['fbproducts'],
				        'num_items' => count($data['fbproducts']),
				        'source_url' => $server . $_SERVER["REQUEST_URI"]
				    );
				    
				    $user_data = array(
				        'fn' => hash('sha256', $order_info['firstname']),
				        'ln' => hash('sha256', $order_info['lastname']),
				        'em' => hash('sha256', $order_info['email']),
				        'ph' => hash('sha256', $order_info['telephone'])
				    );
				    
				    $this->model_extension_me_fb_events->conversionapi('Purchase', $custom_data, $user_data, $data['event_id']);
				}
			
		if (isset($this->session->data['order_id'])) {
			$this->session->data['last_order_id'] = $this->session->data['order_id'];
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		if (!empty($this->session->data['last_order_id'])) {
			$this->document->setTitle(sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']));
			$this->document->setRobots('noindex,follow');
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
			$this->document->setRobots('noindex,follow');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if (!empty($this->session->data['last_order_id'])) {
			$data['heading_title'] = sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']);
		} else {
			$data['heading_title'] = $this->language->get('heading_title');
		}

		if ($this->customer->isLogged() && !empty($this->session->data['last_order_id'])) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/order/info&order_id=' . $this->session->data['last_order_id'], '', true), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('information/contact'), $this->url->link('product/special'), $this->session->data['last_order_id'], $this->url->link('account/download', '', true));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		// === ДОБАВЛЕНО: Проверка метода оплаты и вставка банковского текста ===
		$this->load->model('checkout/order');
		$order_id = $this->session->data['last_order_id'];
		$order = $this->model_checkout_order->getOrder($order_id);
		if ($order && $order['payment_code'] == 'bank_transfer') {
			$firstname = $order['firstname'];
			$lastname = $order['lastname'];
			$text = $this->language->get('text_bank_transfer_info');
			$text = str_replace('{firstname}', $firstname, $text);
			$text = str_replace('{lastname}', $lastname, $text);
			$data['bank_transfer_info'] = $text;
		} else {
			$data['bank_transfer_info'] = '';
		}
		// ====================================================================

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}
}
