<?php
class ModelAccountCustomField extends Model {
	public function getCustomField($custom_field_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN `" . DB_PREFIX . "custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cf.custom_field_id = '" . (int)$custom_field_id . "' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getCustomFields($customer_group_id = 0) {
		$custom_field_data = array();

		if (!$customer_group_id) {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field` cf LEFT JOIN `" . DB_PREFIX . "custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cf.status = '1' ORDER BY cf.sort_order ASC");
		} else {
			$custom_field_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "custom_field_customer_group` cfcg LEFT JOIN `" . DB_PREFIX . "custom_field` cf ON (cfcg.custom_field_id = cf.custom_field_id) LEFT JOIN `" . DB_PREFIX . "custom_field_description` cfd ON (cf.custom_field_id = cfd.custom_field_id) WHERE cf.status = '1' AND cfd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cfcg.customer_group_id = '" . (int)$customer_group_id . "' ORDER BY cf.sort_order ASC");
		}

		foreach ($custom_field_query->rows as $custom_field) {
			$custom_field_value_data = array();

			if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio' || $custom_field['type'] == 'checkbox') {
				$custom_field_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value cfv LEFT JOIN " . DB_PREFIX . "custom_field_value_description cfvd ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) WHERE cfv.custom_field_id = '" . (int)$custom_field['custom_field_id'] . "' AND cfvd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY cfv.sort_order ASC");

				foreach ($custom_field_value_query->rows as $custom_field_value) {
					$custom_field_value_data[] = array(
						'custom_field_value_id' => $custom_field_value['custom_field_value_id'],
						'name'                  => $custom_field_value['name']
					);
				}
			}

			$custom_field_data[] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $custom_field_value_data,
				'name'               => $custom_field['name'],
				'type'               => $custom_field['type'],
				'value'              => $custom_field['value'],
				'validation'         => $custom_field['validation'],
				'location'           => $custom_field['location'],
				'required'           => empty($custom_field['required']) || $custom_field['required'] == 0 ? false : true,
				'sort_order'         => $custom_field['sort_order']
			);
		}

		return $custom_field_data;
	}
	/**
     * Возвращает массив описаний custom fields по списку ID для данной группы клиента.
     * @param int[] $ids Список custom_field_id
     * @param int|null $customer_group_id Если null — возьмёт группу текущего клиента
     * @return array Массив полей с ключами: custom_field_id, name, type, location, custom_field_value (если применимо) и др.
     */
    public function getCustomFieldsByIds(array $ids, $customer_group_id = null) {
        // Определяем группу покупателя
        if ($customer_group_id === null) {
            $customer_group_id = $this->customer->isLogged()
                ? $this->customer->getGroupId()
                : $this->config->get('config_customer_group_id');
        }
        // Загружаем модель custom_field
        $this->load->model('account/custom_field');

        // Получаем все поля данной группы
        $all = $this->model_account_custom_field->getCustomFields(['filter_customer_group_id' => $customer_group_id]);

        $result = [];
        foreach ($all as $field) {
            $fid = (int)$field['custom_field_id'];
            if (in_array($fid, $ids, true) && $field['location'] === 'address') {
                // Для типов с вариантами подтянем значения
                if (in_array($field['type'], ['select','radio','checkbox','image'], true)) {
                    $values = $this->model_account_custom_field->getCustomFieldValues($fid);
                    $opts = [];
                    foreach ($values as $value_id => $val) {
                        $opts[] = [
                            'custom_field_value_id' => $value_id,
                            'name' => $val['name']
                        ];
                    }
                    $field['custom_field_value'] = $opts;
                }
                $result[] = $field;
            }
        }
        return $result;
    }

    /**
     * Упрощённо: возвращает поля получателя (recipient) по жёсткому списку ID.
     * @param int|null $customer_group_id
     * @return array
     */
    public function getRecipientCustomFields($customer_group_id = null) {
        // Список ID полей получателя жёстко
        $recipient_ids = $this->getRecipientFieldIds();
        return $this->getCustomFieldsByIds($recipient_ids, $customer_group_id);
    }

    /**
     * Проверка: является ли поле recipient
     * @param int $custom_field_id
     * @return bool
     */
    public function isRecipientField($custom_field_id) {
        return in_array((int)$custom_field_id, $this->getRecipientFieldIds(), true);
    }

    /**
     * Жёстко прописанный набор ID полей для recipient.
     * @return int[]
     */
    public function getRecipientFieldIds() {
        return [7, 8, 9, 10, 11, 12, 13, 14];
    }
	public function getCustomFieldValues($custom_field_id) {
    $custom_field_value_data = [];

    $query = $this->db->query(
        "SELECT cfv.custom_field_value_id, cfvd.name 
         FROM " . DB_PREFIX . "custom_field_value cfv 
         LEFT JOIN " . DB_PREFIX . "custom_field_value_description cfvd 
           ON (cfv.custom_field_value_id = cfvd.custom_field_value_id) 
         WHERE cfv.custom_field_id = '" . (int)$custom_field_id . "' 
           AND cfvd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
         ORDER BY cfv.sort_order ASC"
    );

    foreach ($query->rows as $row) {
        // Возвращаем в формате [value_id => ['custom_field_value_id'=>..., 'name'=>...]] или [value_id=>'name']
        $custom_field_value_data[$row['custom_field_value_id']] = [
            'custom_field_value_id' => $row['custom_field_value_id'],
            'name' => $row['name']
        ];
    }

    return $custom_field_value_data;
}

}