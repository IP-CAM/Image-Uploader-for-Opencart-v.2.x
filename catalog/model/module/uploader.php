<?php
class ModelModuleUploader extends Model{
  public function getImages($id_session){
    $this->db->query("UPDATE " . DB_PREFIX . "uploader_image SET date = NOW() + INTERVAL 7 DAY WHERE cart IS NULL AND session_id = '" . $this->db->escape($id_session) . "'");
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_image WHERE cart IS NULL AND session_id = '" . $this->db->escape($id_session) . "'")->rows;
  }

  public function getImage($name, $id_session){
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'")->row;
  }

  public function addImage($data){
    $sql = "INSERT INTO " . DB_PREFIX . "uploader_image (name, session_id, path, base, link, base_path, format_id, paper_type_id, set_in_format, options, copy_count, size, date) ";
    $sql .= "VALUES ('" . $this->db->escape($data['name']) . "',";
    $sql .= " '" . $this->db->escape($data['session_id']) . "',";
    $sql .= " '" . $this->db->escape($data['path']) . "',";
    $sql .= " '" . $this->db->escape($data['base']) . "',";
    $sql .= " '" . $this->db->escape($data['link']) . "',";
    $sql .= " '" . $this->db->escape($data['base_path']) . "',";
    $sql .= " '" . (int)$data['format_id'] . "',";
    $sql .= " '" . (int)$data['paper_type_id'] . "',";
    $sql .= " '" . (isset($data['set_in_format'])?(int)$data['set_in_format']:0) . "',";
    $sql .= " '" . $this->db->escape($data['options']) . "',";
    $sql .= " '" . (int)$data['copy_count'] . "',";
    $sql .= " '" . (int)$data['size'] . "',";
    $sql .= "  NOW() + INTERVAL 7 DAY)";
    $this->db->query($sql);
  }

  public function updateImage($key, $value, $name, $id_session){
    $this->db->query("UPDATE " . DB_PREFIX . "uploader_image SET " . $this->db->escape($key) . " = '" . $this->db->escape($value) . "' WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
  }

  public function getImageOption($name, $id_session){
    $result = $this->db->query("SELECT options FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
    return isset($result->row['options'])?$result->row['options']:null;
  }

  public function deleteImage($name, $id_session){
    $result = $this->db->query("SELECT path, base_path FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
    $this->db->query("DELETE FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
    return $result->row;
  }

  public function getDefault($uploader_type){
    $params = array();
    $params['format'] = $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_format WHERE default_value = '1'")->row;
    $params['paper_type'] = $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_paper_type WHERE default_value = '1' AND uploader_type = '" . (int)$uploader_type . "'")->row;
    $params['options'] = $this->db->query("SELECT o.id as `option_id`, v.id as `value_id` FROM " . DB_PREFIX . "uploader_option o JOIN " . DB_PREFIX . "uploader_option_value v ON(o.id = v.option_id) WHERE v.default_value = '1' AND o.uploader_type = '" . (int)$uploader_type . "'")->rows;
    return $params;
  }

  public function getRows($table, $uploader_type = 0, $option_id = 0){
    $sql = "";
    if($table == "option"){
      return $this->getOptionRows($uploader_type);
    }
    if($table == "option_value"){
      $sql .= " WHERE option_id = '" . (int)$option_id . "'";
    }
    if($table == "paper_type" || $table == "count_paper"){
      $sql .= " WHERE uploader_type = '" . (int)$uploader_type . "'";
    }
    if($table == "option_value" || $table == "format" || $table == "paper_type"){
      $sql .= " ORDER BY sort ASC";
    }
    if($table == "count_paper"){
      $sql .= " ORDER BY name ASC";
    }
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_" . $table . $sql)->rows;
  }

  public function getOptionRows($uploader_type){
    $sql = "SELECT * FROM " . DB_PREFIX . "uploader_option WHERE uploader_type = '" . (int)$uploader_type . "'";
    $results = $this->db->query($sql)->rows;
    foreach($results as $key => $result){
      if(!is_null($result['article_id'])){
        $results[$key]['article_id'] = $result['article_id'];
      }
      $results[$key]['values'] = $this->getRows("option_value", 0, $result['id']);
    }
    return $results;
  }

  public function getOptionType($uploader_type, $id){
    $sql = "SELECT type FROM " . DB_PREFIX . "uploader_option WHERE id='" . (int)$id . "' AND uploader_type = '" . (int)$uploader_type . "'";
    $result = $this->db->query($sql)->row;
    return isset($result['type'])?$result['type']:null;
  }

  public function getPrices(){
    $results = $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_price")->rows;
    $val_price = array();
    foreach($results as $result){
      $val_price[$result['key_text'].'_'.($result['format_id']==NULL?'':$result['format_id']).'_'.($result['count_paper_id']==NULL?'':$result['count_paper_id'])] = $result['price'];
    }
    return $val_price;
  }

  public function getCountFormats($formats, $id_session){
    $count = array();
    foreach($formats as $format){
      $count[$format['id']] = $this->db->query("SELECT SUM(copy_count) as `count` FROM " . DB_PREFIX . "uploader_image WHERE format_id = '" . (int)$format['id'] . "' AND session_id = '" . $this->db->escape($id_session) . "'")->row['count'];
    }
    return $count;
  }

  public function getArticle($id){
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) LEFT JOIN " . DB_PREFIX . "information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$id . "' AND id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND i.status = '1'");

		return $query->row;
  }

  public function addProduct($id_session, $name, $price, $special){
    $this->db->query("INSERT INTO " . DB_PREFIX . "product SET
			model = '".$name."',
			quantity = '1',
			shipping = '1',
			weight_class_id = '1',
			sort_order = '1',
			status = '1',
			price = '".$price."',
			image = 'data/photo.jpg',
			date_available = '".date('Y-m-d')."',
			date_added = '".date('Y-m-d G:i:s')."'"
		);

    $product_id = $this->db->getLastId();

    if($special < $price){
      $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET
        product_id = '" . (int)$product_id . "',
        customer_group_id	= '1',
        priority = '1',
        price = '" . $special . "',
        date_start = '0000-00-00',
        date_end = '0000-00-00'"
      );
    }

    $this->load->model('localisation/language');

		foreach($this->model_localisation_language->getLanguages() as $language) {
	    $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET
				`product_id` = '".(int)$product_id."',
				`language_id` = '".$language['language_id']."',
				`name` = '".$name."',
				`description` = ''"
			);
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET `product_id` = '" . (int)$product_id . "', `store_id` = '0'");

    $this->db->query("UPDATE " . DB_PREFIX . "uploader_image SET cart = '" . (int)$product_id . "' WHERE cart IS NULL AND session_id = '" . $this->db->escape($id_session) . "'");

    return $product_id;
  }
}
?>
