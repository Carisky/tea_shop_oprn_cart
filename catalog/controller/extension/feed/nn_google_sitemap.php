<?php
class ControllerExtensionFeedNNGoogleSitemap extends Controller {
	public function index() {
		if (!$this->config->get('feed_nn_google_sitemap_status')) return; 
    
    if ($this->config->get('feed_nn_google_sitemap_login') && $this->config->get('feed_nn_google_sitemap_password')) {
      header('Cache-Control: no-cache, must-revalidate, max-age=0');
      if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="NN"');
        header('HTTP/1.1 401 Unauthorized');
        echo 'HTTP authentication required!';
        exit;
      } else {
        if ($_SERVER['PHP_AUTH_USER'] != $this->config->get('feed_nn_google_sitemap_login') || $_SERVER['PHP_AUTH_PW'] != $this->config->get('feed_nn_google_sitemap_password')) {
          header('WWW-Authenticate: Basic realm="NN"');
          header('HTTP/1.1 401 Unauthorized User');
          echo 'Invalid login!';
          exit;
        }
      }
    }

    $output  = '<?xml version="1.0" encoding="UTF-8"?>';
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

    if (isset($this->request->get['catalog'])) {
      $catalogs = explode(',', $this->request->get['catalog']);
    }

    if (!empty($catalogs) && in_array('product', $catalogs) || !isset($catalogs)) {
      $this->load->model('catalog/product');
      $this->load->model('tool/image');

      $products = $this->model_catalog_product->getProducts();

      foreach ($products as $product) {
        $output .= '<url>';
        $output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</loc>';
        $output .= '  <changefreq>weekly</changefreq>';
        $output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
        $output .= '  <priority>1.0</priority>';

        if ($product['image']) {
          $output .= '  <image:image>';
          $output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')) . '</image:loc>';
          $output .= '  <image:caption>' . htmlspecialchars($product['name'], ENT_XML1, 'UTF-8') . '</image:caption>';
          $output .= '  <image:title>' . htmlspecialchars($product['name'], ENT_XML1, 'UTF-8') . '</image:title>';
          $output .= '  </image:image>';
        }

        $output .= '</url>';
      }
    }

    if (!empty($catalogs) && in_array('category', $catalogs) || !isset($catalogs)) {
      $this->load->model('catalog/category');

      $output .= $this->getCategories(0);
    }

    if (!empty($catalogs) && in_array('manufacturer', $catalogs) || !isset($catalogs)) {
      $this->load->model('catalog/manufacturer');

      $manufacturers = $this->model_catalog_manufacturer->getManufacturers();

      foreach ($manufacturers as $manufacturer) {
        $output .= '<url>';
        $output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>';
        $output .= '  <changefreq>weekly</changefreq>';
        $output .= '  <priority>0.7</priority>';
        $output .= '</url>';
      }
    }


    if (!empty($catalogs) && in_array('information', $catalogs) || !isset($catalogs)) {
      $this->load->model('catalog/information');

      $informations = $this->model_catalog_information->getInformations();

      foreach ($informations as $information) {
        $output .= '<url>';
        $output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id']) . '</loc>';
        $output .= '  <changefreq>weekly</changefreq>';
        $output .= '  <priority>0.5</priority>';
        $output .= '</url>';
      }
    }
    
    $output .= '</urlset>';

    $this->response->addHeader('Content-Type: application/xml');
    $this->response->setOutput($output);
	}

	protected function getCategories($parent_id) {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			$output .= '<url>';
			$output .= '  <loc>' . $this->url->link('product/category', 'path=' . $result['category_id']) . '</loc>';
			$output .= '  <changefreq>weekly</changefreq>';
			$output .= '  <priority>0.7</priority>';
			$output .= '</url>';

			$output .= $this->getCategories($result['category_id']);
		}

		return $output;
	}
}
