<?php
class ModelExtensionModuleNrMailDiscount extends Model {

 public function getCoupon($id)
 {
	 $query = $this->db->query("SELECT * FROM ".DB_PREFIX."coupon WHERE coupon_id = ".(int)$id);
	 return $query->row;
 }

}