<div class="tab-box_second">
  <form action="index.php?route=module/uploader/socialSettings&token=<?php echo $token; ?>" method="post">
    <div class="box-group">
      <label for="link"><?php echo $text_link; ?></label>
      <?php echo $text_link_pl; ?>
      <input type="hidden" name="type" value="image">
    </div>
    <hr><br>
    <label class="box-group">
      <?php echo $text_facebook; ?>
      <input type="text" name="facebook_client_id" placeholder="1079984405513021" value="<?php echo $facebook_client_id; ?>">
    </label>
    <label class="box-group">
      <?php echo $text_social_limit_photo; ?>
      <input type="text" name="facebook_photos_limit" placeholder="15" value="<?php echo $facebook_photos_limit; ?>">
    </label>
    <label class="box-group">
      <?php echo $text_social_limit_albums; ?>
      <input type="text" name="facebook_albums_limit" placeholder="15" value="<?php echo $facebook_albums_limit; ?>">
    </label>
    <label class="box-group">
      <?php echo $text_instagram; ?>
      <input type="text" name="instagram_client_id" placeholder="d103bb3cf5c84baca2a28a5a502ec7be" value="<?php echo $instagram_client_id; ?>">
    </label>
    <label class="box-group">
      <?php echo $text_social_limit_photo; ?>
      <input type="text" name="instagram_photos_limit" placeholder="15" value="<?php echo $instagram_photos_limit; ?>">
    </label>
    <div class="box-group">
      <label><?php echo $text_social_allowed_formats; ?></label>
      <label>
        <?php echo $text_social_allowed_formats_jpg; ?>
        <input type="checkbox" checked="checked" disabled>
        <input type="hidden" name="image_allowed_formats[jpg]" value="jpg" checked="checked">
      </label>
      <label>
        <?php echo $text_social_allowed_formats_png; ?>
        <input type="checkbox" checked="checked" disabled>
        <input type="hidden" name="image_allowed_formats[png]" value="png" checked="checked">
      </label>
      <label>
        <?php echo $text_social_allowed_formats_gif; ?>
        <input type="checkbox" name="image_allowed_formats[gif]" value="gif" <?php if(isset($image_allowed_formats['gif'])){ ?>checked="checked"<?php } ?>>
      </label>
      <label>
        <?php echo $text_social_allowed_formats_rar; ?>
        <input type="checkbox" name="image_allowed_formats[rar]" value="rar" <?php if(isset($image_allowed_formats['rar'])){ ?>checked="checked"<?php } ?>>
      </label>
      <label>
        <?php echo $text_social_allowed_formats_zip; ?>
        <input type="checkbox" name="image_allowed_formats[zip]" value="zip" <?php if(isset($image_allowed_formats['zip'])){ ?>checked="checked"<?php } ?>>
      </label>
    </div>
    <a class="button save"><?php echo $button_save; ?></a>
  </form>
</div>

<div class="tab-box_main">
  <div class="main-list" data-group-name="format">
    <label for="format"><?php echo $text_image_format; ?></label>
    <ul class="list-wrap">
      <?php foreach($formats as $format){ ?>
        <li lass="list-item" data-item-id="<?php echo $format['id']; ?>">
          <input type="radio" name="default_value-format" <?php if((int)$format['default_value']){ ?>checked="checked"<?php } ?>>
          <input type="text" name="sort" value="<?php echo $format['sort']; ?>" title="<?php echo $text_sort_pl; ?>">
          <input type="text" name="name" value="<?php echo $format['name']; ?>" placeholder="<?php echo $text_image_format_pl; ?>">
          <input type="text" name="ratio" value="<?php echo $format['ratio']; ?>" placeholder="<?php echo $text_image_format_ratio_pl; ?>">
          <button class="button remove" data-id="<?php echo $format['id']; ?>"><?php echo $button_remove; ?></button>
        </li>
      <?php } ?>
    </ul>
    <div class="box-group form-group marg-set">
      <input type="text" name="name" id="format" placeholder="<?php echo $text_image_format_pl; ?>">
      <input type="text" name="ratio" placeholder="<?php echo $text_image_format_ratio_pl; ?>">
      <button class="button add"><?php echo $button_add; ?></button>
    </div>
  </div>

  <div class="main-list" data-group-name="paper_type">
    <label for="paper_type"><?php echo $text_paper_type; ?></label>
    <ul class="list-wrap">
      <?php foreach($paper_types as $type){ ?>
        <li lass="list-item" data-item-id="<?php echo $type['id']; ?>">
          <input type="radio" name="default_value-paper_type" <?php if((int)$type['default_value']){ ?>checked="checked"<?php } ?>>
          <input type="text" name="sort" value="<?php echo $type['sort']; ?>" title="<?php echo $text_sort_pl; ?>">
          <input type="text" name="name" value="<?php echo $type['name']; ?>" placeholder="<?php echo $text_paper_type_pl; ?>">
          <button class="button remove" data-id="<?php echo $type['id']; ?>"><?php echo $button_remove; ?></button>
        </li>
      <?php } ?>
    </ul>
    <div class="box-group form-group marg-set">
      <input type="text" name="name" id="paper_type" placeholder="<?php echo $text_paper_type_pl; ?>">
      <input type="hidden" name="uploader_type" value="0">
      <button class="button add"><?php echo $button_add; ?></button>
    </div>
  </div>

  <div class="main-list" data-group-name="count_paper">
    <label for="count_paper"><?php echo $text_count_paper; ?></label>
    <ul class="list-wrap">
      <?php foreach($counts_paper as $count){ ?>
        <li lass="list-item" data-item-id="<?php echo $count['id']; ?>">
          <input type="text" name="name"value="<?php echo $count['name']; ?>" placeholder="<?php echo $text_count_paper_pl; ?>">
          <button class="button remove" data-id="<?php echo $count['id']; ?>"><?php echo $button_remove; ?></button>
        </li>
      <?php } ?>
    </ul>
    <div class="box-group form-group">
      <input type="text" name="name" id="count_paper" placeholder="<?php echo $text_count_paper_pl; ?>">
      <input type="hidden" name="uploader_type" value="0">
      <button class="button add"><?php echo $button_add; ?></button>
    </div>
  </div>

  <div class="main-list" data-group-name="option">
    <label for="option"><?php echo $text_option; ?></label>
    <ul class="list-wrap">
      <?php foreach($options as $option){ ?>
        <li lass="list-item" data-item-id="<?php echo $option['id']; ?>">
          <input type="text" name="name" value="<?php echo $option['name']; ?>" placeholder="<?php echo $text_option_pl; ?>">
          <p class="item-text"><?php if($option['type']=='checkbox'){ echo $text_option_checkbox; }else if($option['type']=='select'){ echo $text_option_select; } ?></p>
          <input tyle="text" value="<?php echo $option['article_title']; ?>" class="autocomplete-article" data-set="autocomplete-article_<?php echo $option['id']; ?>" title="<?php echo $text_option_article; ?>">
          <input type="hidden" id="autocomplete-article_<?php echo $option['id']; ?>" name="article_id" value="<?php echo $option['article_id']; ?>">
          <button class="button remove" data-id="<?php echo $option['id']; ?>"><?php echo $button_remove; ?></button>
        </li>
      <?php } ?>
    </ul>
    <div class="box-group form-group">
      <input type="text" name="name" id="option" name="name" placeholder="<?php echo $text_option_pl; ?>">
      <select name="type">
        <option value="checkbox"><?php echo $text_option_checkbox; ?></option>
        <option value="select"><?php echo $text_option_select; ?></option>
      </select>
      <input tyle="text" class="autocomplete-article" data-set="autocomplete-article" title="<?php echo $text_option_article; ?>">
      <input type="hidden" id="autocomplete-article" name="article_id" value="">
      <input type="hidden" name="uploader_type" value="0">
      <button class="button add"><?php echo $button_add; ?></button>
    </div>
  </div>
</div>
<script>
var secondSettingsLink = "index.php?route=module/uploader/getSecondSettingsImage&token=<?php echo $token; ?>",
    secondScript = function(){


    var grid = document.querySelector('#options');
    //var els = $('.main-list');
    var items = document.querySelectorAll('#options .main-list');
    //console.log(items);
    items.forEach(function(item, content){
      const rowSpan = Math.round(
        (Math.round(item.offsetHeight + 5) + 0.5)
      );
      item.style.setProperty("--row-span", rowSpan);
    });

    //grid.removeClass('fixer');
    grid.classList.remove("fixer");
    setTimeout($('.second-settings_wrap .tabs-bar a').tabs(), 200);
    //$('.second-settings_wrap .tabs-bar a').tabs();
}
</script>
