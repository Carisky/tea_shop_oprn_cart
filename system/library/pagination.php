<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Pagination class
*/
class Pagination {
	public $total = 0;
	public $page = 1;
	public $limit = 20;
	public $num_links = 8;
	public $url = '';
	public $text_first = '|&lt;';
	public $text_last = '&gt;|';
	public $text_next = '<svg width="22" height="19">><use xlink:href="/catalog/view/theme/noir/img/sprite.svg#pag"></use></svg>'; //Noir
	public $text_prev = '<svg width="22" height="19">><use xlink:href="/catalog/view/theme/noir/img/sprite.svg#pag"></use></svg>'; //Noir

	/**
     * 
     *
     * @return	text
     */
	public function render() { 
		$total = $this->total;

		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}

		if (!(int)$this->limit) {
			$limit = 10;
		} else {
			$limit = $this->limit;
		}

		$num_links = $this->num_links;
		$num_pages = ceil($total / $limit);

		$this->url = str_replace('%7Bpage%7D', '{page}', $this->url);

		$output = '<ul class="pagination df jcc">';

		if ($page > 1) {
			$output .= '<li class="first"><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $this->text_first . '</a></li>'; // Noir 
			
			if ($page - 1 === 1) {
				$output .= '<li class="prev"><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $this->text_prev . '</a></li>'; // Noir 
			} else {
				$output .= '<li class="prev"><a href="' . str_replace('{page}', $page - 1, $this->url) . '">' . $this->text_prev . '</a></li>'; // Noir 
			}
		}

		if ($num_pages > 1) {
			if ($num_pages <= $num_links) {
				$start = 1;
				$end = $num_pages;
			} else {
				$start = $page - floor($num_links / 2);
				$end = $page + floor($num_links / 2);

				if ($start < 1) {
					$end += abs($start) + 1;
					$start = 1;
				}

				if ($end > $num_pages) {
					$start -= ($end - $num_pages);
					$end = $num_pages;
				}
			}

			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= '<li class="pg active"><span>' . $i . '</span></li>';
				} else {
					if ($i === 1) {
						$output .= '<li class="pg"><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">' . $i . '</a></li>'; //Noir
					} else {
						$output .= '<li class="pg"><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>'; //Noir
					}
				}
			}
		}

		if ($page < $num_pages) {
			$output .= '<li class="next"><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>'; // Noir 
			$output .= '<li class="last"><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $this->text_last . '</a></li>'; // Noir 
		}

		$output .= '</ul>';

		if ($num_pages > 1) {
			return $output;
		} else {
			return '';
		}
	}
}
