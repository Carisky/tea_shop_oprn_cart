<?php
class ModelLocalisationCity extends Model {
    public function getCity($city_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "city WHERE city_id = '" . (int)$city_id . "'");
        return $query->row;
    }

    public function getCityByKeyword($keyword) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "city WHERE keyword = '" . $this->db->escape($keyword) . "'");
        return $query->row;
    }

    public function getCities() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "city ORDER BY name ASC");
        return $query->rows;
    }
}
