<?php
require '../config.php';
require '../system/library/db.php';
require '../system/library/config.php';
class Redirect{
  private $code = null;
  private $access_token = null;
  private $social_name = null;
  private $expires_in = null;
  private $social_settings = null;

  public function __construct($code, $social_name){
    $this->code = $code;
    $this->social_name = $social_name;
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

    if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
    	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`ssl`, 'www.', '') = '" . $db->escape('https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
    } else {
    	$store_query = $db->query("SELECT * FROM " . DB_PREFIX . "store WHERE REPLACE(`url`, 'www.', '') = '" . $db->escape('http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/') . "'");
    }

    if ($store_query->num_rows) {
    	$config_store_id = $store_query->row['store_id'];
    } else {
    	$config_store_id =  0;
    }

    $this->social_settings = unserialize($db->query("SELECT `value` FROM " . DB_PREFIX . "setting WHERE (store_id = '0' OR store_id = '" . (int)$config_store_id . "') AND `key` = 'image_uploader'")->row['value']);
  }

  private function setAccesToken(){
    if(!is_null($this->access_token)){
      $exp = is_null($this->expires_in)?3600 * 24 * 7:$this->expires_in;
      setcookie("a_" . $this->social_name, $this->access_token, time() + $exp, '/');
      header("Location: " . HTTPS_SERVER . "index.php?route=module/image_uploader#" . $this->social_name);
    }else{
      header("Location: " . HTTPS_SERVER . "index.php?route=module/image_uploader");
    }
  }

  private function curlInit($data){

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $data['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_POST, $data['post']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data['fields']);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

  public function getAccessFacebook(){
    if(!is_null($this->code)){
      $params = array(
        'fields' => array(
          'client_id'     => $this->social_settings['facebook']['client_id'],
          'client_secret' => $this->social_settings['facebook']['secret'],
          'redirect_uri'  => HTTPS_SERVER . "api/" . $this->social_name . ".php",
          'code'          => $this->code
        ),
        'url' => 'https://graph.facebook.com/v3.2/oauth/access_token',
        'post' => false
      );
      $data = json_decode($this->curlInit($params), true);


      $params = array(
        'fields' => array(
          'fields' => 'id',
          'access_token' => $data['access_token'],
        ),
        'url' => 'https://graph.facebook.com/v3.2/me',
        'post' => false
      );

      $user = json_decode($this->curlInit($params), true);
      $this->access_token = isset($data['access_token'])?$data['access_token']:null;
      $this->expires_in = isset($data['expires_in'])?$data['expires_in']:null;
      setcookie("f_uid", $user['id'], time() + $data['expires_in'], '/');
    }

    $this->setAccesToken();
  }

  public function getAccessInstagram(){
    if(!is_null($this->code)){
      $params = array(
        'fields' => array(
          'client_id'     => $this->social_settings['instagram']['client_id'],
          'client_secret' => $this->social_settings['instagram']['secret'],
          'grant_type'    => 'authorization_code',
          'redirect_uri'  => HTTPS_SERVER . "api/" . $this->social_name . ".php",
          'code'          => $this->code
        ),
        'url' => 'https://api.instagram.com/oauth/access_token',
        'post' => true
      );

      $data = json_decode($this->curlInit($params), true);

      $this->access_token = isset($data['access_token'])?$data['access_token']:null;
    }

    $this->setAccesToken();
  }
}
?>
