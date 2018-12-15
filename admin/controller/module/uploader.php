<?php
class ControllerModuleUploader extends Controller {
	private $error = array();

	public function install(){
		$this->load->model('module/uploader');
		$this->model_module_uploader->install();
		$this->cache->delete('seo_pro');
	}

	public function uninstall(){
		$this->load->model('module/uploader');
		$this->model_module_uploader->remove();
		$this->cache->delete('seo_pro');
	}

  public function index(){
    $this->load->language('module/uploader');
    $this->data['heading_title'] = $this->language->get('heading_title');
    $this->document->setTitle($this->data['heading_title']);
    $this->document->addStyle("view/stylesheet/uploader.css");
		$this->document->addScript('view/javascript/jquery/tabs.js');
    $this->load->model('module/uploader');

    $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
        'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_module'),
        'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => ' :: '
    );

    $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('heading_title'),
        'href'      => $this->url->link('module/uploader', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => ' :: '
    );

    $this->data['tab_image_upload'] = $this->language->get('tab_image_upload');
    $this->data['tab_document_upload'] = $this->language->get('tab_document_upload');

    $this->data['button_add'] = $this->language->get('button_add');
    $this->data['button_remove'] = $this->language->get('button_remove');
    $this->data['button_save'] = $this->language->get('button_save');
    $this->data['button_reload'] = $this->language->get('button_reload');

    $this->data['text_sort_pl'] = $this->language->get('text_sort_pl');
    $this->data['token'] = $this->request->get['token'];

    $this->data['link_image_upload'] = $this->url->link('module/uploader', 'type=images&token=' . $this->session->data['token'], 'SSL');
    $this->data['link_document_upload'] = $this->url->link('module/uploader', 'type=documents&token=' . $this->session->data['token'], 'SSL');


    $this->data['content'] = "";
    if(isset($this->request->get['type'])){
      $func =  "tab" . lcfirst($this->request->get['type']);
      $this->data['content'] = $this->$func();
      $this->data['main_tab'] = $this->request->get['type'];
    }else{
      $this->data['content'] = $this->tabImages();
      $this->data['main_tab'] = "images";
    }

    $this->template = 'module/uploader/main_settings.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
  }

  private function tabImages(){
		$this->data['text_link'] = $this->language->get('text_link');
		$this->data['text_link_pl'] = sprintf($this->language->get('text_link_label'), HTTP_CATALOG, $this->model_module_uploader->getLink("image"));
		$this->data['text_image_format'] = $this->language->get('text_image_format');
		$this->data['text_image_format_pl'] = $this->language->get('text_image_format_pl');
		$this->data['text_image_format_ratio_pl'] = $this->language->get('text_image_format_ratio_pl');
		$this->data['text_image_format_pix_pl'] = $this->language->get('text_image_format_pix_pl');
		$this->data['text_paper_type'] = $this->language->get('text_paper_type');
		$this->data['text_paper_type_pl'] = $this->language->get('text_paper_type_pl');
		$this->data['text_image_quality'] = $this->language->get('text_image_quality');
		$this->data['text_quality_bad'] = $this->language->get('text_quality_bad');
		$this->data['text_quality_normal'] = $this->language->get('text_quality_normal');
		$this->data['text_quality_good'] = $this->language->get('text_quality_good');
		$this->data['text_count_paper'] = $this->language->get('text_count_paper');
		$this->data['text_count_paper_pl'] = $this->language->get('text_count_paper_pl');
		$this->data['text_quality_bad_pl'] = $this->language->get('text_quality_bad_pl');
		$this->data['text_quality_normal_pl'] = $this->language->get('text_quality_normal_pl');
		$this->data['text_quality_good_pl'] = $this->language->get('text_quality_good_pl');
		$this->data['text_option'] = $this->language->get('text_option');
		$this->data['text_option_type'] = $this->language->get('text_option_type');
		$this->data['text_option_article'] = $this->language->get('text_option_article');
		$this->data['text_option_pl'] = $this->language->get('text_option_pl');
		$this->data['text_option_checkbox'] = $this->language->get('text_option_checkbox');
		$this->data['text_option_select'] = $this->language->get('text_option_select');
		$this->data['text_facebook'] = $this->language->get('text_facebook');
		$this->data['text_instagram'] = $this->language->get('text_instagram');
		$this->data['text_social_limit_photo'] = $this->language->get('text_social_limit_photo');
		$this->data['text_social_limit_albums'] = $this->language->get('text_social_limit_albums');
		$this->data['text_social_allowed_formats'] = $this->language->get('text_social_allowed_formats');
		$this->data['text_social_allowed_formats_rar'] = $this->language->get('text_social_allowed_formats_rar');
		$this->data['text_social_allowed_formats_zip'] = $this->language->get('text_social_allowed_formats_zip');
		$this->data['text_social_allowed_formats_jpg'] = $this->language->get('text_social_allowed_formats_jpg');
		$this->data['text_social_allowed_formats_gif'] = $this->language->get('text_social_allowed_formats_gif');
		$this->data['text_social_allowed_formats_png'] = $this->language->get('text_social_allowed_formats_png');

		$this->data['formats'] = $this->model_module_uploader->getRows("format");
		$this->data['paper_types'] = $this->model_module_uploader->getRows("paper_type", 0);
		$this->data['counts_paper'] = $this->model_module_uploader->getRows("count_paper", 0);
		$this->data['options'] = $this->model_module_uploader->getOptionRows(0);

		if($this->config->get('facebook_client_id')){
			$this->data['facebook_client_id'] = $this->config->get('facebook_client_id');
		}else{
			$this->data['facebook_client_id'] = '';
		}

		if($this->config->get('facebook_photos_limit')){
			$this->data['facebook_photos_limit'] = $this->config->get('facebook_photos_limit');
		}else{
			$this->data['facebook_photos_limit'] = '';
		}

		if($this->config->get('facebook_albums_limit')){
			$this->data['facebook_albums_limit'] = $this->config->get('facebook_albums_limit');
		}else{
			$this->data['facebook_albums_limit'] = '';
		}

		if($this->config->get('instagram_client_id')){
			$this->data['instagram_client_id'] = $this->config->get('instagram_client_id');
		}else{
			$this->data['instagram_client_id'] = '';
		}

		if($this->config->get('instagram_photos_limit')){
			$this->data['instagram_photos_limit'] = $this->config->get('instagram_photos_limit');
		}else{
			$this->data['instagram_photos_limit'] = '';
		}

		if($this->config->get('image_allowed_formats')){
			$this->data['image_allowed_formats'] = $this->config->get('image_allowed_formats');
		}else{
			$this->data['image_allowed_formats'] = array();
		}

    $this->template = 'module/uploader/images.tpl';
		return $this->render();
  }

  private function tabDocuments(){

    $this->template = 'module/uploader/documents.tpl';
    return $this->render();
  }

	public function autocompleteArticle() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('module/uploader');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			$data = array(
				'filter_name'  => $filter_name,
				'filter_id'		 => null
			);

			$results = $this->model_module_uploader->getArticles($data);
			foreach ($results as $result) {
				$json[] = array(
					'article_id' => $result['information_id'],
					'title'      => html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		$this->response->setOutput(json_encode($json));
	}

	public function saveOptionValue(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');

			$json['success'] = $this->model_module_uploader->setOptionValue($this->request->post);
			$this->response->addHeader('Content-Type: application/json');
		}else{
			$json['error'] = true;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function saveRow(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');

			$function_elements = explode('_', $this->request->post['group']);
			$model_function = "set";
			foreach($function_elements as $e){
				$model_function .= ucfirst($e);
			}

			$json['success'] = $this->model_module_uploader->$model_function($this->request->post);
			$this->response->addHeader('Content-Type: application/json');
		}else{
			$json['error'] = true;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeRow(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');
			$this->model_module_uploader->removeRow($this->request->post);
			$json['success'] = true;
		}else{
			$json['error'] = true;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function updateRow(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');

			$this->model_module_uploader->updateRow($this->request->post);
			$json['success'] = true;
		}else{
			$json['error'] = true;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getSecondSettingsImage(){
		$this->load->model('module/uploader');
		$this->load->language('module/uploader');

		$this->data['tab_price'] = $this->language->get('tab_price');
		$this->data['tab_option'] = $this->language->get('tab_option');
		$this->data['tab_quality'] = $this->language->get('tab_quality');
		$this->data['h3_price_format'] = $this->language->get('h3_price_format');
		$this->data['h3_price_option'] = $this->language->get('h3_price_option');
		$this->data['text_sort_pl'] = $this->language->get('text_sort_pl');
		$this->data['button_remove'] = $this->language->get('button_remove');
		$this->data['button_add'] = $this->language->get('button_add');
		$this->data['text_quality_bad'] = $this->language->get('text_quality_bad');
		$this->data['text_quality_normal'] = $this->language->get('text_quality_normal');
		$this->data['text_quality_good'] = $this->language->get('text_quality_good');
		$this->data['text_pre'] = $this->language->get('text_pre');
		$this->data['text_set_info_option'] = $this->language->get('text_set_info_option');
		$this->data['text_set_info'] = $this->language->get('text_set_info');
		$this->data['text_option_name_pr'] = $this->language->get('text_option_name_pr');
		$this->data['text_option_value_pr'] = $this->language->get('text_option_value_pr');

		$this->data['button_reload'] = $this->language->get('button_reload');
		$this->data['button_save'] = $this->language->get('button_save');

		$this->data['formats'] = $this->model_module_uploader->getRows("format");
		$this->data['paper_types'] = $this->model_module_uploader->getRows("paper_type");
		$this->data['counts_paper'] = $this->model_module_uploader->getRows("count_paper");
		$this->data['options'] = $this->model_module_uploader->getOptionRows(0);
		$this->data['price'] = $this->model_module_uploader->getPrices();
		$this->data['select_options_count'] = $this->model_module_uploader->getCountSelectOption(0);

		$this->data['token'] = $this->request->get['token'];
		$this->data['type'] = "image";

		$this->template = 'module/uploader/second_settings.tpl';
		$this->response->setOutput($this->render());
	}

	public function savePrices(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');
			foreach($this->request->post as $key => $row){
				if(empty($row)){
					$row = 0;
				}
				$val = explode('_', $key);
				$data = array(
					'key_text' => $val[0],
					'format_id' => $val[1]==''?NULL:(int)$val[1],
					'count_paper_id' => $val[2]==''?NULL:(int)$val[2],
					'price' => (float)$row
				);
				$this->model_module_uploader->savePrice($data);
			}
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function saveQualitys(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('module/uploader');
			foreach($this->request->post['quality'] as $key => $row){
				$data = array(
					'id' => $key,
					'bad' => $row['bad'],
					'normal' => $row['normal'],
					'good' => $row['good']
				);
				$this->model_module_uploader->saveQuality($data);
			}
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function socialSettings(){
		$json = array();
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			$this->load->model('setting/setting');
			$this->load->model('module/uploader');
			$this->model_setting_setting->editSetting('uploader', $this->request->post);

			$this->model_module_uploader->saveLink($this->request->post);
			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>
