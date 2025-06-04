<?php


	class ControllerCommonLoadFormatPagination extends Controller
	{
		public function index()
		{
			$data['load_format_pagination_borderwidth'] = $this->config->get('load_format_pagination_borderwidth');
			$data['load_format_pagination_borderround'] = $this->config->get('load_format_pagination_borderround');
			$data['load_format_pagination_bordercolor'] = $this->config->get('load_format_pagination_bordercolor');
			$data['load_format_pagination_buttoncolor'] = $this->config->get('load_format_pagination_buttoncolor');
			$data['load_format_pagination_backgroundcolor'] = $this->config->get('load_format_pagination_backgroundcolor');
			$data['load_format_pagination_hover_buttoncolor'] = $this->config->get('load_format_pagination_hover_buttoncolor');
			$data['load_format_pagination_hover_backgroundcolor'] = $this->config->get('load_format_pagination_hover_backgroundcolor');

			return $this->load->view('common/load_format_pagination', $data);
		}

		public function info()
		{
			$this->response->setOutput($this->index());
		}
	}