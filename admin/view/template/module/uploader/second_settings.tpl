<?php if(($type == "image" && (count($counts_paper) == 0 || count($formats) == 0)) || (false/*Для документов*/)){ ?>
  <div class="warning">
    <?php echo $text_set_info; ?>
  </div>
<?php }else{ ?>
  <div class="tabs-bar">
    <button class="button reload"><?php echo $button_reload; ?></button>
    <a href="#price"><?php echo $tab_price; ?></a>
    <?php if($type == "image"){ ?>
      <a href="#quality"><?php echo $tab_quality; ?></a>
    <?php } ?>
    <?php if((int)$select_options_count > 0){ ?>
      <a href="#options" class="selected"><?php echo $tab_option; ?></a>
    <?php } ?>
  </div>
  <div class="tab" id="price">
    <form action="index.php?route=module/uploader/savePrices&token=<?php echo $token; ?>" method="post">
      <?php if($type == "image"){ ?>
        <h3><?php echo $h3_price_format; ?></h3>
        <table class="list">
          <thead>
            <tr>
              <td></td>
              <td><?php echo $text_pre; ?> 1</td>
              <?php foreach($counts_paper as $count){ ?>
                <td><?php echo $text_pre . ' ' . $count['name']; ?></td>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach($formats as $format){ ?>
              <tr>
                <td><?php echo $format['name']; ?></td>
                <td><input type="text" name="price_<?php echo $format['id'] . '_'; ?>" value="<?php echo isset($price['price_' . $format['id'] . '_'])?$price['price_' . $format['id'] . '_']:"0"; ?>"></td>
                <?php foreach($counts_paper as $count){ ?>
                  <td><input type="text" name="price_<?php echo $format['id'] . '_' . $count['id']; ?>" value="<?php echo isset($price['price_' . $format['id'] . '_' . $count['id']])?$price['price_' . $format['id'] . '_' . $count['id']]:"0"; ?>"></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php } ?>

      <?php if(count($options) > 0){ ?>
        <h3><?php echo $h3_price_option; ?></h3>
        <?php foreach($options as $option){ ?>
          <?php if($option['type'] == "select"){ ?>
            <table class="list">
              <thead>
                <tr style="line-height: 25px;text-transform: uppercase;">
                  <td colspan="2"><?php echo $option['name']; ?></td>
                </tr>
                <tr>
                  <td><?php echo $text_option_name_pr?></td>
                  <td><?php echo $text_option_value_pr?></td>
                </tr>
              </thead>
              <tbody>
                <?php if(count($option['values']) > 0){ ?>
                  <?php foreach($option['values'] as $value){ ?>
                    <tr>
                      <td><?php echo $value['text']; ?>:</td><td><input type="text" name="option-<?php echo $option['id'] . '-' . $value['id'] . '__'; ?>" value="<?php echo isset($price['option-' . $option['id'] . '-' . $value['id'] . '__'])?$price['option-' . $option['id'] . '-' . $value['id'] . '__']:"0"; ?>"></td>
                    </tr>
                  <?php } ?>
                <?php }else{ ?>
                  <tr>
                    <td colspan="2"><?php echo $text_set_info_option; ?></td>
                  <tr>
                <?php } ?>
              </tbody>
            </table>
          <?php }else{ ?>
            <table class="list">
              <thead>
                <tr style="line-height: 25px;text-transform: uppercase;">
                  <td colspan="2"><?php echo $option['name']; ?></td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo $text_option_value_pr?>:</td>
                  <td><input type="text" name="option-<?php echo $option['id'] . '__'; ?>" value="<?php echo isset($price['option-' . $option['id'] . '__'])?$price['option-' . $option['id'] . '__']:"0"; ?>"></td>
                </tr>
              </tbody>
            </table>
          <?php } ?>
        <?php } ?>
      <?php } ?>
      <a class="button save"><?php echo $button_save; ?></a>
    </form>
  </div>

  <?php if($type == "image"){ ?>
    <div class="tab" id="quality">
      <form action="index.php?route=module/uploader/saveQualitys&token=<?php echo $token; ?>" method="post">
        <table class="list">
          <thead>
            <tr>
              <td></td>
              <td><?php echo $text_quality_bad; ?></td>
              <td><?php echo $text_quality_normal; ?></td>
              <td><?php echo $text_quality_good; ?></td>
            </tr>
          </thead>
          <tbody>
            <?php foreach($formats as $format){ ?>
              <tr>
                <td><?php echo $format['name']; ?></td>
                <td><input type="text" name="quality[<?php echo $format['id']; ?>][bad]" value="<?php echo $format['bad']; ?>"></td>
                <td><input type="text" name="quality[<?php echo $format['id']; ?>][normal]" value="<?php echo $format['normal']; ?>"></td>
                <td><input type="text" name="quality[<?php echo $format['id']; ?>][good]" value="<?php echo $format['good']; ?>"></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        <a class="button save"><?php echo $button_save; ?></a>
      </form>
    </div>
  <?php } ?>

  <?php if((int)$select_options_count > 0){ ?>
    <div class="tab fixer" id="options">
      <?php foreach($options as $option){ ?>
        <?php if($option['type'] == "select"){ ?>
          <div class="main-list option-<?php echo $option['id']; ?>" data-group-name="option_value">
            <label for="option-<?php echo $option['id']; ?>"><?php echo $option['name']; ?></label>
            <ul class="list-wrap">
              <?php foreach($option['values'] as $value){ ?>
                <li class="list-item" data-item-id="<?php echo $value['id']; ?>">
                  <input type="radio" name="default_value_option-<?php echo $option['id']; ?>" <?php if((int)$value['default_value']){ ?>checked="checked"<?php } ?>>
                  <input type="text" name="sort" value="<?php echo $value['sort']; ?>" title="<?php echo $text_sort_pl; ?>">
                  <input type="text" name="text"value="<?php echo $value['text']; ?>">
                  <button class="button remove" data-id="<?php echo $value['id']; ?>"><?php echo $button_remove; ?></button>
                </li>
              <?php } ?>
            </ul>
            <div class="box-group form-group">
              <input type="text" name="text" id="option-<?php echo $option['id']; ?>">
              <button class="button add" data-id="<?php echo $option['id']; ?>"><?php echo $button_add; ?></button>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  <?php } ?>
<?php } ?>
