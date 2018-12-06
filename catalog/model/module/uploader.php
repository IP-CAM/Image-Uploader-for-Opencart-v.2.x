<?php
class ModelModuleUploader extends Model{
  public function getImages($id_session){
    $this->db->query("UPDATE " . DB_PREFIX . "uploader_image SET date = NOW() + INTERVAL 7 DAY WHERE session_id = '" . $this->db->escape($id_session) . "'");
    return $this->db->query("SELECT name, path, options, base, format_id, paper_type_id, set_in_format, copy_count, size FROM " . DB_PREFIX . "uploader_image WHERE session_id = '" . $this->db->escape($id_session) . "'")->rows;
  }

  public function getImage($name, $id_session){
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'")->row;
  }

  public function addImage($data){
    $sql = "INSERT INTO " . DB_PREFIX . "uploader_image (name, session_id, path, base, format_id, paper_type_id, set_in_format, options, copy_count, size, date) ";
    $sql .= "VALUES ('" . $this->db->escape($data['name']) . "',";
    $sql .= " '" . $this->db->escape($data['session_id']) . "',";
    $sql .= " '" . $this->db->escape($data['path']) . "',";
    $sql .= " '" . $this->db->escape($data['base']) . "',";
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
    $result = $this->db->query("SELECT path FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
    $this->db->query("DELETE FROM " . DB_PREFIX . "uploader_image WHERE name = '" . $this->db->escape($name) . "' AND session_id = '" . $this->db->escape($id_session) . "'");
    return isset($result->row['path'])?$result->row['path']:0;
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
        $results[$key]['article'] = $this->getArticle($result['article_id']);
      }else{
        $results[$key]['article'] = "";
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

  public function getArticle($id){
    $result = $this->db->query("SELECT title FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$id . "'")->row;
    if(isset($result['title'])){
      $result['link'] = $this->url->link('information/information', 'information_id=' .  $id);
      return $result;
    }
    return "";
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
}
?>
