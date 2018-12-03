<?php
if(isset($_GET['code']) && !empty($_GET['code'])){
  $data = getAccessInstagram($_GET['code'], $_GET['referer']);
  var_dump($data);
  if(isset($data['access_token'])){
    setcookie('insAcc', $data['access_token'], time() + 3600, '/');
    header('Location: http://photoradost.loc/index.php?route=module/image_uploader&instagram');
  }
}

function getAccessInstagram($code){
  $fields = array(
    'client_id'     => 'd103bb3cf5c84baca2a28a5a502ec7be',
    'client_secret' => 'cfe7e32a73924080afbacaa310356b50',
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'http://photoradost.loc/social/redirect.php',
    'code'          => $code
  );

  $url = 'https://api.instagram.com/oauth/access_token';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
  $result = curl_exec($ch);
  curl_close($ch);

  return json_decode($result, true);
}
?>
