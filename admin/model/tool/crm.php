<?php
class ModelToolCrm extends Model {
	
	public function getProductId($crm_product_id)
	{
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model = '".(int)$crm_product_id."'");
		return ($query->num_rows ? (int)$query->row['product_id'] : 0);
	}
	
	public function updateProduct($product_id, $data)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = ".$data['quantity'].", price = '".(float)$data['price']."' WHERE product_id = ".$product_id);
		
	}
	
	public function saveProductDescription($product_id, $language_id, $value)
	{
		$this->db->query("
			INSERT INTO ".DB_PREFIX."product_description 
			SET 
				product_id = ".(int)$product_id.", 
				language_id = ".(int)$language_id.", 
				name = '".$this->db->escape($value['name'])."', 
				description = '".(isset($value['description']) ? $this->db->escape($value['description']) : '')."', 
				tag = '".(isset($value['tag']) ? $this->db->escape($value['tag']) : '')."', 
				meta_title = '".(isset($value['meta_title']) ? $this->db->escape($value['meta_title']) : '')."', 
				meta_description = '".(isset($value['meta_description']) ? $this->db->escape($value['meta_description']) : '')."', 
				meta_keyword = '".(isset($value['meta_keyword']) ? $this->db->escape($value['meta_keyword']) : '')."', 
				meta_h1 = '".(isset($value['meta_h1']) ? $this->db->escape($value['meta_h1']) : '')."'
		");
	}
}