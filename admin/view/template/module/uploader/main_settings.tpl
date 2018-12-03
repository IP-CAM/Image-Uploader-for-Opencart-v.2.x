<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="tabs-bar">
    <?php if($main_tab == "images"){ ?><p><?php echo $tab_image_upload; ?></p><?php } else { ?><a href="<?php echo $link_image_upload; ?>"><?php echo $tab_image_upload; ?></a><?php } ?>
    <?php if($main_tab == "documents"){ ?><p><?php echo $tab_document_upload; ?></p><?php } else { ?><a href="<?php echo $link_document_upload; ?>"><?php echo $tab_document_upload; ?></a><?php } ?>
  </div>
  <div class="tab-box">
    <?php echo $content; ?>
    <div class="second-settings">
      <div class="second-settings_reload">
        <button class="button reload"><?php echo $button_reload; ?></button>
      </div>
      <div class="second-settings_wrap"></div>
    </div>
  </div>
</div>
<script><!--

$(document).on('click', '.reload', function(){
  $('.second-settings_wrap').load(secondSettingsLink, function(response, status, xhr){
    if(status == "success"){
      secondScript();
      $('.second-settings').removeClass('reload-settings-wrap');
    }else{
      alert("Error load second settings!");
    }
  });
});

$('.second-settings_wrap').load(secondSettingsLink, function(response, status){
  if(status == "success"){
    secondScript();
  }
});

$(document).on('ready', function(){
  autocompleteArticle();
});

// поля

$(document).on('click', '.tab-box_main .add', function(){
  $('.second-settings_reload').parent().addClass('reload-settings-wrap');
  var dataGroup = $(this).parent().parent(),
      group = dataGroup.attr('data-group-name'),
      trigger = true, data = '';

  $(this).parent().find('input, select').each(function(i, input){
    data += $(input).attr('name') + '=' + input.value + '&';
    if(!input.value.length && $(input).attr('type') != 'hidden'){
      input.focus();
      trigger = false;
    }
  });

  if(!trigger){
    return;
  }

  $.ajax({
    url: 'index.php?route=module/uploader/saveRow&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: data + 'group=' + group,
    success: function(json){
      if(json['success']){
        html = '<li lass="list-item" ';
        switch (group) {
          case 'format':
            html += 'data-item-id="' + json['success']['id'] + '">';
            html += '<input type="radio" name="default_value-format">';
            html += ' <input type="text" name="sort" value="' + json['success']['sort'] + '" placeholder="<?php echo $text_sort_pl; ?>">';
            html += ' <input type="text" name="name" value="' + json['success']['name'] + '" placeholder="<?php echo $text_image_format_pl; ?>">';
            html += ' <input type="text" name="ratio" value="' + json['success']['ratio'] + '" placeholder="<?php echo $text_image_format_ratio_pl; ?>">';
            html += ' <button class="button remove" data-id="' + json['success']['id'] + '"><?php echo $button_remove; ?></button>';
            break;
          case 'paper_type':
            html += 'data-item-id="' + json['success']['id'] + '">';
            html += '<input type="radio" name="default_value-paper_type">';
            html += ' <input type="text" name="sort" value="' + json['success']['sort'] + '" placeholder="<?php echo $text_sort_pl; ?>">';
            html += ' <input type="text" name="name" value="' + json['success']['name'] + '" placeholder="<?php echo $text_paper_type_pl; ?>">';
            html += ' <button class="button remove" data-id="' + json['success']['id'] + '"><?php echo $button_remove; ?></button>';
            break;
          case 'count_paper':
            html += 'data-item-id="' + json['success']['id'] + '">';
            html += '<input type="text" name="name" value="' + json['success']['name'] + '" placeholder="<?php echo $text_count_paper_pl; ?>">';
            html += ' <button class="button remove" data-id="' + json['success']['id'] + '"><?php echo $button_remove; ?></button>';
            break;
          case 'option':
            html += 'data-item-id="' + json['success']['id'] + '">';
            html += '<input type="text" name="name" value="' + json['success']['name'] + '" placeholder="<?php echo $text_option_pl; ?>">';
            if(json['success']['type'] == "checkbox"){
              html += ' <p class="item-text"><?php echo $text_option_checkbox; ?></p>';
            }else if(json['success']['type'] == "select"){
              html += ' <p class="item-text"><?php echo $text_option_select; ?></p>';
            }
            html += ' <input tyle="text" value="' + json['success']['article_title'] + '" class="autocomplete-article" data-set="autocomplete-article_' + json['success']['id'] + '" title="<?php echo $text_option_article; ?>">';
            html += '<input type="hidden" id="autocomplete-article_' + json['success']['id'] + '" name="article_id" value="' + json['success']['article_id'] + '">';
            html += ' <button class="button remove" data-id="' + json['success']['id'] + '"><?php echo $button_remove; ?></button>';
            break;
          default:
            alert('Error add new row!');
            return;
        }
        html += '</li>';
        dataGroup.find('.list-wrap').append(html);
        $('.form-group').find('input').val('');
        autocompleteArticle();
      }else{
        alert('server error add new row');
      }
    }
  });
});

$(document).on('click', '.remove', function(){
  var parentClass = $(this).parent().parent().parent(),
      group = parentClass.attr('data-group-name'),
      self = $(this).parent();
  if(group == "option_value")
    $('.second-settings .tabs-bar').addClass('reload-tabs');
  else
    $('.second-settings_reload').parent().addClass('reload-settings-wrap');
  $.ajax({
    url: 'index.php?route=module/uploader/removeRow&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: 'group=' + group + '&id=' + $(this).attr('data-id'),
    success: function(json){
      if(json['success']){
        if(group == "option_value")
        updateHeight(parentClass.find('.add').attr('data-id'), -24);
        self.remove();
      }
    }
  });
});

$(document).on('change', '.tab-box_main .list-wrap input, .tab-box_main .list-wrap select', function(){
  if($(this).hasClass('autocomplete-article')){
    var self = this;
    setTimeout(function(){
      updateRow($(self).parent().find('input[name=\"article_id\"]'));
    },200);
    return;
  }
  updateRow(this);
});

var updateRow = function(self){
  $('.second-settings_reload').parent().addClass('reload-settings-wrap');
  var dataGroup, group, id, updatedValue, updatedName;
  dataGroup = $(self).parent().parent().parent(),
  group = dataGroup.attr('data-group-name'),
  id = $(self).parent().attr('data-item-id');
  updatedValue = $(self).val(),
  updatedName = $(self).attr('name');
  if(!updatedValue.length){
    $(self).focus();
    return;
  }

  $.ajax({
    url: 'index.php?route=module/uploader/updateRow&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: 'value=' + updatedValue + '&col=' + updatedName + '&group=' + group + '&id=' + id,
    success: function(json){
      dataGroup.removeClass("error-update");
      dataGroup.removeClass("success-update");
      if(json['success']){
        dataGroup.addClass("success-update");
        setTimeout(function(){dataGroup.removeClass("success-update");}, 600);
      }else{
        dataGroup.addClass("error-update");
        setTimeout(function(){dataGroup.removeClass("error-update");}, 600);
      }
    },
    error: function(jqXHR, exception){
      dataGroup.addClass("error-update");
      setTimeout(function(){dataGroup.removeClass("error-update");}, 600);
    }
  });
}

var autocompleteArticle = function(){
  $('.autocomplete-article').autocomplete({
  	delay: 0,
  	source: function(request, response) {
  		$.ajax({
  			url: 'index.php?route=module/uploader/autocompleteArticle&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
  			dataType: 'json',
  			success: function(json) {
  				response($.map(json, function(item) {
  					return {
  						label: item.title,
  						value: item.article_id
  					}
  				}));
  			}
  		});
  	},
  	select: function(event, ui) {
  		$(this).val(ui.item.label);
  		$("#"+$(this).attr('data-set')).val(ui.item.value);
  		return false;
  	},
  	focus: function(event, ui) {
    	return false;
    }
  });
}

var updateHeight = function(parentId, size){
  var grid = document.querySelector('#options');
  var item = document.querySelector('#options .option-' + parentId);

  const rowSpan = Math.round(
    ((Math.round(item.offsetHeight  + 5) + 0.5) + size)
  );
  item.style.setProperty("--row-span", rowSpan);
}

$(document).on('click', '.tab .add', function(){
  $('.second-settings .tabs-bar').addClass('reload-tabs');
  var text = $(this).parent().find('input').val(),
      parentId = $(this).attr('data-id'),
      wrap = $(this).parent().parent().find('.list-wrap');

  if(text == ''){
    $(this).parent().find('input').focus();
    return;
  }
  $.ajax({
    url: 'index.php?route=module/uploader/saveOptionValue&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: 'option_id=' + parentId + '&text=' + text,
    success: function(json){
      var html = '';
      if(json['success']){
        html += '<li class="list-item" data-item-id="' + json['success']['id'] + '">';
        html += '<input type="radio" name="default_value_option-' + json['success']['option_id'] + '">';
        html += ' <input type="text" name="sort" value="' + json['success']['sort'] + '" title="<?php echo $text_sort_pl; ?>">';
        html += ' <input type="text" name="text"value="' + json['success']['text'] + '">';
        html += ' <button class="button remove" data-id="' + json['success']['id'] + '"><?php echo $button_remove; ?></button>';
        html += '</li>';

        updateHeight(parentId, 22.5);
        wrap.append(html);
        $('.form-group').find('input').val('');
      }
    }
  });
});

// ---- ---- Апдейт
$(document).on('change', '.tab .list-item input', function(){
  $('.second-settings .tabs-bar').addClass('reload-tabs');
  var value = $(this).val(), id = $(this).parent().attr('data-item-id'),
      col = $(this).attr('name'),
      dataGroup = $(this).parent().parent().parent();

  $.ajax({
    url: 'index.php?route=module/uploader/updateRow&token=<?php echo $token; ?>',
    type: 'post',
    dataType: 'json',
    data: 'value=' + value + '&col=' + col + '&group=option_value&id=' + id,
    success: function(json){
      dataGroup.removeClass("error-update");
      dataGroup.removeClass("success-update");
      if(json['success']){
        dataGroup.addClass("success-update");
        setTimeout(function(){dataGroup.removeClass("success-update");}, 600);
      }else{
        dataGroup.addClass("error-update");
        setTimeout(function(){dataGroup.removeClass("error-update");}, 600);
      }
    },
    error: function(jqXHR, exception){
      dataGroup.addClass("error-update");
      setTimeout(function(){dataGroup.removeClass("error-update");}, 600);
    }
  });
});

$(document).on('click', '.save', function(){
  if($(this).parent().hasClass('module-settings_box')){
    $(this).parent().find('input').each(function(i, input){
      if(!$(input).length){
        $(input).focus();
        return;
      }
    });
  }
  $.ajax({
    url: $(this).parent().attr('action'),
    type: $(this).parent().attr('method'),
    dataType: 'json',
    data: $(this).parent().serialize(),
    success: function(){
      alert('Success updated');
    },
    error: function(jqXHR, exception){
      var msg = '';
      if (jqXHR.status === 0) {
          msg = 'Not connect.\n Verify Network.';
      } else if (jqXHR.status == 404) {
          msg = 'Requested page not found. [404]';
      } else if (jqXHR.status == 500) {
          msg = 'Internal Server Error [500].';
      } else if (exception === 'parsererror') {
          msg = 'Requested JSON parse failed.';
      } else if (exception === 'timeout') {
          msg = 'Time out error.';
      } else if (exception === 'abort') {
          msg = 'Ajax request aborted.';
      } else {
          msg = 'Uncaught Error.\n' + jqXHR.responseText;
      }
      alert(msg);
    }
  });
  return 0;
});
--></script>
<?php echo $footer; ?>
