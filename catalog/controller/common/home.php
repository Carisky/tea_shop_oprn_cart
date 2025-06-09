<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_meta_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));
		$this->document->setKeywords($this->config->get('config_meta_keyword'));

		if (isset($this->request->get['route'])) {
			$canonical = $this->url->link('common/home');
			if ($this->config->get('config_seo_pro') && !$this->config->get('config_seopro_addslash')) {
				$canonical = rtrim($canonical, '/');
			}
			$this->document->addLink($canonical, 'canonical');
		}

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$schema = [
			'@context'        => 'http://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => []
		];

		foreach ($data['breadcrumbs'] as $i => $crumb) {
			$schema['itemListElement'][] = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'item'     => [
					'@id'  => $crumb['href'],
					'name' => $crumb['text']
				]
			];
		}

		$this->document->setSchema($schema);

		$data['column_left']   = $this->load->controller('common/column_left');
		$data['column_right']  = $this->load->controller('common/column_right');
		$data['content_top']   = $this->load->controller('common/content_top');
		$data['content_bottom']= $this->load->controller('common/content_bottom');
		$data['footer']        = $this->load->controller('common/footer');
		$data['header']        = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}

}