<?php
require '../config.php';
class Redirect{
  private $code = null;
  private $access_token = null;
  private $social_name = null;
  private $expires_in = null;

  public function __construct($code, $social_name){
    $this->code = $code;
    $this->social_name = $social_name;
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data['fields']);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }

  public function getAccessFacebook(){
    if(!is_null($this->code)){
      $params = array(
        'fields' => array(
          'client_id'     => FACEBOOK_CLIENT_ID,
          'client_secret' => FACEBOOK_SECRET,
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
          'client_id'     => INSTAGRAM_CLIENT_ID,
          'client_secret' => INSTAGRAM_SECRET,
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
