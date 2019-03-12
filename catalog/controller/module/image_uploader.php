<?php
class ControllerModuleImageUploader extends Controller{
  private $image;
  private $image_type;

  public function index(){
    $this->load->model('module/uploader');
    $this->load->language('module/uploader');

    $this->data['image_uploader'] = $this->config->get('image_uploader');

    $this->document->addScript("catalog/view/javascript/uploader/SimpleAjaxUploader.min.js");
    $this->document->addScript("https://cdn.jsdelivr.net/npm/lodash@4.17.11/lodash.min.js");

    if(!empty($this->data['image_uploader']['instagram']['client_id']) || !empty($this->data['image_uploader']['facebook']['client_id'])){
      $this->document->addScript("catalog/view/javascript/uploader/js.cookie.min.js");
    }

    if(!empty($this->data['image_uploader']['facebook']['client_id'])){
      $this->document->addScript("catalog/view/javascript/uploader/facebook.js");
    }

    // if(empty($this->data['image_uploader']['instagram']['client_id'])){
    //   $this->document->addScript("catalog/view/javascript/uploader/instagram.js");
    // }

    $this->document->addScript("catalog/view/javascript/uploader/uploadermain.js");

    //temporarily style
    $this->document->addStyle("https://use.fontawesome.com/releases/v5.5.0/css/regular.css");
    $this->document->addStyle("https://use.fontawesome.com/releases/v5.5.0/css/solid.css");
    $this->document->addStyle("https://use.fontawesome.com/releases/v5.5.0/css/brands.css");
    $this->document->addStyle("https://use.fontawesome.com/releases/v5.5.0/css/fontawesome.css");
    //temporarily style

    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/uploader/image.tpl')) {
      $this->document->addStyle("catalog/view/theme/" . $this->config->get('config_template') . "/stylesheet/uploader.css");
      $this->template = $this->config->get('config_template') . '/template/uploader/image.tpl';
    } else {
      $this->document->addStyle("catalog/view/theme/default/stylesheet/uploader.css");
      $this->template = 'default/template/uploader/image.tpl';
    }

    $this->data['heading_title'] = $this->language->get('heading_title_image');
    $this->document->setTitle($this->data['heading_title']);
    if(isset($this->request->cookie['uis'])){
    	$uis = $this->request->cookie['uis'];
    	setcookie('uis', $uis, time() + 3600 * 24 * 7, '/');
    }else{
      $uis = md5(rand(0, 200) * rand(0, 200) . openssl_random_pseudo_bytes(5));
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
        'link' => $image['link'],
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
    $this->data['text_pc_upload_btn'] = $this->language->get('text_pc_upload_btn');
    $this->data['text_upload_or'] = $this->language->get('text_upload_or');
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
    $this->data['text_check_all'] = $this->language->get('text_check_all');
    $this->data['server_redirect'] = HTTPS_SERVER;

    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput($this->render());
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
    $images = array();
    $file_type = substr($file_upload['name'], strrpos($file_upload['name'], '.') + 1);
    if(!file_exists(DIR_IMAGE . "uploader_tmp/" . $session_id . "/")){
      @mkdir(DIR_IMAGE . "uploader_tmp/" . $session_id . "/", 0777);
    }
    $image_name = md5($file_upload['name'] . openssl_random_pseudo_bytes(5));
    $images['path'] = DIR_IMAGE . "uploader_tmp/" . $session_id . "/" . $image_name . "." . $file_type;
    if(isset($file_upload['tmp_name'])){
      move_uploaded_file($file_upload['tmp_name'], $images['path']);
    }else if(isset($file_upload['copy_name'])){
      copy($file_upload['copy_name'], $images['path']);
    }else if(isset($file_upload['social_name'])){
      file_put_contents($images['path'], $this->getTarget($file_upload['social_name']));
    }
    $this->load($images['path']);
    $this->scale(260);

    if(!file_exists(DIR_IMAGE . "uploader_base/" . $session_id . "/")){
      @mkdir(DIR_IMAGE . "uploader_base/" . $session_id . "/", 0777);
    }
    $images['base_path'] = DIR_IMAGE . "uploader_base/" . $session_id . "/" . $image_name . "." . $file_type;
    $this->output($images['base_path']);
    $images['base'] = HTTPS_SERVER . "image/uploader_base/" . $session_id . "/" . $image_name . "." . $file_type;
    $images['link'] = HTTPS_SERVER . "image/uploader_tmp/" . $session_id . "/" . $image_name . "." . $file_type;

    return $images;
  }

  private function getTarget($url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

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

  function output($to) {
    if($this->image_type == "image/jpeg") {
       imagejpeg($this->image, $to);
    }else if($this->image_type == "image/gif") {
       imagegif($this->image, $to);
    }else if($this->image_type == "image/png") {
       imagepng($this->image, $to);
    }
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
        if(!file_exists(DIR_IMAGE . "uploader_base/")){
          @mkdir(DIR_IMAGE . "uploader_base/", 0777);
        }

        if(isset($this->request->files['files_upload'])){
          $file_upload = $this->request->files['files_upload'];
          if(strpos($file_upload['type'], "image") !== false){
            $images_saved = $this->saveImage($file_upload, $session_id);
            $tmp_image = array(
              'session_id' => $session_id,
              'name' => md5($file_upload['name'] . date("j, n, Y H:i") . openssl_random_pseudo_bytes(5)),
              'path' => $images_saved['path'],
              'base_path' => $images_saved['base_path'],
              'base' => $images_saved['base'],
              'link' => $images_saved['link'],
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
          }else if((strpos($file_upload['type'], "zip") !== false) || (strpos($file_upload['type'], "rar") !== false)){

            $file_type = substr($file_upload['name'], strrpos($file_upload['name'], '.') + 1);
            $archive_path = DIR_IMAGE . "uploader_tmp/" . md5($file_upload['name'] . openssl_random_pseudo_bytes(5)) . "/";

            if(strpos($file_upload['type'], "zip") !== false){
              $zip = new ZipArchive();
              if($zip->open($file_upload['tmp_name']) === true){
                @mkdir($archive_path, 0777);
                $zip->extractTo($archive_path);
                $zip->close();
              }else{
                $json['error'] = $this->language->get('error_zip');
              }
            }else{
              $rar = RarArchive::open($file_upload['tmp_name']);
              if($rar !== false){
                @mkdir($archive_path, 0777);
                foreach($rar->getEntries() as $e){
                  $e->extract($archive_path);
                }
                $rar->close();
              }else{
                $json['error'] = $this->language->get('error_zip');
              }
            }

            if(!isset($json['error'])){
              $images = scandir($archive_path);
              if($images !== false){
                $images = preg_grep("/\.(?:png|gif|jpe?g)$/i", $images);
                if(is_array($images)){
                  foreach($images as $image){
                    $filesize = filesize($archive_path . $image);
                    $images_saved = $this->saveImage(array('name' => $image, 'copy_name' => $archive_path . $image), $session_id);
                    $tmp_image = array(
                      'session_id' => $session_id,
                      'name' => md5($image . date("j, n, Y H:i") . openssl_random_pseudo_bytes(5)),
                      'path' => $images_saved['path'],
                      'base_path' => $images_saved['base_path'],
                      'base' => $images_saved['base'],
                      'link' => $images_saved['link'],
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
              $this->removeDirectory($archive_path);
            }
          }else{
            $json['error'] = $this->language->get('error_format');
          }
        }else if(isset($this->request->post['social_upload'])){
           $social_upload = json_decode(html_entity_decode(urldecode($this->request->post['social_upload'])), true);
           foreach($social_upload as $social_image){
             $image_name = md5($social_image . date("j, n, Y H:i") . openssl_random_pseudo_bytes(5));
             $images_saved = $this->saveImage(array('name' => md5(openssl_random_pseudo_bytes(10)) . '.jpg', 'social_name' => $social_image), $session_id);
             $tmp_image = array(
               'session_id' => $session_id,
               'name' => $image_name,
               'path' => $images_saved['path'],
               'base_path' => $images_saved['base_path'],
               'base' => $images_saved['base'],
               'link' => $images_saved['link'],
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
          $results = $this->model_module_uploader->deleteImage($image, $session_id);
          try{
            unlink($results['path']);
            unlink($results['base_path']);
          }catch(Exception $e){
            $json['error'] = $this->language->get('error_image_exists') . " " . $e->getMessage();
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

        $json_exception = array();
        $json_exception['exception'] = $this->language->get('error_exception');
        $json_exception['exception_images'] = array();

        if(isset($values['paper_type_id']) || isset($values['format_id'])){
          $results = $this->model_module_uploader->getRows('exception');
          $exception = array();
          foreach($results as $result){
            $exception[$result['paper_type_id'] . '_' . $result['format_id']] = (int)$result['possibly'];
          }
        }

        if(isset($values['paper_type_id']) && isset($values['format_id'])){
          if(isset($exception[$values['paper_type_id'] . '_' . $values['format_id']]) && !$exception[$values['paper_type_id'] . '_' . $values['format_id']]){
            unset($values['paper_type_id'], $values['format_id']);
            $json_exception['exception_images'] = 'all';
          }
        }

        foreach($images as $image){
          $options = null;

          if(isset($values['paper_type_id']) || isset($values['format_id'])){
            $reset_image = $this->model_module_uploader->getImage($image, $session_id);
          }

          foreach($values as $key => $value){
            if(strpos($key, "option") !== false){
              if(is_null($options)){
                $options = json_decode($this->model_module_uploader->getImageOption($image, $session_id), true);
              }
              $option_data = explode('_', $key);
              if(isset($options[$option_data[1]])){
                if($options[$option_data[1]]['type'] == 'checkbox' && (int)$value == 0){
                  unset($options[$option_data[1]]);
                }else{
                  $options[$option_data[1]]['value'] = $value;
                }
              }else{
                $type = $this->model_module_uploader->getOptionType('image', $option_data[1]);
                if(($type == 'checkbox' && (int)$value != 0) || ($type == 'select')){
                  $options[$option_data[1]]['value'] = $value;
                  $options[$option_data[1]]['type'] = $type;
                }
              }
            }else if($key == "paper_type_id"){
              if(isset($exception[(int)$value . '_' . $reset_image['format_id']]) && !$exception[(int)$value . '_' . $reset_image['format_id']]){
                $json_exception['exception_images'][$image] = 'paper_type_id';
              }else{
                $this->model_module_uploader->updateImage($key, $value, $image, $session_id);
              }
            }else if($key == "format_id"){
              if(isset($exception[$reset_image['paper_type_id'] . '_' . (int)$value]) && !$exception[$reset_image['paper_type_id'] . '_' . (int)$value]){
                $json_exception['exception_images'][$image] = 'format_id';
              }else{
                $this->model_module_uploader->updateImage($key, $value, $image, $session_id);
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

          if(empty($json_exception['exception_images'])){
            unset($json_exception['exception_images'], $json['success']['exception']);
          }else{
            $json['success'] = array_merge($json['success'], $json_exception);
          }
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
        $image['name'] = md5($image['name'] . date("j, n, Y H:i") . openssl_random_pseudo_bytes(5));
        try{
          $path = substr($image['path'], 0, strrpos($image['path'], '/'));
          $base_path = substr($image['base_path'], 0, strrpos($image['base_path'], '/'));

          $file = explode('.', strrchr($image['path'], '/'));
          $new_file = "/" . md5($file[0] . openssl_random_pseudo_bytes(5)) . "." . $file[1];

          $path .= $new_file;
          $base_path .= $new_file;

          $old = umask(0);
          copy($image['path'], $path);
          copy($image['base_path'], $base_path);
          umask($old);

          $image['path'] = $path;
          $image['base_path'] = $base_path;
          $image['base'] = str_replace(DIR_IMAGE, HTTPS_SERVER . "image/", $base_path);
          $image['link'] = str_replace(DIR_IMAGE, HTTPS_SERVER . "image/", $path);

          $this->model_module_uploader->addImage($image);
          unset($image['id'], $image['session_id'], $image['path'], $image['size'], $image['date'], $image['base_path']);

          $json['success']['copy']= $image;
          $json['success']['data'] = $this->getData($session_id);
        }catch(Exception $e){
          $json['error'] = $this->language->get('error_copy') . " " . $e->getMessage();
        }
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function getArticle(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      $this->load->model('module/uploader');

      $article = $this->model_module_uploader->getArticle($this->request->post['article_id']);

      $json['success'] = array(
        'title' => $article['title'],
        'description' => $article['description']
      );
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function addToCart(){
    $json = array();
    if($this->request->server['REQUEST_METHOD'] == 'POST'){
      if(isset($this->request->cookie['uis'])){
        $uis = $this->request->cookie['uis'];
      }else{
        $json['error'] = $this->language->get('error_uis');
      }

      if(!isset($json['error'])){
        $this->load->model('module/uploader');
        $this->load->language('module/uploader');
        $session_id = hash("sha256", $uis);



        $data = array();
        $formats = $this->model_module_uploader->getRows('format', 0);
        $paper_types = $this->model_module_uploader->getRows('paper_type', 0);
        $options_available = $this->model_module_uploader->getRows('option', 0);
        $option_values = $this->model_module_uploader->getRows('option_value', 0);

        $price = $this->model_module_uploader->getPrices();
        $images = $this->model_module_uploader->getImages($session_id);
        $count_format = $this->model_module_uploader->getCountFormats($formats, $session_id);
        $data['total_full_price'] = $data['total_count'] = $data['total_price'] = 0;
        $data['names'] = array();

        $count_paper_arr = array();
        $count_paper_arr[1] = '';
        $counts_paper = $this->model_module_uploader->getRows('count_paper', 0);
        foreach($counts_paper as $count){
          $count_paper_arr[$count['name']] = $count['id'];
        }

        foreach($formats as $key => $format){
          $formats[$format['id'] . '-'] = $format['name'];
          unset($formats[$key]);
        }

        foreach($paper_types as $key => $paper_type){
          $paper_types[$paper_type['id'] . '-'] = mb_strimwidth($paper_type['name'], 0, 4);
          unset($paper_types[$key]);
        }

        foreach($options_available as $key => $option){
          $options_available[$option['id'] . '-']['name'] = mb_strimwidth($option['name'], 0, 4);
          foreach($option['values'] as $val){
            $options_available[$option['id'] . '-']['values'][$val['id']] = mb_strimwidth($val['text'], 0, 4);;
          }
          unset($options_available[$key]);
        }

        foreach($images as $img_key => $image){
          $image_name = $formats[$image['format_id'] . '-'] . '__' . $paper_types[$image['paper_type_id'] . '-'] . '__кол-во-' . $image['copy_count'] . '__впис-' . $image['set_in_format'] . '__';

          $image_price_option = 0;
          $options = json_decode($image['options'], true);
          foreach($options as $key => $option){
            if($option['type'] == 'select'){
              $image_name .= $options_available[$key . '-']['name'] . '-' . $options_available[$key . '-']['values'][$option['value']] . '__';
              $image_price_option += (float)$price['option-' . $key . '-' . $option['value'] . '__'];
            }else{
              $image_name .= $options_available[$key . '-']['name'] . '-1__';
              $image_price_option += (float)$price['option-' . $key . '__'];
            }
          }

          $image_name = substr($image_name, 0, -2);

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


          $data['total_price'] += $image_price;
          $data['total_full_price'] += $image_full_price;
          $data['total_count'] += $image['copy_count'];
          $data['names'][$img_key] = $image_name;
        }

        $name = sprintf($this->language->get('text_image_uploader_name'), $data['total_count']);

        $json['success']['id'] = $product_id = $this->model_module_uploader->addProduct($session_id, $name, $data['total_full_price'], $data['total_price']);

        $old = umask(0);
        if(!file_exists(DIR_IMAGE . "uploader_confirm/")){
          @mkdir(DIR_IMAGE . "uploader_confirm/", 0777);
        }
        if(!file_exists(DIR_IMAGE . "uploader_confirm/" . $product_id . "/")){
          @mkdir(DIR_IMAGE . "uploader_confirm/" . $product_id . "/", 0777);
        }

        asort($data['names']);
        $i = 1;
        foreach($data['names'] as $key => $name){
          $file = explode('.', strrchr($images[$key]['path'], '/'));
          $new_path = DIR_IMAGE . 'uploader_confirm/' . $product_id . '/' . $i . '_' . $name . '.' . $file[1];
          copy($images[$key]["path"], $new_path);
          $this->model_module_uploader->updateImage("path", $new_path, $images[$key]['name'], $session_id);
          $i++;
        }

        $this->removeDirectory(DIR_IMAGE . 'uploader_base/' . $session_id);
        $this->removeDirectory(DIR_IMAGE . 'uploader_tmp/' . $session_id);
        umask($old);

        $uis = md5(rand(0, 200) * rand(0, 200) . openssl_random_pseudo_bytes(5));
      	setcookie('uis', $uis, time() + 3600 * 24 * 7, '/');
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
}
?>
