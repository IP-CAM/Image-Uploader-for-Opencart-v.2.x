<?php echo $header; ?>
<div class="items-loader">
  <div class="items-loader-wrap">
    <i class="fas fa-spinner fa-pulse icon-load"></i>
    <span class="text-load"><?php echo $text_loading; ?></span>
  </div>
</div>
<div id="uploaded-images" class="items">
  <nav class="mass-change unset">
    <div class="mass-change_action">
      <span class="title"><?php echo $text_paper_type; ?></span>
      <select name="paper_type_id">
        <option value="" selected><?php echo $text_select; ?></option>
        <?php foreach($paper_types as $type){ ?>
          <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
        <?php }?>
      </select>
    </div>
    <div class="mass-change_action">
      <span class="title"><?php echo $text_format; ?></span>
      <select name="format_id">
        <option value="" selected><?php echo $text_select; ?></option>
        <?php foreach($formats as $format){ ?>
          <option value="<?php echo $format['id']; ?>"><?php echo $format['name']; ?></option>
        <?php }?>
      </select>
    </div>
    <?php foreach($options as $option) {?>
      <div class="mass-change_action">
        <span class="title"><?php if(!empty($option['article'])){ ?><a href="<?php echo $option['article']['link']; ?>" target="_blank" rel="noopener"><?php } ?><?php echo $option['name']; ?><?php if(!empty($option['article'])){ ?></a><?php }?></span>
        <?php if($option['type'] == "select"){ ?>
          <select name="option_<?php echo $option['id']; ?>">
            <option value="" selected><?php echo $text_select; ?></option>
            <?php foreach($option['values'] as $val){ ?>
              <option value="<?php echo $val['id']; ?>"><?php echo $val['text']; ?></option>
            <?php }?>
          </select>
        <?php }else if($option['type'] == "checkbox"){ ?>
          <input type="checkbox" name="option_<?php echo $option['id']; ?>">
        <?php } ?>
      </div>
    <?php } ?>
    <div class="mass-change_action">
      <span class="title"><?php echo $text_count; ?></span>
      <div class="count">
        <button data-type="minus" class="btn button-count">-</button>
        <input type="text" name="copy_count" value="1">
        <button data-type="plus" class="btn button-count">+</button>
      </div>
    </div>
    <div class="mass-change_controls">
      <div class="selected-count">
        <span class="btn selected-count_text"><?php echo $text_selected_count; ?></span>
        <span class="btn selected-count_value">0</span>
      </div>
      <button class="btn mass-delete"><?php echo $text_delete; ?></button>
      <button class="btn mass-submit"><?php echo $text_submit; ?></button>
    </div>
  </nav>
  <div class="format-count-container <?php if(empty($images)){ ?>unset<?php } ?>">
    <span class="title"><?php echo $text_multiplicity; ?></span>
    <?php foreach($formats as $format) { ?>
      <?php if($format['count'] > 0) { ?>
        <div class="format-count_block" data-id="<?php echo $format['id']; ?>">
          <span class="format-count_value"><?php echo $format['count']; ?></span>
          <span class="format-count_name"><?php echo $text_multiplicity_dot . ' ' . $format['name']; ?></span>
        </div>
      <?php }else{ ?>
        <div class="format-count_block unset" data-id="<?php echo $format['id']; ?>">
          <span class="format-count_value">0</span>
          <span class="format-count_name"><?php echo $text_multiplicity_dot . ' ' . $format['name']; ?></span>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
  <div class="uploader">
    <div class="upload-button">
      <label class="pc-upload thumbnail">
        <span><?php echo $text_pc_upload; ?></span>
        <input type="file" name="file_upload" class="file-upload" multiple>
        <div class="drop-zone"></div>
      </label>
    </div>
    <div class="upload-button">
      <a href="#" class="fb-upload thumbnail">
        <span><?php echo $text_fb_upload; ?></span>
        <span><i class="fab fa-facebook"></i></span>
      </a>
    </div>
    <div class="upload-button">
      <a href="#" class="inst-upload thumbnail">
        <span><?php echo $text_inst_upload; ?></span>
        <span><i class="fab fa-instagram"></i></span>
      </a>
    </div>
  </div>
  <div class="items-container <?php if(!$images){ ?>empty-uploaded<?php } ?>">
    <?php foreach($images as $image){ ?>
      <div class="item" data-name="<?php echo $image['name']; ?>">
        <span class="quality <?php echo $image['quality']; ?>">
          <?php echo ${"text_quality_" . $image['quality']}; ?>
        </span>
        <div class="item-controls">
          <button class="btn item-controls_select">
            <i class="far fa-check-circle"></i>
          </button>
          <button class="btn item-controls_copy">
            <i class="far fa-copy"></i>
          </button>
          <button class="btn item-controls_delete">
            <i class="far fa-trash-alt"></i>
          </button>
        </div>
        <div class="item-wrap">
          <div class="item-inner">
            <div class="mask"></div>
            <img src="<?php echo $image['base']; ?>" alt="<?php echo $image['name']; ?>">
          </div>
        </div>
        <div class="item-actions">
          <div class="action-group">
            <span class="title"><?php echo $text_paper_type; ?></span>
            <select name="paper_type_id">
              <?php foreach($paper_types as $type){ ?>
                <?php if($type['id'] == $image['paper_type_id']){ ?>
                  <option value="<?php echo $type['id']; ?>" selected><?php echo $type['name']; ?></option>
                <?php }else{ ?>
                  <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                <?php }?>
              <?php }?>
            </select>
          </div>
          <div class="action-group">
            <span class="title"><?php echo $text_format; ?></span>
            <select name="format_id">
              <?php foreach($formats as $format){ ?>
                <?php if($format['id'] == $image['format_id']){ ?>
                  <option value="<?php echo $format['id']; ?>" selected><?php echo $format['name']; ?></option>
                <?php }else{ ?>
                  <option value="<?php echo $format['id']; ?>"><?php echo $format['name']; ?></option>
                <?php }?>
              <?php }?>
            </select>
          </div>
          <?php foreach($options as $option) {?>
            <div class="action-group">
              <span class="title"><?php if(!empty($option['article'])){ ?><a href="<?php echo $option['article']['link']; ?>" target="_blank" rel="noopener"><?php } ?><?php echo $option['name']; ?><?php if(!empty($option['article'])){ ?></a><?php }?></span>
              <?php if($option['type'] == "select"){ ?>
                <select name="option_<?php echo $option['id']; ?>">
                  <?php foreach($option['values'] as $val){ ?>
                    <?php if($val['id'] == $image['options'][$option['id']]['value']){ ?>
                      <option value="<?php echo $val['id']; ?>" selected><?php echo $val['text']; ?></option>
                    <?php }else{ ?>
                      <option value="<?php echo $val['id']; ?>"><?php echo $val['text']; ?></option>
                    <?php }?>
                  <?php }?>
                </select>
              <?php }else if($option['type'] == "checkbox"){ ?>
                <input type="checkbox" name="option_<?php echo $option['id']; ?>" <?php if(isset($image['options'][$option['id']])){ ?>checked<?php } ?>>
              <?php } ?>
            </div>
          <?php } ?>
          <div class="action-group">
            <span class="title"><?php echo $text_set_in_format; ?></span>
            <input type="checkbox" name="set_in_format" <?php if($image['set_in_format'] == 1){ ?>checked<?php } ?>>
          </div>
          <div class="action-group">
            <span class="title"><?php echo $text_count; ?></span>
            <div class="count">
              <button data-type="minus" class="btn button-count">-</button>
              <input type="text" name="copy_count" value="<?php echo isset($image['count'])?$image['count']:1; ?>">
              <button data-type="plus" class="btn button-count">+</button>
            </div>
          </div>
        </div>
        <div class="price">
          <span class="title"><?php echo $text_price; ?></span>
          <span class="value-item-price"><?php echo $image['price']; ?></span>
        </div>
      </div>
    <?php } ?>
  </div>
  <div class="summary">
    <div class="total-box">
      <div class="item">
        <span class="title"><?php echo $text_total_count; ?>:</span>
        <span class="value value-count"><?php echo $total_count; ?><span>
      </div>
      <div class="item">
        <span class="title"><?php echo $text_price; ?>:</span>
        <span class="value value-full-price"><?php echo $total_full_price; ?><span>
      </div>
      <div class="item">
        <span class="title"><?php echo $text_full_price; ?>:</span>
        <span class="value value-price"><?php echo $total_price; ?><span>
      </div>
    </div>
    <div class="confirm-box">
      <button class="btn confirm" <?php if(!$images){ ?>disabled<?php } ?>><?php echo $text_conform; ?></button>
    </div>
  </div>
</div>
<div id="instagram-loaded" class="loaded unset">
  <div class="items-container-wrap">
    <nav class="loaded-nav">
      <div class="loaded-title"><?php echo $text_inst_upload; ?></div>
      <div class="loaded-buttons">
        <button class="btn upload-selected"><?php echo $text_upload_selected; ?></button>
        <button class="btn reload"><i class="fas fa-sync-alt"></i></button>
        <button class="btn close-loaded"><i class="fas fa-times"></i></button>
      </div>
    </nav>
    <ul class="items-container"></ul>
  </div>
</div>
<div id="facebook-loaded" class="loaded unset">
  <div class="items-container-wrap">
    <nav class="loaded-nav">
      <div class="loaded-title"><?php echo $text_fb_upload; ?></div>
      <div class="loaded-buttons">
        <button class="btn upload-selected"><?php echo $text_upload_selected; ?></button>
        <button class="btn reload"><i class="fas fa-sync-alt"></i></button>
        <button class="btn close-loaded"><i class="fas fa-times"></i></button>
      </div>
    </nav>
    <ul class="items-container"></ul>
  </div>
</div>
<div class="uploader-error"></div>

<script type="text/template" id="loaded-items">
<% if(items.length > 0){ %>
<% items.forEach(function(item){ %>
<li class="social-item" data-pid="<%=item.id%>" data-url="<%=item.data_url%>" style="background-image:url('<%=item.url%>');">
  <div class="item-inner">
    <i class="fas fa-spinner fa-pulse icon-load"></i>
    <i class="far fa-check-circle icon-selected"></i>
  </div>
</li>
<% }); %>
<% }else{ %>
<p class="loaded-empty"><?php echo $text_loaded_empty; ?></p>
<% } %>
</script>

<script type="text/template" id="upload-item">
<div class="item" data-name="<%=name%>">
  <span class="quality <% if(typeof quality!=='undefined'){ %><%=quality%><% } %>">
    <% if(typeof quality!=='undefined'){ %>
      <% if(quality=='good'){ %>
      <?php echo $text_quality_good; ?>
      <% }else if(quality=='normal'){ %>
      <?php echo $text_quality_normal; ?>
      <% }else if(quality=='bad'){ %>
      <?php echo $text_quality_bad; ?>
      <% }else{ %>
      <?php echo $text_quality_very_bad; ?>
      <% } %>
    <% } %>
  </span>
  <div class="item-controls">
    <button class="btn item-controls_select">
      <i class="far fa-check-circle"></i>
    </button>
    <button class="btn item-controls_copy">
      <i class="far fa-copy"></i>
    </button>
    <button class="btn item-controls_delete">
      <i class="far fa-trash-alt"></i>
    </button>
  </div>
  <div class="item-wrap">
    <div class="item-inner">
    <div class="mask"></div>
      <img src="<%=base%>" alt="<%=name%>">
    </div>
  </div>
  <div class="item-actions">
    <div class="action-group">
      <span class="title"><?php echo $text_paper_type; ?></span>
      <select name="paper_type_id">
        <?php foreach($paper_types as $type){ ?>
          <option value="<?php echo $type['id']; ?>" <% if(paper_type_id=='<?php echo $type['id']; ?>'){ %>selected<% } %>><?php echo $type['name']; ?></option>
        <?php }?>
      </select>
    </div>
    <div class="action-group">
      <span class="title"><?php echo $text_format; ?></span>
      <select name="format_id">
        <?php foreach($formats as $format){ ?>
          <option value="<?php echo $format['id']; ?>" <% if(format_id=='<?php echo $format['id']; ?>'){ %>selected<% } %>><?php echo $format['name']; ?></option>
        <?php }?>
      </select>
    </div>
    <?php foreach($options as $option) {?>
      <div class="action-group">
        <span class="title"><?php if(!empty($option['article'])){ ?><a href="<?php echo $option['article']['link']; ?>" target="_blank" rel="noopener"><?php } ?><?php echo $option['name']; ?><?php if(!empty($option['article'])){ ?></a><?php }?></span>
        <?php if($option['type'] == "select"){ ?>
          <select name="option_<?php echo $option['id']; ?>">
            <?php foreach($option['values'] as $val){ ?>
              <option value="<?php echo $val['id']; ?>" <% if(options.hasOwnProperty('<?php echo $option['id']; ?>')){ %><% if(options['<?php echo $option['id']; ?>'].value=='<?php echo $val['id']; ?>'){ %>selected<% } %><% } %>><?php echo $val['text']; ?></option>
            <?php }?>
          </select>
        <?php }else if($option['type'] == "checkbox"){ ?>
          <input type="checkbox" name="option_<?php echo $option['id']; ?>" <% if(options.hasOwnProperty('<?php echo $option['id']; ?>')){ %>checked<% } %>>
        <?php } ?>
      </div>
    <?php } ?>
    <div class="action-group">
      <span class="title"><?php echo $text_set_in_format; ?></span>
      <input type="checkbox" name="set_in_format" <% if(set_in_format==1){ %>checked<% } %>>
    </div>
    <div class="action-group">
      <span class="title"><?php echo $text_count; ?></span>
      <div class="count">
        <button data-type="minus" class="btn button-count">-</button>
        <input type="text" name="copy_count" value="<%=copy_count%>">
        <button data-type="plus" class="btn button-count">+</button>
      </div>
    </div>
  </div>
  <div class="price">
    <span class="title"><?php echo $text_price; ?></span>
    <span class="value-item-price"><% if(typeof price!=='undefined'){ %><%=price%><% } %></span>
  </div>
</div>
</script>

<script type="text/template" id="load-more-button">
<li class="social-item_load-more">
  <button class="btn load-more" data-page="<% if(typeof next_url!=='undefined'){ %><%=next_url%><% }else{ %><%=next%><% } %>">
    <span class="load-more_preloader"><i class="fas fa-spinner fa-pulse"></i></span>
    <span class="load-more_text"><?php echo $text_load_more; ?></span>
  </button>
</li>
</script>

<script type="text/template" id="loaded-albums">
<% if(items.length > 0){ %>
<% items.forEach(function(item){ %>
<li class="social-album">
  <div class="social-album_header" data-page="<%=item.url%>">
    <i class="far fa-folder icon-closed"></i>
    <i class="far fa-folder-open icon-open"></i>
    <span class="social-album_header-title"><%=item.name%></span>
    <i class="fas fa-spinner fa-pulse album-loader"></i>
  </div>
  <ul class="social-album_content"></ul>
</li>
<% }); %>
<% }else{ %>
<p class="loaded-empty"><?php echo $text_loaded_empty; ?></p>
<% } %>
</script>

<script><!--
uploader({
  uploaderType: "image",
  allowedFormats: ["zip", "jpg", "jpeg", "png"],
  ratio: <?php echo $ratio; ?>
});
--></script>
<?php echo $footer; ?>
