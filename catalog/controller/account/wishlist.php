<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt 

class ControllerAccountWishList extends Controller {
	public function index() {
		/* Noir
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/wishlist', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		*/
		$this->load->language('account/wishlist');

		$this->load->model('account/wishlist');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
	
		if (isset($this->request->get['remove'])) {
			// Remove Wishlist
			//$this->model_account_wishlist->deleteWishlist($this->request->get['remove']);
			// < Noir
			$product_id = (int)$this->request->get['remove'];
			if ($this->customer->isLogged()) { 
				$this->model_account_wishlist->deleteWishlist($product_id);
			}
			if(!empty($this->session->data['wishlist'])) {
				$this->session->data['wishlist'] = array_diff($this->session->data['wishlist'], [$product_id]);
			}
			// Noir >
			$this->session->data['success'] = $this->language->get('text_remove');

			$this->response->redirect($this->url->link('account/wishlist'));
		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		/* Noir
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		*/
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/wishlist')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['products'] = array();

		//$results = $this->model_account_wishlist->getWishlist();
		// < Noir
		if ($this->customer->isLogged()) { 
			$results = $this->model_account_wishlist->getNrWishlist();
			$this->session->data['wishlist'] = $results;
		} elseif(!empty($this->session->data['wishlist'])) {
			$results = $this->session->data['wishlist'];
		} else {
			$results = array();
		}
		$data['is_wishlist'] = 1;
		// Noir >

		foreach ($results as $result) {
			//$product_info = $this->model_catalog_product->getProduct($result['product_id']);
			$product_info = $this->model_catalog_product->getProduct($result); //Noir
			
			if ($product_info) {
				if ($product_info['image']) {
					$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
				} else {
					$image = false;
				}
				if ($product_info['image1']) { // Noir
					$image1 = $this->model_tool_image->resize($product_info['image1'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
				} else {
					$image1 = false;
				}

				if ($product_info['quantity'] <= 0) {
					$stock = $product_info['stock_status'];
				} elseif ($this->config->get('config_stock_display')) {
					$stock = $product_info['quantity'];
				} else {
					$stock = $this->language->get('text_instock');
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				$data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'thumb'      => $image,
					'thumb1'      => $image1, //Noir
					'name'       => $product_info['name'],
					'model'      => $product_info['model'],
					'stock'      => $stock,
					'price'      => $price,
					'special'    => $special,
					'rating'      => $product_info['rating'], //Noir
					'quantity' => $product_info['quantity'], // Noir
					'intro' => $product_info['meta_h1'], // Noir
					'href'       => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
					'remove'     => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
				);
			} else {
				//$this->model_account_wishlist->deleteWishlist($result['product_id']);
				// < Noir
				if ($this->customer->isLogged()) { 
					$this->model_account_wishlist->deleteWishlist($result['product_id']);
				}
				if(!empty($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array_diff($this->session->data['wishlist'], [$result['product_id']]);
				}
				// Noir >
			}
		}

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wishlist', $data));
	}

	public function add() {
		$this->load->language('account/wishlist');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if ($this->customer->isLogged()) {
				// Edit customers cart
				$this->load->model('account/wishlist');

				$this->model_account_wishlist->addWishlist($this->request->post['product_id']);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			} else {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$this->session->data['wishlist'][] = $this->request->post['product_id'];

				$this->session->data['wishlist'] = array_unique($this->session->data['wishlist']);

				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true), $this->url->link('product/product', 'product_id=' . (int)$this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));

				$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nrWishlist() // Noir
	{
		$product_id = $this->request->post['product_id'];
		$this->load->model('account/wishlist');
		$json = array();
		
		if(isset($this->session->data['wishlist'])) {
			$wishlist = $this->session->data['wishlist'];
		} elseif ($this->customer->isLogged()) {
			$wishlist = $this->model_account_wishlist->getNrWishlist();
		}
		if(empty($wishlist)) {
			$wishlist = [$product_id];
			$add = 1;
		} else {
			if(in_array($product_id, $wishlist)) {
				$wishlist = array_diff($wishlist, [$product_id]);
				$add = 0;
			} else {
				$wishlist[] = $product_id;
				$add = 1;
			}
		}
		
		$json['total'] = count($wishlist);
		$this->session->data['wishlist'] = $wishlist;
		if ($this->customer->isLogged()) {
			if($add) {
				$this->model_account_wishlist->addWishlist($product_id);
			} else {
				$this->model_account_wishlist->deleteWishlist($product_id);
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function nrDelete() //Noir
	{
		$product_id = $this->request->get['product_id'];
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			$this->model_account_wishlist->deleteWishlist($product_id);
			if(empty($this->session->data['wishlist'])) {
				$this->session->data['wishlist'] = $this->model_account_wishlist->getNrWishlist();
			} else {
				$this->session->data['wishlist'] = array_diff($this->session->data['wishlist'], [$product_id]);
			}
		} else {
			if(!empty($this->session->data['wishlist'])) $this->session->data['wishlist'] = array_diff($this->session->data['wishlist'], [$product_id]);
		}
		exit('1');
	}
}
