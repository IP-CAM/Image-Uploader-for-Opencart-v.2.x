<?php
class ControllerModuleImageUploader extends Controller{
  private $image;
  private $image_type;

  public function index(){
    $this->load->model('module/uploader');
    $this->load->language('module/uploader');
    $this->document->addScript("catalog/view/javascript/uploader/liteuploader.js");
    $this->document->addScript("https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js");
    $this->document->addScript("catalog/view/javascript/uploader/uploadermain.js");
    $this->document->addStyle("catalog/view/theme/testUploader/stylesheet/uploader.css");
    $this->data['heading_title'] = $this->language->get('heading_title_image');
    $this->document->setTitle($this->data['heading_title']);
    if(isset($this->request->cookie['uis'])){
    	$uis = $this->request->cookie['uis'];
    	setcookie('uis', $uis, time() + 3600 * 24 * 7, '/');
    }else{
      $uis = md5(rand(0, 200) * rand(0, 200) . random_bytes(5));
    	setcookie('uis', $uis, time() + 3600 * 24 * 7, '/');
    }
    $session_id = hash("sha256", $uis);

    $images = $this->model_module_uploader->getImages($session_id);

    $this->data['formats'] = $this->model_module_uploader->getRows('format', 0);
    $count_format = $this->model_module_uploader->getCountFormats($this->data['formats'], $session_id);
    $formats_quality = array();
    $this->data['ratio'] = array();
    foreach($this->data['formats'] as $key => $format){
      $this->data['formats'][$key]['count'] = $count_format[$format['id']];
      $formats_quality[$format['id']] = array(
        'key' => $key,
        'bad' => $format['bad'],
        'normal' => $format['normal'],
        'good' => $format['good']
      );
      $ratio = explode(':', $format['ratio']);
      $this->data['ratio'][$format['id']] = array(
        "a" => $ratio[0],
        "b" => $ratio[1]
      );
    }
    $this->data['ratio'] = json_encode($this->data['ratio']);
    $count_paper_arr = array();
    $count_paper_arr[1] = '';
    $counts_paper = $this->model_module_uploader->getRows('count_paper', 0);
    foreach($counts_paper as $count){
      $count_paper_arr[$count['name']] = $count['id'];
    }

    $this->data['options'] = $this->model_module_uploader->getRows('option', 0);
    $this->data['paper_types'] = $this->model_module_uploader->getRows('paper_type', 0);
    $price = $this->model_module_uploader->getPrices();

    $this->data['images'] = array();
    $this->data['total_full_price'] = $this->data['total_count'] = $this->data['total_price'] = 0;
    foreach($images as $image){
      $image_price_option = 0;

      $options = json_decode($image['options'], true);
      foreach($options as $key => $option){
        if($option['type'] == 'select'){
          $image_price_option += (float)$price['option-' . $key . '-' . $option['value'] . '__'];
        }else{
          $image_price_option += (float)$price['option-' . $key . '__'];
        }
      }

      $count_id = '';
      foreach($count_paper_arr as $key => $count){
        if($key < $count_format[$image['format_id']]){
          $count_id = $count;
        }
      }

      $image_price = (float)$price['price_' . $image['format_id'] . '_' . $count_id] * $image['copy_count'];
      $image_price = $image_price + $image_price_option;

      $image_full_price = (float)$price['price_' . $image['format_id'] . '_'] * $image['copy_count'];
      $image_full_price = $image_full_price + $image_price_option;

      $this->data['images'][] = array(
        'name' => $image['name'],
        'format_id' => $image['format_id'],
        'paper_type_id' => $image['paper_type_id'],
        'set_in_format' => $image['set_in_format'],
        'base' => $image['base'],
        'quality' => $this->setQuality($formats_quality[(int)$image['format_id']], $image['size']),
        'options' => $options,
        'count' => $image['copy_count'],
        'price' => $this->currency->format($image_price)
      );

      $this->data['total_price'] += $image_price;
      $this->data['total_full_price'] += $image_full_price;
      $this->data['total_count'] += $image['copy_count'];
    }

    $this->data['total_price'] = $this->currency->format($this->data['total_price']);
    $this->data['total_full_price'] = $this->currency->format($this->data['total_full_price']);

    $this->data['text_pc_upload'] = $this->language->get('text_pc_upload');
    $this->data['text_vk_upload'] = $this->language->get('text_vk_upload');
    $this->data['text_fb_upload'] = $this->language->get('text_fb_upload');
    $this->data['text_inst_upload'] = $this->language->get('text_inst_upload');
    $this->data['text_paper_type'] = $this->language->get('text_paper_type');
    $this->data['text_format'] = $this->language->get('text_format');
    $this->data['text_count'] = $this->language->get('text_count');
    $this->data['text_quality_good'] = $this->language->get('text_quality_good');
    $this->data['text_quality_normal'] = $this->language->get('text_quality_normal');
    $this->data['text_quality_bad'] = $this->language->get('text_quality_bad');
    $this->data['text_quality_very_bad'] = $this->language->get('text_quality_very_bad');
    $this->data['text_select'] = $this->language->get('text_select');
    $this->data['text_delete'] = $this->language->get('text_delete');
    $this->data['text_multiplicity'] = $this->language->get('text_multiplicity');
    $this->data['text_multiplicity_dot'] = $this->language->get('text_multiplicity_dot');
    $this->data['text_set_in_format'] = $this->language->get('text_set_in_format');
    $this->data['text_submit'] = $this->language->get('text_submit');
    $this->data['text_selected_count'] = $this->language->get('text_selected_count');
    $this->data['text_loaded_empty'] = $this->language->get('text_loaded_empty');
    $this->data['text_empty'] = $this->language->get('text_empty');
    $this->data['text_upload_selected'] = $this->language->get('text_upload_selected');
    $this->data['text_total_count'] = $this->language->get('text_total_count');
    $this->data['text_price'] = $this->language->get('text_price');
    $this->data['text_full_price'] = $this->language->get('text_full_price');
    $this->data['text_conform'] = $this->language->get('text_conform');
    $this->data['text_load_more'] = $this->language->get('text_load_more');

    $this->children = array(
      'common/column_left',
      'common/column_right',
      'common/footer',
      'common/header'
    );

    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/uploader/image.tpl')) {
      $this->template = $this->config->get('config_template') . '/template/uploader/image.tpl';
    } else {
      $this->template = 'default/template/uploader/image.tpl';
    }

    $this->response->setOutput($this->render());
  }

  public function getAccessInstagram($code){
    $fields = array(
       'client_id'     => 'd103bb3cf5c84baca2a28a5a502ec7be',
       'client_secret' => 'cfe7e32a73924080afbacaa310356b50',
       'grant_type'    => 'authorization_code',
       'redirect_uri'  => $this->url->link('module/image_uploader', '', 'SSL'),
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

    return json_decode($result);
  }

  public function getData($session_id){
    $data = array();
    $formats = $this->model_module_uploader->getRows('format', 0);
    $data['count_format'] = $this->model_module_uploader->getCountFormats($formats, $session_id);

    $count_paper_arr = array();
    $count_paper_arr[1] = '';
    $counts_paper = $this->model_module_uploader->getRows('count_paper', 0);
    foreach($counts_paper as $count){
      $count_paper_arr[$count['name']] = $count['id'];
    }
    $formats = $this->model_module_uploader->getRows('format', 0);
    $formats_quality = array();
    foreach($formats as $format){
      $formats_quality[$format['id']] = array(
        'bad' => $format['bad'],
        'normal' => $format['normal'],
        'good' => $format['good']
      );
    }

    $price = $this->model_module_uploader->getPrices();
    $images = $this->model_module_uploader->getImages($session_id);
    $data['total_full_price'] = $data['total_count'] = $data['total_price'] = 0;
    $data['item_price'] = array();
    $data['item_quality'] = array();
    foreach($images as $image){
      $image_price_option = 0;
      $options = json_decode($image['options'], true);
      foreach($options as $key => $option){
        if($option['type'] == 'select'){
          $image_price_option += (float)$price['option-' . $key . '-' . $option['value'] . '__'];
        }else{
          $image_price_option += (float)$price['option-' . $key . '__'];
        }
      }

      $count_id = '';
      foreach($count_paper_arr as $key => $count){
        if($key < $data['count_format'][$image['format_id']]){
          $count_id = $count;
        }
      }

      $image_price = (float)$price['price_' . $image['format_id'] . '_' . $count_id] * $image['copy_count'];
      $image_price = $image_price + $image_price_option;

      $image_full_price = (float)$price['price_' . $image['format_id'] . '_'] * $image['copy_count'];
      $image_full_price = $image_full_price + $image_price_option;

      $data['item_price'][$image['name']] = $this->currency->format($image_price);

      $quality = $this->setQuality($formats_quality[(int)$image['format_id']], $image['size']);
      $data['item_quality'][$image['name']] = array(
        'text' => $this->language->get('text_quality_' . $quality),
        'class' => $quality
      );

      $data['total_price'] += $image_price;
      $data['total_full_price'] += $image_full_price;
      $data['total_count'] += $image['copy_count'];
    }

    $data['total_price'] = $this->currency->format($data['total_price']);
    $data['total_full_price'] = $this->currency->format($data['total_full_price']);

    return $data;
  }

  private function saveImage($file_upload, $session_id){
    $file_type = substr($file_upload['name'], strrpos($file_upload['name'], '.') + 1);
    if(!file_exists(DIR_IMAGE . "uploader_tmp/" . $session_id . "/")){
      @mkdir(DIR_IMAGE . "uploader_tmp/" . $session_id . "/", 0777);
    }
    $image_path = DIR_IMAGE . "uploader_tmp/" . $session_id . "/" . md5($file_upload['name'] . random_bytes(5)) . "." . $file_type;
    if(isset($file_upload['tmp_name'])){
      move_uploaded_file($file_upload['tmp_name'], $image_path);
    }else if(isset($file_upload['copy_name'])){
      copy($file_upload['copy_name'], $image_path);
    }else if(isset($file_upload['social_name'])){
      file_put_contents($image_path, $this->getTarget($file_upload['social_name']));
      //var_dump($this->getTarget($file_upload['social_name']));
    }
    $this->load($image_path);
    return $image_path;
  }

  private function getTarget($url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = array();
    $headers[] = "Cookie: mid=W9SBpgAEAAGdwZ7TV1a1yx-l9y9W; mcd=3; csrftoken=ctebMkMOsBANk5xpoBLUd1t9is94K9tI; shbid=10692; ds_user_id=1386665409; rur=ATN; shbts=1543750445.2428164; sessionid=IGSC8ebff0fb286606ac3b9e3a9e9f72b066363ac64f1574fb1e6475f884e7863c14%3AdbiwJ3TxrXBjX9WzNgQSDpAvWYNzA6Ay%3A%7B%22_auth_user_id%22%3A1386665409%2C%22_auth_user_backend%22%3A%22accounts.backends.CaseInsensitiveModelBackend%22%2C%22_token%22%3A%221386665409%3A5nEQ3MQ7hRUcF7X1eAnKEZuL7BHgJM7y%3A0d30671d02936135656b954d0ccc10c2d26d38fb632f34d4fb93721604dfaff0%22%2C%22_platform%22%3A4%2C%22_remote_ip%22%3A%2291.222.220.66%22%2C%22_mid%22%3A%22W9SBpgAEAAGdwZ7TV1a1yx-l9y9W%22%2C%22_user_agent_md5%22%3A%22007875cd98f94237faa88c9dc5d00425%22%2C%22_token_ver%22%3A2%2C%22last_refreshed%22%3A1543750445.2441196442%7D; urlgen=\"{\"91.222.220.66\": 9164054 \"91.222.222.49\": 9164}:1gTdWY:SCFto2WvmYdiDcMTxMKS7gXUvFM\"";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $image = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return $image;
  }

  private function scale($max) {
    $width = imagesx($this->image);
    $height = imagesy($this->image);

    $ratio = min($max / $width, $max / $height);

    $new_width = $width * $ratio;
    $new_height = $height * $ratio;

    $this->resize($new_width, $new_height);
  }

  private function load($filename) {
    $image_info = getimagesize($filename);
    $this->image_type = $image_info['mime'];
    if($this->image_type == "image/jpeg") {
       $this->image = imagecreatefromjpeg($filename);
    }else if($this->image_type == "image/gif") {
       $this->image = imagecreatefromgif($filename);
    }else if($this->image_type == "image/png") {
       $this->image = imagecreatefrompng($filename);
    }
  }

  function resize($width, $height) {
    $new_image = imagecreatetruecolor($width, $height);
    imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, imagesx($this->image), imagesy($this->image));
    $this->image = $new_image;
  }

  function output() {
    if($this->image_type == "image/jpeg") {
       imagejpeg($this->image);
    }else if($this->image_type == "image/gif") {
       imagegif($this->image);
    }else if($this->image_type == "image/png") {
       imagepng($this->image);
    }
  }

  private function baseImage(){
    $this->scale(260);
    ob_start();
      $this->output();
      $data = ob_get_contents();
    ob_end_clean();
    return 'data:' . $this->image_type . ';base64,' . base64_encode($data);
  }

  private function removeDirectory($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
       }
    }
    rmdir($dir);
  }

  private function setQuality($options, $size){
    if($size>($options['good']*1048576)){
      $quality = 'good';
    }else if($size>($options['normal']*1048576)){
      $quality = 'normal';
    }else if($size>($options['bad']*1048576)){
      $quality = 'bad';
    }else{
      $quality = 'very_bad';
    }
    return $quality;
  }

  public function upload(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      $this->load->model('module/uploader');
      $this->load->language('module/uploader');
      $default = $this->model_module_uploader->getDefault(0);

      $options = array();
      foreach($default['options'] as $option){
        $options[$option['option_id']] = array(
          'type' => 'select',
          'value' => $option['value_id']
        );
      }

      if(isset($this->request->cookie['uis'])){
        $uis = $this->request->cookie['uis'];
      }else{
        $json['error'] = $this->language->get('error_uis');
      }

      $old = umask(0);
      if(!isset($json['error'])){
        $session_id = hash("sha256", $uis);
        $upload_images = array();
        if(!file_exists(DIR_IMAGE . "uploader_tmp/")){
            @mkdir(DIR_IMAGE . "uploader_tmp/", 0777);
        }

        if(isset($this->request->files['file_upload'])){
          $file_upload = $this->request->files['file_upload'];

          if(strpos($file_upload['type'], "image") !== false){
            $tmp_image = array(
              'session_id' => $session_id,
              'name' => md5($file_upload['name'] . date("j, n, Y H:i") . random_bytes(5)),
              'path' => $this->saveImage($file_upload, $session_id),
              'base' => $this->baseImage(),
              'format_id' => $default['format']['id'],
              'paper_type_id' => $default['paper_type']['id'],
              'set_in_format' => 0,
              'quality' => $this->setQuality($default['format'], $file_upload['size']),
              'options' => json_encode($options),
              'copy_count' => 1,
              'size' => $file_upload['size']
            );

            $this->model_module_uploader->addImage($tmp_image);

            unset($tmp_image['session_id'], $tmp_image['path'], $tmp_image['size']);
            $upload_images[] = $tmp_image;
          }else if(strpos($file_upload['type'], "zip") !== false){
            $file_type = substr($file_upload['name'], strrpos($file_upload['name'], '.') + 1);
            $zip_path = DIR_IMAGE . "uploader_tmp/" . md5($file_upload['name'] . random_bytes(5)) . "/";

            $zip = new ZipArchive();
            if($zip->open($file_upload['tmp_name']) === true){
              @mkdir($zip_path, 0777);
              $zip->extractTo($zip_path);
              $zip->close();
            }else{
              $json['error'] = $this->language->get('error_zip');
            }

            if(!isset($json['error'])){
              $images = scandir($zip_path);
              if($images !== false){
                $images = preg_grep("/\.(?:png|gif|jpe?g)$/i", $images);
                if(is_array($images)){
                  foreach($images as $image){
                    $filesize = filesize($zip_path . $image);
                    $tmp_image = array(
                      'session_id' => $session_id,
                      'name' => md5($tmp_image . date("j, n, Y H:i") . random_bytes(5)),
                      'path' => $this->saveImage(array('name' => $image, 'copy_name' => $zip_path . $image), $session_id),
                      'base' => $this->baseImage(),
                      'format_id' => $default['format']['id'],
                      'paper_type_id' => $default['paper_type']['id'],
                      'set_in_format' => 0,
                      'quality' => $this->setQuality($default['format'], $filesize),
                      'options' => json_encode($options),
                      'copy_count' => 1,
                      'size' => $filesize
                    );

                    $this->model_module_uploader->addImage($tmp_image);

                    unset($tmp_image['session_id'], $tmp_image['path'], $tmp_image['size']);
                    $upload_images[] = $tmp_image;
                  }
                }
              }else{
                $json['error'] = $this->language->get('error_zip_empty');
              }
              $this->removeDirectory($zip_path);
            }
          }else{
            $json['error'] = $this->language->get('error_format');
          }
        }else if(isset($this->request->post['social_upload'])){
           $social_upload = json_decode(html_entity_decode(urldecode($this->request->post['social_upload'])), true);
           foreach($social_upload as $social_image){
             $tmp_image = array(
               'session_id' => $session_id,
               'name' => md5($social_image . date("j, n, Y H:i") . random_bytes(5)),
               'path' => $this->saveImage(array('name' => md5(random_bytes(10)) . '.jpg', 'social_name' => $social_image), $session_id),
               'base' => $this->baseImage(),
               'format_id' => $default['format']['id'],
               'paper_type_id' => $default['paper_type']['id'],
               'set_in_format' => 0,
               'options' => json_encode($options),
               'copy_count' => 1
             );

             $filesize = filesize($tmp_image['path']);

             $tmp_image['quality'] = $this->setQuality($default['format'], $filesize);
             $tmp_image['size'] = $filesize;
             $this->model_module_uploader->addImage($tmp_image);

             unset($tmp_image['session_id'], $tmp_image['path'], $tmp_image['size']);
             $upload_images[] = $tmp_image;
           }
        }
      }
      umask($old);

      if(empty($json['error'])){
        $json['success'] = array();
        $json['success']['data'] = $this->getData($session_id);
        foreach($upload_images as $key => $image){
          $upload_images[$key]['price'] = $json['success']['data']['item_price'][$image['name']];
        }
        $json['success']['uploaded'] = $upload_images;
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function delete(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      $this->load->model('module/uploader');
      $this->load->language('module/uploader');

      if(isset($this->request->cookie['uis'])){
        $uis = $this->request->cookie['uis'];
      }else{
        $json['error'] = $this->language->get('error_uis');
      }

      if(!isset($json['error'])){
        $session_id = hash("sha256", $uis);
        $images = json_decode(html_entity_decode(urldecode($this->request->post['items'])), true);
        foreach($images as $image){
          $path = $this->model_module_uploader->deleteImage($image, $session_id);
          if(file_exists($path)){
            if(!unlink($path)){
              $json['error'] = $this->language->get('error_image_delete');
            }
          }else{
            $json['error'] = $this->language->get('error_image_exists');
          }
        }
      }
      if(!isset($json['error'])){
        $json['success'] = $this->getData($session_id);
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function update(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      $this->load->model('module/uploader');
      $this->load->language('module/uploader');

      if(isset($this->request->cookie['uis'])){
        $uis = $this->request->cookie['uis'];
      }else{
        $json['error'] = $this->language->get('error_uis');
      }

      if(!isset($json['error'])){
        $session_id = hash("sha256", $uis);
        $images = json_decode(html_entity_decode(urldecode($this->request->post['items'])), true);
        $values = json_decode(html_entity_decode(urldecode($this->request->post['values'])), true);
        $this->model_module_uploader->getOptionRows("image");

        foreach($images as $image){
          $options = null;
          foreach($values as $key => $value){
            if(strpos($key, "option") !== false){
              if(is_null($options)){
                $options = json_decode($this->model_module_uploader->getImageOption($image, $session_id), true);
              }
              $option_data = explode('_', $key);
              if(isset($options[$option_data[1]])){
                $options[$option_data[1]]['value'] = $value;
              }else{
                $type = $this->model_module_uploader->getOptionType($option_data[1], 'image');
                if(!is_null($type)){
                  $options[$option_data[1]]['value'] = $value;
                  $options[$option_data[1]]['type'] = $type;
                }
              }
            }else{
              $this->model_module_uploader->updateImage($key, $value, $image, $session_id);
            }
          }
          if(!is_null($options)){
            $this->model_module_uploader->updateImage("options", json_encode($options), $image, $session_id);
          }
        }
        if(!isset($json['error'])){
          $json['success'] = $this->getData($session_id);
        }
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function copy(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      $this->load->model('module/uploader');
      $this->load->language('module/uploader');

      if(isset($this->request->cookie['uis'])){
        $uis = $this->request->cookie['uis'];
      }else{
        $json['error'] = $this->language->get('error_uis');
      }

      if(!isset($json['error'])){
        $session_id = hash("sha256", $uis);
        $name = urldecode($this->request->post['item']);
        $image = $this->model_module_uploader->getImage($name, $session_id);
        $image['name'] = md5($image['name'] . date("j, n, Y H:i") . random_bytes(5));
        if(file_exists($image['path'])){
          $path = substr($image['path'], 0, strrpos($image['path'], '/'));

          $file = explode('.', strrchr($image['path'], '/'));
          $path .= "/" . md5($file[0] . random_bytes(5)) . "." . $file[1];
          $old = umask(0);
          copy($image['path'], $path);
          umask($old);
          $image['path'] = $path;
          $this->model_module_uploader->addImage($image);
          unset($image['id'], $image['session_id'], $image['path'], $image['size'], $image['date']);
          $json['success']['copy']= $image;
          $json['success']['data'] = $this->getData($session_id);
        }else{
          $json['error'] = $this->language->get('error_image_exists');
        }
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
}
?>
