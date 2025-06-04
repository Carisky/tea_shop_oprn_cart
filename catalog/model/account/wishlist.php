<?php
class ModelAccountWishlist extends Model {
	public function addWishlist($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_wishlist SET customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', date_added = NOW()");
	}

	public function deleteWishlist($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "' AND product_id = '" . (int)$product_id . "'");
	}

	public function getWishlist() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->rows;
	}

	public function getTotalWishlist() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_wishlist WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
	
	public function getNrWishlist() // Noir
	{
		$return = array();
		$query = $this->db->query("SELECT product_id FROM ".DB_PREFIX."customer_wishlist WHERE customer_id = ".(int)$this->customer->getId());
		if($query->num_rows) {
			foreach($query->rows as $row) {
				$return[] = $row['product_id'];
			}
		}
		return $return;
	}
}
