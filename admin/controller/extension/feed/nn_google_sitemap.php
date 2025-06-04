<?php
class ControllerExtensionFeedNNGoogleSitemap extends Controller {
  private $version = '1.3';
  private $version_date = '2024-04-23';
  private $version_change = 'Fix login details and parse XML characters.';
  private $update = 'https://shop.nanet.uk/opencart-google-sitemap';

  public function info() {
    return [
      'version'        => $this->version,
      'version_date'   => $this->version_date,
      'version_change' => $this->version_change,
      'update'         => $this->update,
    ];
  }

	private $error = array();

	public function index() {
		$this->load->language('extension/feed/nn_google_sitemap');

    $this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('feed_nn_google_sitemap', $this->request->post);

      $this->backup();

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/feed/nn_google_sitemap', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/feed/nn_google_sitemap', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);

		if (isset($this->request->post['feed_nn_google_sitemap_status'])) {
			$data['feed_nn_google_sitemap_status'] = $this->request->post['feed_nn_google_sitemap_status'];
		} else {
			$data['feed_nn_google_sitemap_status'] = $this->config->get('feed_nn_google_sitemap_status');
		}

		if (isset($this->request->post['feed_nn_google_sitemap_login'])) {
			$data['feed_nn_google_sitemap_login'] = $this->request->post['feed_nn_google_sitemap_login'];
		} else {
			$data['feed_nn_google_sitemap_login'] = $this->config->get('feed_nn_google_sitemap_login');
		}		
    
    if (isset($this->request->post['feed_nn_google_sitemap_password'])) {
			$data['feed_nn_google_sitemap_password'] = $this->request->post['feed_nn_google_sitemap_password'];
		} else {
			$data['feed_nn_google_sitemap_password'] = $this->config->get('feed_nn_google_sitemap_password');
		}
    
    if (isset($this->request->post['feed_nn_google_sitemap_product'])) {
			$data['feed_nn_google_sitemap_product'] = $this->request->post['feed_nn_google_sitemap_product'];
		} else {
			$data['feed_nn_google_sitemap_product'] = $this->config->get('feed_nn_google_sitemap_product');
		}
    
    if (isset($this->request->post['feed_nn_google_sitemap_category'])) {
			$data['feed_nn_google_sitemap_category'] = $this->request->post['feed_nn_google_sitemap_category'];
		} else {
			$data['feed_nn_google_sitemap_category'] = $this->config->get('feed_nn_google_sitemap_category');
		}
    
    if (isset($this->request->post['feed_nn_google_sitemap_manufacturer'])) {
			$data['feed_nn_google_sitemap_manufacturer'] = $this->request->post['feed_nn_google_sitemap_manufacturer'];
		} else {
			$data['feed_nn_google_sitemap_manufacturer'] = $this->config->get('feed_nn_google_sitemap_manufacturer');
		}
    
    if (isset($this->request->post['feed_nn_google_sitemap_information'])) {
			$data['feed_nn_google_sitemap_information'] = $this->request->post['feed_nn_google_sitemap_information'];
		} else {
			$data['feed_nn_google_sitemap_information'] = $this->config->get('feed_nn_google_sitemap_information');
		}

		$data['data_feed_url'] = HTTP_CATALOG . 'index.php?route=extension/feed/nn_google_sitemap';
		$data['data_feed_seo'] = HTTP_CATALOG . 'sitemap.xml';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/nn_google_sitemap', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/feed/nn_google_sitemap')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

  private function backup() {
    $this->load->model('setting/setting');
    $json['settings'] = $this->model_setting_setting->getSetting('feed_nn_google_sitemap');

    $path = DIR_STORAGE . 'nn/extension_feed_nn_google_sitemap.json';
    if (!file_exists(DIR_STORAGE . 'nn/')) {
      mkdir(DIR_STORAGE . 'nn/', 0777, true);
    }

    file_put_contents($path, json_encode($json));
  }

  private function restore() {
    $this->load->model('setting/setting');
    $path = DIR_STORAGE . 'nn/extension_feed_nn_google_sitemap.json';

    if (is_file($path)) {
      $backup = json_decode(file_get_contents($path, true), true);

      if (!empty($backup['settings'])) {
        $this->model_setting_setting->editSetting('feed_nn_google_sitemap', $backup['settings']);
      }
    }
  }

  public function import() {
    $this->load->language('extension/feed/nn_google_sitemap');

		$json = [];

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'extension/feed/nn_google_sitemap')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
				// Sanitize the filename
				$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

				// Allowed file extension types
				if (strtolower(substr(strrchr($filename, '.'), 1)) != 'json') {
					$json['error'] = $this->language->get('error_filetype');
				}

        // Allowed file mime types
				if ($this->request->files['file']['type'] != 'application/json') {
					$json['error'] = $this->language->get('error_filetype');
				}

				// Return any upload error
				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_import_success');

			// Get the contents of the uploaded file
			$content = file_get_contents($this->request->files['file']['tmp_name']);

      $path = DIR_STORAGE . 'nn/extension_feed_nn_google_sitemap.json';
      if (!file_exists(DIR_STORAGE . 'nn/')) {
        mkdir(DIR_STORAGE . 'nn/', 0777, true);
      }
  
      file_put_contents($path, $content);

			unlink($this->request->files['file']['tmp_name']);

      $this->restore();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

  public function export() {
    $path = DIR_STORAGE . 'nn/extension_feed_nn_google_sitemap.json';

    $content = (is_file($path) ? file_get_contents($path) : '');

    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"extension_feed_nn_google_sitemap.json\"");

    echo $content;

    exit;
  }

  public function install() {
    $this->load->model('design/seo_url');
    $this->model_design_seo_url->addSeoUrl(array(
      'store_id'    => $this->config->get('config_store_id'),
      'language_id' => $this->config->get('config_language_id'),
      'query'       => 'extension/feed/nn_google_sitemap',
      'keyword'     => 'sitemap.xml',
    ));

    $this->restore();
  }

  public function uninstall() {
    $this->load->model('design/seo_url');
    $seo_urls = $this->model_design_seo_url->getSeoUrls([
      'filter_keyword'  => 'sitemap.xml',
      'filter_store_id' => $this->config->get('config_store_id'),
      'filter_language_id' => $this->config->get('config_language_id'),
    ]);
    
    foreach ($seo_urls as $seo) {
      $this->model_design_seo_url->deleteSeoUrl($seo['seo_url_id']);
    }
  }
}