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

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');

$schema = array('@context' => 'http://schema.org');
$schema['@type'] = 'Organization';
$schema['name'] = $this->config->get('config_meta_title');
if ($this->request->server['HTTPS']) {
    $schema['url'] = $this->config->get('config_ssl');
} else {
    $schema['url'] = $this->config->get('config_url');
}
if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
    $schema['logo'] = $this->config->get('config_logo');
}
$schema['contactPoint'] = array(
    '@type'       => 'ContactPoint',
    'telephone'   => $this->config->get('config_telephone'),
    'contactType' => 'customer support',
    'email'       => $this->config->get('config_email')
);
$this->document->setSchema($schema);
            
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}