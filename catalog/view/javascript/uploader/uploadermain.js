var uploader = function(settings) {

  var self = this;

  self.main = {};
  self.main.uploadWrap  = null;
  self.main.itemsWrap   = null;
  self.main.massWrap    = null;
  self.main.summaryWrap = null;
  self.main.errorWrap   = null;

  self.preloader = {};
  self.preloader.wrap   = null;
  self.server = settings.server;
  self.onlineTrigger = true;

  var ratio = {};
  var selectedItemsCount = 0;
  var selectedItems = null;

  self.template = function(templateName, data) {
    var template = _.template($("#" + templateName).html());
    return template(data);
  };

  self.error = function(data) {
    var show = {
      message: data
    };

    var message = $(self.template("error-message", show));
    self.main.errorWrap.empty();
    self.main.errorWrap.html(message);

    $(message).find(".remove-message").on("click", () => {
      message.fadeOut(300, function(){$(this).remove()});
    });

    setTimeout(() => {
      message.fadeOut(300, function(){$(this).remove()});
    }, 5000);
  };

  self.checkSelected = function() {
    var selected = self.main.itemsWrap.find(".item.selected");
    selectedItemsCount = selected.length;
    selectedItems = selected;

    //if(selectedItemsCount != 0) self.main.massWrap.find(".mass-change_controls button").attr("disabled", false);
    //else self.main.massWrap.find(".mass-change_controls button").attr("disabled", true);

    self.main.massWrap.find(".selected-count_value").html(selectedItemsCount);

    return selectedItemsCount;
  };

  self.preloader.show = function() {
    $("body").css("overflow","hidden");
    self.preloader.wrap.addClass("loading");
  };

  self.preloader.hide = function() {
    setTimeout(() => {
      $("body").css("overflow","visible");
      self.preloader.wrap.removeClass("loading");
    });
  };

  var showBigImage = function(trigger) {
    self.preloader.show();
    var imgPage = self.main.uploadWrap.find("#image-page");
    imgPage.find("img").attr("src", $(trigger).attr("data-full-image"));
    imgPage.find("img").attr("alt", $(trigger).parent().attr("data-name"));
    imgPage.find("img").on("load", function() {
      self.preloader.hide();
      imgPage.removeClass("unset");
    });
  };

  var successUpload = function(response) {
    if(response.hasOwnProperty("success")){
      var data = response.success.data,
          uploaded = response.success.uploaded;

      uploaded.forEach((item, i) => {
        item.options = jQuery.parseJSON(item.options);
        self.main.itemsWrap.append(self.template("upload-item", item));
        $(".item[data-name=\'" + item.name + "\']").find("img").on("load", () => calculateMask(".item[data-name=\'" + item.name + "\']"));
      });

      update(data);
    }else{
      self.error(responseParsed.error);
    }
  };

  var resetValues = function(items, values){
    for(keyItem in items){
      for(keyVal in values){
        var option = $(".item[data-name=\'" + items[keyItem] + "\']").find("*[name=\'" + keyVal + "\']"),
            nodeName = $(option).prop('nodeName'),
            reset = option.attr("data-reset-val");
        if(nodeName == "SELECT" || (nodeName == "INPUT" && ($(option).attr("type") == "text" || $(option).attr("type") == "radio"))){
          option.val(reset);
        }else{
          option.prop("checked", +reset);
        }
      }
    }
  }

  var update = function(data) {
    self.main.summaryWrap.find(".value-count").text(data.total_count);
    self.main.summaryWrap.find(".value-full-price").text(data.total_full_price);
    self.main.summaryWrap.find(".value-price").text(data.total_price);

    var itemsIsset = false;
    self.main.itemsWrap.find(".item").each((i, item) => {
      var item = $(item);
      item.find(".value-item-price").text(data.item_price[item.attr('data-name')]);
      item.find(".quality").text(data.item_quality[item.attr('data-name')].text);
      item.find(".quality").removeClass("bad good very_bad normal").addClass(data.item_quality[item.attr('data-name')].class);
      itemsIsset = true;
    });

    if(itemsIsset) {
      self.main.summaryWrap.find(".confirm").attr("disabled", false);
      self.main.summaryWrap.find(".format-box").removeClass("unset");
      self.main.massWrap.removeClass("unset");
    }else{
      self.main.summaryWrap.find(".confirm").attr("disabled", true);
      self.main.summaryWrap.find(".format-box").addClass("unset");
      self.main.massWrap.addClass("unset");
    }

    self.main.summaryWrap.find(".format-count_block").each((i, block) => {
      var item = $(block);
      if(data.count_format[item.attr("data-id")]!=null){
        item.find(".format-count_value").html(data.count_format[item.attr("data-id")]);
        if(item.hasClass("unset")) item.removeClass("unset");

      }else{
        item.find(".format-count_value").html(0);
        if(!item.hasClass("unset")) item.addClass("unset");
      }
    });
  };

  var deleteItem = function(trigger) {
    var items = {};
    if($(trigger).hasClass("mass-delete")){
      if(selectedItemsCount > 0){
        selectedItems.each((i, item) => {
          items[i] = $(item).attr("data-name");
          $(item).find(".item-controls_delete").attr("disabled", true);
        });
      }else{
        self.error("Изображения не выбраны");
        return;
      }
    }else{
      items[0] = $(trigger).parent().parent().attr("data-name");
      $(trigger).attr("disabled", true);
    }

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/delete",
      type: "post",
      dataType: "json",
      data: {"items":encodeURIComponent(JSON.stringify(items))},
      beforeSend: () => {
        if(!self.onlineTrigger){
          $(document).find(".item-controls_delete").attr("disabled", false);
          self.error("Проверьте подключение к интернету и попробуйте еще раз.");
        }
      },
      success: json => {
        if(json['success']) {
          for(key in items){
            $(".item[data-name=\'" + items[key] + "\']").remove();
          }
          self.checkSelected();
          update(json['success']);
        }else{
          self.error(json['error']);
        }
      }
    });
  };

  var updateItem = function(trigger) {
    var items = {}, values = {};
    if($(trigger).hasClass("mass-submit")){
      if(selectedItemsCount > 0){
        self.main.massWrap.find("input, select").each((i, option) => {
          var nodeName = $(option).prop('nodeName');
          if(nodeName == "SELECT" || (nodeName == "INPUT" && ($(option).attr("type") == "text" || $(option).attr("type") == "radio"))){
            var value = $(option).val();
            if(value != "") values[$(option).attr("name")] = value;
          }else{
            values[$(option).attr("name")] = $(option).prop("checked")?1:0;
          }
        });
        selectedItems.each((i, item) => {
          items[i] = $(item).attr("data-name");
        });
      }else{
        self.error("Изображения не выбраны");
        return;
      }
    }else{
      if($(trigger).attr("name") == "copy_count"){
        countChange(trigger);
        items[0] = $(trigger).parent().parent().parent().parent().attr("data-name");
      }else{
        items[0] = $(trigger).parent().parent().parent().attr("data-name");
      }

      var nodeName = $(trigger).prop('nodeName');

      if(nodeName == "SELECT" || (nodeName == "INPUT" && ($(trigger).attr("type") == "text" || $(trigger).attr("type") == "radio")))
        values[$(trigger).attr("name")] = $(trigger).val();
      else
        values[$(trigger).attr("name")] = $(trigger).prop("checked")?1:0;
    };

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/update",
      type: "post",
      dataType: "json",
      data: {"items":encodeURIComponent(JSON.stringify(items)), "values": encodeURIComponent(JSON.stringify(values))},
      beforeSend: () => {
        if(!self.onlineTrigger){
          resetValues(items, values);
          self.error("Проверьте подключение к интернету и попробуйте еще раз.");
        }
      },
      success: json => {
        if(json['success']){
          if($(trigger).hasClass("mass-submit")){
            for(key in values){
              $(".item.selected").find("select[name=\'" + key + "\'], input[type=\'text\'][name=\'" + key + "\'], input[type=\'radio\'][name=\'" + key + "\']").val(values[key]).attr("data-reset-val", values[key]);
              $(".item.selected").find("input[type=\'checkbox\'][name=\'" + key + "\']").prop("checked", values[key]).attr("data-reset-val", values[key]);
            }

            if(values.hasOwnProperty("format_id") || values.hasOwnProperty("set_in_format")){
              for(key in items){
                calculateMask(".item[data-name=\'" + items[key] + "\']");
              }
            }

            $(".item.selected").addClass("updated");
          }else{
            $(trigger).attr("data-reset-val", values[Object.keys(values)[0]]);
            if(values.hasOwnProperty("format_id") || values.hasOwnProperty("set_in_format")) calculateMask(".item[data-name=\'" + items[0] + "\']");
            $(".item[data-name=\'" + items[0] + "\']").addClass("updated");
          }
          self.main.massWrap.find("input:not([name=\'copy_count\']), select").val("");
          self.main.massWrap.find("input[type=\'checkbox\']").prop("checked", false);
          self.main.massWrap.find("input[name=\'copy_count\']").val(1);
          setTimeout(() => {
            $(".item").removeClass("updated");
          }, 2000);

          update(json['success']);
        }else{
          self.error(json['error']);
        }
      }
    });
  };

  var copyItem = function(trigger) {
    var item = $(trigger).parent().parent().attr("data-name");

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/copy",
      type: "post",
      dataType: "json",
      data: "item=" + encodeURIComponent(item),
      beforeSend: () => {
        if(self.onlineTrigger){
          self.preloader.show();
        }else{
          self.error("Проверьте подключение к интернету и попробуйте еще раз.");
        }
      },
      success: json => {
        if(json["success"]){
          var data = json.success.data,
              copy = json.success.copy;
          copy.options = jQuery.parseJSON(copy.options);
          $(trigger).parent().parent().after(self.template("upload-item", copy));
          update(data);
          $(".item[data-name=\'" + copy.name + "\']").find("img").on("load", () => calculateMask(".item[data-name=\'" + copy.name + "\']"));
        }else{
          self.error(json['error']);
        }
        self.preloader.hide();
      }
    });
  };

  var count = function(trigger) {
    var count = +$(trigger).parent().find("input").val();
    if($(trigger).attr("data-type") == "minus" && count > 1){
      $(trigger).parent().find("input").val(--count);
      return true;
    }else if($(trigger).attr("data-type") == "plus"){
      $(trigger).parent().find("input").val(++count);
      return true;
    }else{
      return false;
    }
  };

  var countChange = function(trigger) {
    var count = +$(trigger).val().replace(/,/, '.');
    if(isNaN(count) || count <= 1){
      $(trigger).val(1);
    }else if(Number(count) === count && count % 1 !== 0){
      $(trigger).val(Math.floor(count));
    }
  };

  var calculateMask = function(item) {
    $(item).find(".item-inner, img").css({
      "height": "auto",
      "width": "auto"
    });
    var format = $(item).find("select[name=\'format_id\']").val(),
        tmpWidth = +settings.ratio[format].a * 300,
        tmpHeight = +settings.ratio[format].b * 300,
        tmpMaxWidth = $(item).find("img").width(),
        tmpMaxHeight = $(item).find("img").height(),
        newWidth, newHeight, width, height, maxHeight, maxWidth;
        newWidth = newHeight = width = height = maxHeight = maxWidth = 0;

    if((tmpMaxWidth > tmpMaxHeight && tmpWidth > tmpHeight) || (tmpMaxWidth > tmpMaxHeight && tmpWidth < tmpHeight)){
      width = tmpHeight;
      height = tmpWidth;
      maxHeight = tmpMaxHeight;
      maxWidth = tmpMaxWidth;
    }else if((tmpMaxWidth < tmpMaxHeight && tmpWidth > tmpHeight) || (tmpMaxWidth < tmpMaxHeight && tmpWidth < tmpHeight)){
      width = tmpWidth;
      height = tmpHeight;
      maxHeight = tmpMaxHeight;
      maxWidth = tmpMaxWidth;
    }else{
      width = tmpWidth;
      height = tmpHeight;
      maxHeight = tmpMaxHeight;
      maxWidth = tmpMaxWidth;
    }

    var ratio = 0;

    if($(item).find("input[name=\'set_in_format\']").prop("checked")){
      ratio = Math.min(maxWidth / width, maxHeight / height);
      newWidth = width * ratio;
      newHeight = height * ratio;

      $(item).find(".mask").css({
        "width": newWidth,
        "height": newHeight
      });

      $(item).find(".mask").removeClass("unset");
    }else{
      ratio = Math.min(260 / width, 260 / height);
      newWidth = width * ratio;
      newHeight = height * ratio;

      $(item).find(".item-inner").css({
        "width": newWidth,
        "height": newHeight
      });

      $(item).find(".mask").addClass("unset");

      ratio = Math.min(newWidth / tmpMaxWidth, newHeight / tmpMaxHeight);

      newWidth = tmpMaxWidth * ratio;
      newHeight = tmpMaxHeight * ratio;

      $(item).find("img").css({
        "width": newWidth,
        "height": newHeight
      });
    }
  };

  var uploadSelected = function(trigger) {
    var itemsUploadWrap = $(trigger).parent().parent().parent().parent();
        socialUpload = {};

    itemsUploadWrap.find(".selected").each((i, item) => {
      socialUpload[i] = $(item).attr("data-url");
    });

    if(itemsUploadWrap.find(".selected").length > 0){
      $.ajax({
        url: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
        type: "post",
        dataType: "json",
        data: "social_upload=" + encodeURIComponent(JSON.stringify(socialUpload)),
        beforeSend: () => {
          if(self.onlineTrigger){
            self.preloader.show();
            itemsUploadWrap.find(".selected").removeClass("selected");
            itemsUploadWrap.find(".open").removeClass("open").addClass("ready-open");
            itemsUploadWrap.addClass("unset");
          }else{
            self.error("Проверьте подключение к интернету и попробуйте еще раз.");
          }
        },
        success: json => {
          if(json["success"]){
            var data = json.success.data,
                uploaded = json.success.uploaded;
            uploaded.forEach((item, i) => {
              item.options = jQuery.parseJSON(item.options);
              self.main.itemsWrap.append(self.template("upload-item", item));
              $(".item[data-name=\'" + item.name + "\']").find("img").on("load", () => {
                calculateMask(".item[data-name=\'" + item.name + "\']");
              });
            });

            update(data);
          }else{
            self.error(responseParsed.error);
          }
          self.preloader.hide();
        }
      });
    }else{
      self.error("Нет выбранных изображений");
    }
  };

  $(window).on("load", function() {
    if(typeof settings.ratio != "undefined"){
      self.main.itemsWrap.find(".item").each((i, item) => calculateMask(item));
    }
  });

  $(document).on("ready", function() {
    self.main.uploadWrap  = $(".uploader-container");
    self.main.itemsWrap   = $(".items-container");
    self.main.massWrap    = $(".mass-change-container");
    self.main.summaryWrap = $(".summary-container");
    self.main.errorWrap   = $(".uploader-error");
    self.preloader.wrap   = $(".preloader");

    var settingsUpload = {
      button: $(".file-upload"),
      dropzone: $(".drop-zone"),
      dragClass: "active",
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
      name: "files_upload",
      multiple: true,
      multipleSelect: true,
      responseType: "json",
      allowedExtensions: settings.allowedFormats,
      maxSize: 1022976,
      onExtError: filename => {
        self.error("Файл \"" + filename + "\" не будет загружен. Поддерживаются форматы " + settings.allowedFormats.join(', ') + "!");
      },
      onChange: () => {
        if(!self.onlineTrigger){
          self.preloader.hide();
          self.error("Проверьте подключение к интернету и попробуйте еще раз.");
          return false;
        }
      },
      onSubmit: () => self.preloader.show(),
      onComplete: (filename, response) => successUpload(response),
      onAllDone: () => self.preloader.hide()
    };

    var upload = new ss.SimpleUpload(settingsUpload);

    $(document).on("click", ".item-controls_delete, .mass-delete", function() {
      deleteItem(this);
    });

    $(document).on("click", ".item-controls_copy", function() {
      copyItem(this);
    });

    $(document).on("click", ".item-controls_select", function() {
      if($(this).parent().parent().hasClass("selected")) $(this).parent().parent().removeClass("selected");
      else $(this).parent().parent().addClass("selected");
      self.checkSelected();
    });

    $(document).on("change", ".action-group input, .action-group select", function() {
      updateItem(this);
    });

    $(document).on("click", ".mass-submit", function() {
      updateItem(this);
    });

    $(document).on("change", ".mass-change input[name=\'copy_count\']", function() {
      countChange(this);
    });

    $(document).on("click", ".button-count", function() {
      if(count(this)) $(this).parent().find("input").trigger("change");
    });

    $(document).on("click", ".mass-all", function() {
      self.main.itemsWrap.find(".item").addClass("selected");
      self.checkSelected();
    });

    $(document).on("click", ".item-wrap", function() {
      showBigImage(this);
    });

    $(document).on("click", "#image-page", function() {
      $(this).addClass("unset");
    });

    if(settings.facebook){
      var fb = facebook(settings.facebook);

      if(window.location.hash == "#facebook"){
        fb.albums();
        history.pushState("", document.title, window.location.href.substr(0, window.location.href.indexOf('#')));
      }

      $(document).on("click", ".fb-upload", function(e) {
        e.preventDefault();
        fb.albums();
      });

      $(document).on("click", "#facebook-page .social-album_header", function() {
        fb.photos(this);
      });

      $(document).on("click", "#facebook-page .social-item_load-more", function() {
        fb.loadMore(this);
      });

      $(document).on("click", "#facebook-page .loaded-buttons .reload", function() {
        fb.reload();
      });
    }

    $(document).on("click", ".loaded", function(e) {
      if($(".loaded").has(e.target).length === 0){
        var wrap = $(this).find(".items-container-wrap");
        wrap.parent().addClass("unset");
        wrap.find(".open").removeClass("open").addClass("ready-open");
        $("body").css("overflow","visible");
      }
    });

    $(document).on("click", ".loaded .close", function(e) {
      var wrap = $(this).parent().parent().parent();
      wrap.parent().addClass("unset");
      wrap.find(".open").removeClass("open").addClass("ready-open");
      $("body").css("overflow","visible");
    });

    $(document).on("mouseup touchend", ".social-item", function() {
      if($(this).hasClass("selected")) $(this).removeClass("selected");
      else $(this).addClass("selected");
    });

    $(document).on("click", ".loaded .upload-selected", function() {
      uploadSelected(this);
    });

    window.addEventListener ('online', () => self.onlineTrigger = true);
    window.addEventListener ('offline', () => self.onlineTrigger = false);
  });

  return self;
};
