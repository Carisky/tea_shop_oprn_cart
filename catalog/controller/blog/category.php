<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerBlogCategory extends Controller { // Noir
	public function index() {
				
		$this->load->model('blog/article');
		$this->load->model('tool/image');
				
		$parts = explode('_', (string)$this->request->get['blog_category_id']);
			
		$this->load->language('blog/category');
		$this->load->model('blog/category');
		
		if ($this->config->get('config_noindex_disallow_params')) {
			$params = explode ("\r\n", $this->config->get('config_noindex_disallow_params'));
			if(!empty($params)) {
				$disallow_params = $params;
			}
		}
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		foreach ($parts as $key => $blog_category_id) {
			$category_info = $this->model_blog_category->getCategory($blog_category_id);
			if ($category_info) {
				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id)
				);
			}
			if(!$key) $main_category_info = $category_info;
			if(!isset($parts[$key + 1])) $current_category_info = $category_info;
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
						
		if(empty($current_category_info)) {
			$url = '';
			if (isset($this->request->get['blog_category_id'])) $url .= '&blog_category_id=' . $this->request->get['blog_category_id'];

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('blog/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link('common/home');
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$this->response->setOutput($this->load->view('error/not_found', $data));
				
		} else {
			
			if ($current_category_info['meta_title']) {
				$this->document->setTitle($current_category_info['meta_title']);
			} else {
				$this->document->setTitle($current_category_info['name']);
			}
			
			if ($current_category_info['noindex'] <= 0 and $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
			
			if ($current_category_info['meta_h1']) {
				$data['heading_title'] = $current_category_info['meta_h1'];
			} else {
				$data['heading_title'] = $current_category_info['name'];
			}
			
			$this->document->setDescription($current_category_info['meta_description']);
			$this->document->setKeywords($current_category_info['meta_keyword']);
			$this->document->addLink($this->url->link('blog/category', 'blog_category_id=' . $this->request->get['blog_category_id']), 'canonical');
			
			$data['text_refine'] = $this->language->get('text_refine');
			$data['text_empty'] = $this->language->get('text_empty');
			$data['text_views'] = $this->language->get('text_views');
			$data['button_more'] = $this->language->get('button_more');
			
			$image = $current_category_info['image'] ? $current_category_info['image'] : $main_category_info['image'];
			if ($image) {
				$data['thumb'] = $this->model_tool_image->resize($image, $this->config->get('configblog_image_category_width'), $this->config->get('configblog_image_category_height'));
				$data['thumb_mob'] = $this->model_tool_image->resize($image, 767, 320, 1);
			} else {
				$data['thumb'] = '';
			}
			
			if($current_category_info['blog_category_id'] == $main_category_info['blog_category_id']) {
				$data['is_main'] = 1;
			} else {
				$data['is_main'] = 0;
			}
				
			$data['description'] = html_entity_decode($current_category_info['description'], ENT_QUOTES, 'UTF-8');
			$data['configblog_review_status'] = $this->config->get('configblog_review_status');
			
			$data['categories'] = [
				[
					'name'  => $main_category_info['name'],
					'href'  => $this->url->link('blog/category', 'blog_category_id='.$main_category_info['blog_category_id']),
					'active' => $data['is_main']
				]
			];
			$results = $this->model_blog_category->getCategories($main_category_info['blog_category_id']);
			foreach ($results as $result) {
				$data['categories'][] = array(
					'name'  => $result['name'],
					'href'  => $this->url->link('blog/category', 'blog_category_id='.$main_category_info['blog_category_id'].'_'.$result['blog_category_id']),
					'active' => ($result['blog_category_id'] == $current_category_info['blog_category_id'])
				);
			}
				
			$result = $this->getArticles($current_category_info['blog_category_id'], 1);
			$data['articles'] = $result['articles'];
			$data['more'] = $result['more'];
			if (isset($result['total'])) $data['total'] = $result['total'];
			$data['category_id'] = $current_category_info['blog_category_id'];
				
			$this->response->setOutput($this->load->view('blog/category', $data));
		}
		
	}
	
	public function load() {
		$blog_category_id = (int)$this->request->get['id'];
		$page = (int)$this->request->get['page'];
		$total = (int)$this->request->get['total'];
		
		$result = $this->getArticles($blog_category_id, $page, $total);
		$data['articles'] = $result['articles'];
				
		$json = [
			'html' => $this->load->view('blog/articles', $data),
			'more' => $result['more']
		];
		$this->response->setOutput(json_encode($json));
	}
	
	public function getArticles($blog_category_id, $page, $article_total = null)
	{
		$this->load->model('blog/article');
		$this->load->model('tool/image');
		$articles = array();
		$limit = (int)$this->config->get('configblog_article_limit');
		$prev = ($page - 1)*$limit;
		$article_data = array(
			'filter_blog_category_id' => $blog_category_id,
			'filter_sub_category' => true,
			'sort'               => 'p.date_added',
			'order'              => 'DESC',
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);
						
		if(is_null($article_total)) $article_total = $this->model_blog_article->getTotalArticles($article_data);
		if(!$article_total or $article_total < $prev) return ['articles' => [], 'more' => 0];
		
		$results = $this->model_blog_article->getArticles($article_data);
		if(empty($results)) return ['articles' => [], 'more' => 0];
		
		foreach ($results as $result) {
			
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('configblog_image_article_width'), $this->config->get('configblog_image_article_height'), 1);
			} else {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('configblog_image_article_width'), $this->config->get('configblog_image_article_height'), 1);
			}

			if ($this->config->get('configblog_review_status')) {
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}
			
			$articles[] = [
				'article_id'  => $result['article_id'],
				'thumb'       => $image,
				'name'        => $result['name'],
				//'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
				//'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				//'viewed'      => $result['viewed'],
				//'rating'      => $result['rating'],
				'href'        => $this->url->link('blog/article', 'blog_category_id='.$blog_category_id.'&article_id=' . $result['article_id'])
			];
		}
		
		return [
			'articles' => $articles,
			'total' => $article_total,
			'more' => (($prev + count($results) < $article_total) ? 1 : 0)
		];
	}
	
}