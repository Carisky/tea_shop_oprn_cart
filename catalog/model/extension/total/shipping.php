<?php
class ModelExtensionTotalShipping extends Model {
	public function getTotal($total) {
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
			
			// < Noir
			$free = false;
			$free_sum = (int)$this->config->get('total_shipping_free_sum');
			if($free_sum > 0) {
				foreach($total['totals'] as $item) {
					if($item['code'] == 'sub_total') {
						$sum = $item['value'];
						break;
					}
				}
				if(!isset($sum)) $sum = 0;
				
				if($sum >= $free_sum) $free = true;
			}
			// Noir >
			$total['totals'][] = array(
				'code'       => 'shipping',
				'title'      => $this->session->data['shipping_method']['title'],
				//'value'      => $this->session->data['shipping_method']['cost'],
				'value'      => ($free ? 0 : $this->session->data['shipping_method']['cost']), //Noir
				'sort_order' => $this->config->get('total_shipping_sort_order')
			);

			if ($this->session->data['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($total['taxes'][$tax_rate['tax_rate_id']])) {
						$total['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$total['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}

			//$total['total'] += $this->session->data['shipping_method']['cost'];
			if(!$free) $total['total'] += $this->session->data['shipping_method']['cost']; //Noir
		}
	}
}