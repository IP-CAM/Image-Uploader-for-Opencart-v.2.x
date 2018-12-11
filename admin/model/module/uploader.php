<?php
class ModelModuleUploader extends Model {

  public function install(){
    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_count_paper` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` int(11) NOT NULL,
      `uploader_type` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_format` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `ratio` varchar(50) NOT NULL,
      `bad` float NOT NULL,
      `normal` float NOT NULL,
      `good` float NOT NULL,
      `default_value` int(1) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_image` (
      `id` bigint(19) NOT NULL AUTO_INCREMENT,
      `name` varchar(32) NOT NULL,
      `session_id` varchar(64) NOT NULL,
      `path` varchar(255) NOT NULL,
      `base_path` varchar(255) NOT NULL,
      `base` varchar(255) NOT NULL,
      `format_id` int(11) NOT NULL,
      `paper_type_id` int(11) NOT NULL,
      `set_in_format` int(1) NOT NULL DEFAULT '0',
      `options` json NOT NULL,
      `copy_count` int(11) NOT NULL,
      `size` int(11) NOT NULL,
      `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_option` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(150) NOT NULL,
      `type` enum('select','checkbox') NOT NULL,
      `article_id` int(11) NOT NULL,
      `uploader_type` int(1) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_option_value` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `option_id` int(11) NOT NULL,
      `text` varchar(150) NOT NULL,
      `default_value` int(1) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_paper_type` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `default_value` int(1) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0',
      `uploader_type` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    CREATE TABLE `" . DB_PREFIX . "uploader_price` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `key_text` varchar(80) NOT NULL,
      `format_id` int(11) DEFAULT NULL,
      `count_paper_id` int(11) DEFAULT NULL,
      `price` float NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    $this->db->query("
    ALTER TABLE `" . DB_PREFIX . "uploader_price`
      ADD KEY `format_id` (`format_id`),
      ADD KEY `count_paper_id` (`count_paper_id`);
    ");

    $this->db->query("
    ALTER TABLE `" . DB_PREFIX . "uploader_option_value`
      ADD KEY `option_id` (`option_id`);
    ");

    $this->db->query("
    ALTER TABLE `" . DB_PREFIX . "uploader_option_value`
      ADD CONSTRAINT `" . DB_PREFIX . "uploader_option_value_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `" . DB_PREFIX . "uploader_option` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");

    $this->db->query("
    ALTER TABLE `" . DB_PREFIX . "uploader_price`
      ADD CONSTRAINT `" . DB_PREFIX . "uploader_price_ibfk_1` FOREIGN KEY (`format_id`) REFERENCES `" . DB_PREFIX . "uploader_format` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
      ADD CONSTRAINT `" . DB_PREFIX . "uploader_price_ibfk_2` FOREIGN KEY (`count_paper_id`) REFERENCES `" . DB_PREFIX . "uploader_count_paper` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    ");

    $this->saveLink(array('keyword' => 'image_uploader', 'type' => 'image'));
    $this->saveLink(array('keyword' => 'document_uploader', 'type' => 'document'));
  }

  public function remove(){
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_count_paper`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_format`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_image`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_option`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_option_value`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_paper_type`");
    $this->db->query("DROP TABLE `" . DB_PREFIX . "uploader_price`");
    $this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'module/image_uploader'");
    $this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'module/document_uploader'");
  }

  public function setFormat($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "uploader_format (name, ratio) VALUES ('" . $this->db->escape($data['name']) . "', '" . $this->db->escape($data['ratio']) . "')");
    return $this->getRow($this->db->getLastId(), "uploader_format");
  }

  public function setPaperType($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "uploader_paper_type (name) VALUES ('" . $this->db->escape($data['name']) . "')");
    return $this->getRow($this->db->getLastId(), "uploader_paper_type");
  }

  public function setCountPaper($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "uploader_count_paper (name) VALUES ('" . (int)$data['name'] . "')");
    return $this->getRow($this->db->getLastId(), "uploader_count_paper");
  }

  public function setOption($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "uploader_option (name, type, article_id, uploader_type) VALUES ('" . $this->db->escape($data['name']) . "', '" . $this->db->escape($data['type']) . "', '" . $this->db->escape($data['article_id']) . "', '" . (int)$data['uploader_type'] . "')");
    return $this->getOptionRow($this->db->getLastId(), "uploader_option");
  }

  public function setOptionValue($data){
    $this->db->query("INSERT INTO " . DB_PREFIX . "uploader_option_value (text, option_id) VALUES ('" . $this->db->escape($data['text']) . "', '" . $this->db->escape($data['option_id']) . "')");
    return $this->getRow($this->db->getLastId(), "uploader_option_value");
  }

  public function getRow($id, $table){
    $sql = "SELECT * FROM " . DB_PREFIX . $table . " WHERE `id` = '" . (int)$id . "'";
    return $this->db->query($sql)->row;
  }

  public function getOptionRow($id, $table){
    $sql = "SELECT * FROM " . DB_PREFIX . "uploader_option WHERE `id` = '" . (int)$id . "'";
    $result = $this->db->query($sql)->row;
    if(!is_null($result['article_id'])){
      $result['article_title'] = $this->getArticles(array('filter_id' => $result['article_id'], 'filter_name' => ""));
    }else{
      $result['article_title'] = "";
      $result['article_id'] = "";
    }
    $result['values'] = $this->getRows("option_value", 0, $result['id']);
    return $result;
  }

  public function getOptionRows($type){
    $sql = "SELECT * FROM " . DB_PREFIX . "uploader_option WHERE uploader_type = '" . (int)$type . "'";
    $results = $this->db->query($sql)->rows;
    foreach($results as $key => $result){
      if(!is_null($result['article_id'])){
        $results[$key]['article_title'] = $this->getArticles(array('filter_id' => $result['article_id'], 'filter_name' => ""));
      }else{
        $results[$key]['article_title'] = "";
        $results[$key]['article_id'] = "";
      }
      $results[$key]['values'] = $this->getRows("option_value", 0, $result['id']);
    }
    return $results;
  }

  public function getCountSelectOption($type){
    return $this->db->query("SELECT COUNT(*) as `count` FROM " . DB_PREFIX . "uploader_option WHERE type = 'select' AND uploader_type = '" . (int)$type . "'")->row['count'];
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
    return $this->db->query("SELECT * FROM " . DB_PREFIX . "uploader_" . $table . $sql)->rows;
  }

  public function removeRow($data){
    if($data['group'] == "option"){
      $this->db->query("DELETE FROM `" . DB_PREFIX . "uploader_price` WHERE `key_text` LIKE 'option-" . (int)$data['id'] . "%'");
    }else if($data['group'] == "option_value"){
      $option_id = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "uploader_option_value WHERE id = '" . (int)$data['id'] . "'")->row['option_id'];
      $this->db->query("DELETE FROM `" . DB_PREFIX . "uploader_price` WHERE `key_text` LIKE 'option-" . (int)$option_id . "-" . (int)$data['id'] . "%'");
    }
    $this->db->query("DELETE FROM `" . DB_PREFIX . "uploader_" . $this->db->escape($data['group']) . "` WHERE `id` = '" . (int)$data['id'] . "'");
  }

  public function updateRow($data){
    if(strpos($data['col'], "default") !== false){
      $sql = "";
      if(strpos($data['group'], "option_value") !== false){
        $option_id = $this->db->query("SELECT `option_id` FROM `" . DB_PREFIX . "uploader_option_value` WHERE `id` = '" . (int)$data['id'] . "'")->row['option_id'];
        $sql .= " WHERE `option_id` = '" . $option_id . "'";
      }

      if($data['group'] == "paper_type" || $data['group'] == "count_paper"){
        $uploader_type = $this->db->query("SELECT `uploader_type` FROM `" . DB_PREFIX . "uploader_" . $this->db->escape($data['group']) . "` WHERE `id` = '" . (int)$data['id'] . "'")->row['uploader_type'];
        $sql .= " WHERE `uploader_type` = '" . (int)$uploader_type . "'";
      }
      $data['col'] = "default_value";
      $data['value'] = 1;
      $this->db->query("UPDATE `" . DB_PREFIX . "uploader_" . $this->db->escape($data['group']) . "` SET `default_value` = '0'" . $sql);
    }
    $this->db->query("UPDATE `" . DB_PREFIX . "uploader_" . $this->db->escape($data['group']) . "` SET `" . $this->db->escape($data['col']) . "` = '" . $this->db->escape($data['value']) . "' WHERE `id` = '" . (int)$data['id'] . "'");
  }

  public function getArticles($data){
    if(!is_null($data['filter_id'])){
      $result = $this->db->query("SELECT title FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$data['filter_id'] . "'")->row;
      return isset($result['title'])?$result['title']:"";
    }
    if(!empty($data['filter_name'])){
      return $this->db->query("SELECT information_id, title FROM " . DB_PREFIX . "information_description WHERE LCASE(title) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'")->rows;
    }
    return 0;
  }

  public function savePrice($data){
    $sql = "INSERT INTO `" . DB_PREFIX . "uploader_price` (";
    $sql_search = "";
    if(!is_null($data['format_id'])){
      $sql .= "`format_id`,";
      $sql_search .= " `format_id` = '" . $data['format_id'] . "' AND";
    }
    if(!is_null($data['count_paper_id'])){
      $sql .= " `count_paper_id`,";
      $sql_search .= " `count_paper_id` = '" . $data['count_paper_id'] . "' AND";
    }
    $sql .= " `key_text`, `price`) VALUES (";
    $sql_search .= "`key_text` = '" . $this->db->escape($data['key_text']) . "'";
    if($this->db->query("SELECT COUNT(*) as `count` FROM `" . DB_PREFIX . "uploader_price` WHERE" . $sql_search)->row['count'] == 0){
      if(!is_null($data['format_id'])){
        $sql .= "'" . $data['format_id'] . "',";
      }
      if(!is_null($data['count_paper_id'])){
        $sql .= "'" . $data['count_paper_id'] . "',";
      }

      $sql .= "'" . $data['key_text'] . "', '" . $data['price'] . "')";
      $this->db->query($sql);
    }else{
      $this->db->query("UPDATE `" . DB_PREFIX . "uploader_price` SET `price` = '" . $data['price'] . "' WHERE" . $sql_search);
    }
  }

  public function getPrices(){
    $results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "uploader_price`")->rows;
    $val_price = array();
    foreach($results as $result){
      $val_price[$result['key_text'].'_'.($result['format_id']==NULL?'':$result['format_id']).'_'.($result['count_paper_id']==NULL?'':$result['count_paper_id'])] = $result['price'];
    }
    return $val_price;
  }

  public function saveQuality($data){
    $this->db->query("UPDATE " . DB_PREFIX . "uploader_format SET bad = '" . $this->db->escape($data['bad']) . "', normal = '" . $this->db->escape($data['normal']) . "', good = '" . $this->db->escape($data['good']) . "' WHERE id = '" . (int)$data['id'] . "'");
  }

  public function saveLink($data){
    if($this->db->query("SELECT COUNT(*) as `count` FROM " . DB_PREFIX . "url_alias WHERE query = 'module/" . $this->db->escape($data['type']) . "_uploader'")->row['count'] > 0){
      $this->db->query("UPDATE " . DB_PREFIX . "url_alias SET keyword = '" . $this->db->escape($data['keyword']) . "' WHERE query = 'module/" . $this->db->escape($data['type']) . "_uploader'");
    }else{
      $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'module/" . $this->db->escape($data['type']) . "_uploader', keyword = '" . $this->db->escape($data['keyword']) . "'");
    }
  }

  public function getLink($type){
    $link = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'module/" . $this->db->escape($type) . "_uploader'");
    return isset($link->row['keyword'])?$link->row['keyword']:'';
  }
}
?>
