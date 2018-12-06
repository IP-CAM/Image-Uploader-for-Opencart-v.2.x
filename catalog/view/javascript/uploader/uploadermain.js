var uploader = (settings) => {
  var self = this;

  var settingsUpload = {
    rules: {
      allowedFileTypes: settings.allowedFormats,
    },
    script: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
    singleFileUploads: true
  };

  var itemsWrap;
  var formatWrap;
  var total;
  var massChange;
  var ratio = {};
  var selectedItemsCount = 0;
  var selectedItems = null;

  self.template = (templateName, data) => {
    var template = _.template($("#" + templateName).html());
    return template(data);
  };

  self.beforeUpload = (e, files) => {
    //var fileName = files[0].name;
    //progress[fileName] = 0;
  }

  self.inProgress = (e, state) => {
    // var fileName = state.files[0].name;
    // progress[fileName] = state.percentage;
    // var el = document.querySelector("pre");
    // el.innerHTML = JSON.stringify(progress, null, 2);
  }

  self.successUpload = (e, response) => {
    var responseParsed = jQuery.parseJSON(response);
    if(responseParsed["success"]){
      var data = responseParsed.success.data,
          uploaded = responseParsed.success.uploaded;
      uploaded.forEach((item, i) => {
        item.options = jQuery.parseJSON(item.options);
        itemsWrap.append(self.template("upload-item", item));
        $(".item[data-name=\'" + item.name + "\']").find("img").on("load", () => {
          calculateMask(".item[data-name=\'" + item.name + "\']");
        });
      });
      self.update(data);
    }else{
      self.error(responseParsed.error);
    }
  }

  self.update = data => {
    total.find(".value-count").text(data.total_count);
    total.find(".value-full-price").text(data.total_full_price);
    total.find(".value-price").text(data.total_price);
    var itemsIsset = false;
    itemsWrap.find(".item").each((i, item) => {
      var item = $(item);
      item.find(".value-item-price").text(data.item_price[item.attr('data-name')]);
      item.find(".quality").text(data.item_quality[item.attr('data-name')].text);
      item.find(".quality").removeClass("bad good very_bad normal").addClass(data.item_quality[item.attr('data-name')].class);
      itemsIsset = true;
    });
    if(itemsIsset) {
      formatWrap.removeClass("unset");
      massChange.removeClass("unset");
    }else{
      formatWrap.addClass("unset");
      massChange.addClass("unset");
    }
    $(".format-count_block").each((i, block) => {
      var item = $(block);
      if(data.count_format[item.attr("data-id")]!=null){
        item.find(".format-count_value").html(data.count_format[item.attr("data-id")]);
        if(item.hasClass("unset")) item.removeClass("unset");

      }else{
        item.find(".format-count_value").html(0);
        if(!item.hasClass("unset")) item.addClass("unset");
      }
    });
  }

  self.error = data => {
    var message = $('<div class="uploader-error_message"><span class="message-text">' + data + '</span><button class="remove-message"><i class="far fa-times-circle"></i></button></div>');
    $(".uploader-error").append(message);

    $(message).find(".remove-message").on("click", () => {
      message.fadeOut(300, function(){$(this).remove()});
    });
    setTimeout(() => {
      message.fadeOut(300, function(){$(this).remove()});
    }, 5000);
  }

  self.checkSelected = () => {
    var selected = itemsWrap.find(".item.selected");
    selectedItemsCount = selected.length;
    selectedItems = selected;
    if(selectedItemsCount != 0) $(".mass-change_controls").removeClass("unset");
    else $(".mass-change_controls").addClass("unset");

    $(".selected-count_value").html(selectedItemsCount);

    return selectedItemsCount;
  }

  self.deleteItem = trigger => {
    var items = {};
    if($(trigger).hasClass("mass-delete")){
      if(selectedItemsCount > 0){
        selectedItems.each((i, item) => {
          items[i] = $(item).attr("data-name");
        });
      }else{
        self.error("Изображения не выбраны");
        return;
      }
    }else{
      items[0] = $(trigger).parent().parent().attr("data-name");
    }

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/delete",
      type: "post",
      dataType: "json",
      data: {"items":encodeURIComponent(JSON.stringify(items))},
      success: (json) => {
        if(json['success']) {
          for(key in items){
            $(".item[data-name=\'" + items[key] + "\']").remove();
          }
          self.checkSelected();
          self.update(json['success']);
        }else{
          self.error(json['error']);
        }
      }
    });
  }

  self.updateItem = trigger => {
    var items = {}, values = {};
    if($(trigger).hasClass("mass-submit")){
      if(selectedItemsCount > 0){
        massChange.find("input, select").each((i, option) => {
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
        self.countChange(trigger);
        items[0] = $(trigger).parent().parent().parent().parent().attr("data-name");
      }else{
        items[0] = $(trigger).parent().parent().parent().attr("data-name");
      }

      var nodeName = $(trigger).prop('nodeName');

      if(nodeName == "SELECT" || (nodeName == "INPUT" && ($(trigger).attr("type") == "text" || $(trigger).attr("type") == "radio")))
        values[$(trigger).attr("name")] = $(trigger).val();
      else
        values[$(trigger).attr("name")] = $(trigger).prop("checked")?1:0;

    }

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/update",
      type: "post",
      dataType: "json",
      data: {"items":encodeURIComponent(JSON.stringify(items)), "values": encodeURIComponent(JSON.stringify(values))},
      success: (json) => {
        if(json['success']){
          if($(trigger).hasClass("mass-submit")){
            for(key in values){
              $(".item.selected").find("select[name=\'" + key + "\'], input[type=\'text\'][name=\'" + key + "\'], input[type=\'radio\'][name=\'" + key + "\']").val(values[key]);
              $(".item.selected").find("input[type=\'checkbox\'][name=\'" + key + "\']").prop("checked", values[key]);
            }
            if(values.hasOwnProperty("format_id") || values.hasOwnProperty("set_in_format")){
              for(key in items){
                calculateMask(".item[data-name=\'" + items[key] + "\']");
              }
            }

            $(".item.selected").addClass("updated");
          }else{
            if(values.hasOwnProperty("format_id") || values.hasOwnProperty("set_in_format")) calculateMask(".item[data-name=\'" + items[0] + "\']");
            $(".item[data-name=\'" + items[0] + "\']").addClass("updated");
          }
          massChange.find("input:not([name=\'copy_count\']), select").val("");
          massChange.find("input[name=\'copy_count\']").val(1);
          setTimeout(() => {
            $(".item").removeClass("updated");
          }, 2000);

          self.update(json['success']);
        }else{
          self.error(json['error']);
        }
      }
    });
  }

  self.copyItem = trigger => {
    var item = $(trigger).parent().parent().attr("data-name");

    $.ajax({
      url: "index.php?route=module/" + settings.uploaderType + "_uploader/copy",
      type: "post",
      dataType: "json",
      data: "item=" + encodeURIComponent(item),
      success: (json) => {
        if(json["success"]){
          var data = json.success.data,
              copy = json.success.copy;
          copy.options = jQuery.parseJSON(copy.options);
          itemsWrap.append(self.template("upload-item", copy));
          self.update(data);
          $(".item[data-name=\'" + copy.name + "\']").find("img").on("load", () => {
            calculateMask(".item[data-name=\'" + copy.name + "\']");
          });
        }else{
          self.error(json['error']);
        }
      }
    });
  }

  self.count = trigger => {
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
  }

  self.countChange = trigger => {
    var count = +$(trigger).val().replace(/,/, '.');
    if(isNaN(count) || count <= 1){
      $(trigger).val(1);
    }else if(Number(count) === count && count % 1 !== 0){
      $(trigger).val(Math.floor(count));
    }
  }

  var calculateMask = item => {
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
  }

  self.uploadSelected = trigger => {
    var itemsUploadWrap = $(trigger).parent().parent().parent().parent();
        socialUpload = {};

    itemsUploadWrap.find(".selected").each((i, item) => {
      socialUpload[i] = $(item).attr("data-url")
    });

    if(itemsUploadWrap.find(".selected").length > 0){
      $.ajax({
        url: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
        type: "post",
        dataType: "json",
        data: "social_upload=" + encodeURIComponent(JSON.stringify(socialUpload)),
        success: (json) => {
          if(json["success"]){
            var data = json.success.data,
                uploaded = json.success.uploaded;
            uploaded.forEach((item, i) => {
              item.options = jQuery.parseJSON(item.options);
              itemsWrap.append(self.template("upload-item", item));
              $(".item[data-name=\'" + item.name + "\']").find("img").on("load", () => {
                calculateMask(".item[data-name=\'" + item.name + "\']");
              });
            });

            self.update(data);

            itemsUploadWrap.find(".selected").removeClass("selected");
            itemsUploadWrap.find(".open").removeClass("open").addClass("ready-open");
            itemsUploadWrap.addClass("unset");
          }else{
            self.error(responseParsed.error);
          }
        }
      });
    }else{
      self.error("Нет выбранных изображений");
    }
  }

  self.getCookie = name => {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }

  self.instagram = {};
  self.instagram.accessToken = false;
  self.instagram.nextUrl = null;

  self.instagram.login  = () => {
    window.location = "https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=https://photoradost.loc/api/instagram.php&response_type=code";
  }

  self.instagram.show = (page = null) => {
    if (!self.instagram.accessToken){
      self.instagram.login();
    }else{
      self.instagram.nextUrl = page;
      if($('#instagram-loaded .items-container').html() == '' || self.instagram.nextUrl){
        self.instagram.renderPhotos();
      }else{
        $("#instagram-loaded").removeClass("unset");
      }
    }
  }

  self.instagram.renderPhotos = () => {
    $("#instagram-loaded").removeClass("unset");
    if(self.instagram.nextUrl){
      $("#instagram-loaded .items-container").find(".social-item_load-more").addClass("loading");
    }
    $.ajax({
      url: self.instagram.nextUrl || "https://api.instagram.com/v1/users/self/media/recent/",
      data: {access_token: self.instagram.accessToken, count: 5},
      type: "GET",
      crossDomain: true,
      dataType: "jsonp",
      success: function(photos){
        if(photos.hasOwnProperty("meta") && photos.meta.hasOwnProperty("code") && photos.meta.code == 200){
          var data = {items: []};
          var i, item;
          for (i in photos.data) {
            if(photos.data[i].type == "image" || photos.data[i].type == "carousel"){
              data.items.push({
                id: photos.data[i].id,
                url: photos.data[i].images.low_resolution.url,
                data_url: photos.data[i].link + "media/?size=l"
              });
            }
          }

          if(self.instagram.nextUrl){
            $("#instagram-loaded .items-container").find(".social-item_load-more").remove();
          }

          $("#instagram-loaded .items-container").append(self.template("loaded-items", data));

          if (photos.hasOwnProperty("pagination") && photos.pagination.hasOwnProperty("next_url")) {
            $("#instagram-loaded .items-container").append(self.template("load-more-button", photos.pagination));
          }

          $("#instagram-loaded").find(".reload").removeClass("load");

          self.instagram.nextUrl = null;
        }else{
          self.instagram.login();
        }
      }
    });
  }

  self.facebook = {};
  self.facebook.accessToken = false;
  self.facebook.nextUrl = false;
  self.facebook.userId = null;
  self.facebook.loadbox = null;
  self.facebook.more = 0;

  self.facebook.login  = () => {
    window.location = "https://www.facebook.com/v3.2/dialog/oauth?client_id=1079984405513021&redirect_uri=https://photoradost.loc/api/facebook.php&response_type=code";
  }

  self.facebook.show = () => {
    if (!self.facebook.accessToken && !self.facebook.userId){
      self.facebook.login();
    }else{
      if(self.facebook.loadbox.html() == '' || (self.facebook.nextUrl && self.facebook.loadbox)){
        self.facebook.render();
      }else{
        $("#facebook-loaded").removeClass("unset");
      }
    }
  }

  self.facebook.render = () => {
    $("#facebook-loaded").removeClass("unset");
    if(self.facebook.more == 1){
      self.facebook.loadbox.find(".social-item_load-more").addClass("loading");
    }
    $.ajax({
      url: self.facebook.nextUrl || "https://graph.facebook.com/v3.2/" + self.facebook.userId + "/albums?limit=2",
      data: {access_token: self.facebook.accessToken},
      type: "GET",
      crossDomain: true,
      dataType: "jsonp",
      success: function(data){
        if(!data.hasOwnProperty("error")){
          self.facebook["show" + self.facebook.content](data);
          if (data.hasOwnProperty("paging") && data.paging.hasOwnProperty("next")) {
            self.facebook.loadbox.append(self.template("load-more-button", data.paging));
          }

          $("#facebook-loaded").find(".reload").removeClass("load");

          self.facebook.nextUrl = null;
        }else{
          self.facebook.login();
        }
      }
    });
  }

  self.facebook.showPhotos = data => {
    var photos = {items: []};
    var i, item;
    for (i in data.data) {
      photos.items.push({
        id: data.data[i].id,
        url: data.data[i].images[data.data[i].images.length - 1].source,
        data_url: data.data[i].images[0].source
      });
    }

    if(self.facebook.more == 1){
      self.facebook.loadbox.find(".social-item_load-more").remove();
    }

    self.facebook.loadbox.append(self.template("loaded-items", photos));
  }

  self.facebook.showAlbums = data => {
    var albums = {items: []};
    var i, item;
    for (i in data.data) {
      albums.items.push({
        url: "https://graph.facebook.com/v3.2/" + data.data[i].id + "/photos?limit=2&fields=images,id",
        name: data.data[i].name
      });
    }

    if(self.facebook.more == 1){
      self.facebook.loadbox.find(".social-item_load-more").remove();
    }

    self.facebook.loadbox.append(self.template("loaded-albums", albums));
  }

  $(document).on("ready", () => {
    itemsWrap = $("#uploaded-images .items-container");
    formatWrap = $(".format-count-container");
    total = $(".summary");
    massChange = $(".mass-change");

    //mask init
    if(typeof settings.ratio != "undefined"){
      itemsWrap.find(".item").each((i, item) => {
        calculateMask(item);
      });
    }

    //upload
    $(".file-upload").liteUploader(settingsUpload)
    .on("lu:before", (e, files) => {self.beforeUpload(e, files)})
    .on("lu:progress", (e, state) => {self.inProgress(e, state)})
    .on("lu:success", (e, response) => {self.successUpload(e, response)});

    $(".drop-zone").liteUploader(settingsUpload)
    .on("lu:before", (e, files) => {self.beforeUpload(e, files)})
    .on("lu:progress", (e, state) => {self.inProgress(e, state)})
    .on("lu:success", (e, response) => {self.successUpload(e, response)})
    .on("drag dragstart dragend dragover dragenter dragleave drop", (e) => {
      e.preventDefault();
      e.stopPropagation();
    })
    .on("dragover dragenter", function () {
      $(this).addClass("active");
    })
    .on("dragleave dragend drop", function () {
      $(this).removeClass("active");
    })
    .on("drop", function(e) {
      $(this).data("liteUploader").startUpload(e.originalEvent.dataTransfer.files);
    });

    $("body").on("dragover dragenter", function () {
      $(".drop-zone").addClass("active");
    })
    .on("dragleave dragend drop", function () {
      $(".drop-zone").removeClass("active");
    })

    $(".file-upload").on("change", function () {
      $(this).data("liteUploader").startUpload();
    });

    //copy, select, delete, change
    $(document).on("click", ".item-controls_delete, .mass-delete", function() {
      self.deleteItem(this);
    });

    $(document).on("click", ".item-controls_copy", function() {
      self.copyItem(this);
    });

    $(document).on("click", ".item-controls_select", function(){
      if($(this).parent().parent().hasClass("selected")) $(this).parent().parent().removeClass("selected");
      else $(this).parent().parent().addClass("selected");
      self.checkSelected();
    });

    $(document).on("change", ".action-group input, .action-group select", function(){
      self.updateItem(this);
    });

    $(document).on("click", ".mass-submit", function(){
      self.updateItem(this);
    });

    $(document).on("change", ".mass-change input[name=\'copy_count\']", function(){
      self.countChange(this);
    });

    $(document).on("click", ".button-count", function(){
      if(self.count(this)) $(this).parent().find("input").trigger("change");
    })

    //instagram
    self.instagram.accessToken = self.getCookie("a_instagram");

    if(window.location.hash == "#instagram"){
      self.instagram.show();
      history.pushState("", document.title, window.location.href.substr(0, window.location.href.indexOf('#')));
    }

    $(document).on("click", ".inst-upload", function(e){
      e.preventDefault();
      self.instagram.show();
    });

    $(document).on("click", "#instagram-loaded .items-container .load-more", function() {
      self.instagram.show($(this).attr("data-page"));
    });

    //facebook
    self.facebook.accessToken = self.getCookie("a_facebook");
    self.facebook.userId = self.getCookie("f_uid");
    self.facebook.loadbox = $('#facebook-loaded .items-container');

    if(window.location.hash == "#facebook"){
      self.facebook.content = "Albums";
      self.facebook.show();
      history.pushState("", document.title, window.location.href.substr(0, window.location.href.indexOf('#')));
    }

    $(document).on("click", ".fb-upload", function(e){
      e.preventDefault();
      self.facebook.content = "Albums";
      self.facebook.show();
    });

    $(document).on("click", "#facebook-loaded .social-album .social-album_header", function(){
      if($(this).parent().hasClass("open")){
        $(this).parent().removeClass("open").addClass("ready-open");
      }else{
        if($(this).parent().hasClass("ready-open")){
          $(this).parent().removeClass("ready-open").addClass("open");
        }else{
          self.facebook.nextUrl = $(this).attr("data-page");
          self.facebook.loadbox = $(this).parent().find(".social-album_content");
          self.facebook.content = "Photos";
          self.facebook.show();
          $(this).parent().addClass("open");
        }
      }
    });

    $(document).on("click", "#facebook-loaded .social-item_load-more", function(){
      self.facebook.nextUrl = $(this).find(".load-more").attr("data-page");
      self.facebook.loadbox = $(this).parent();
      if($(this).parent().hasClass("items-container"))
        self.facebook.content = "Albums";
      else
        self.facebook.content = "Photos";
      self.facebook.more = 1;
      self.facebook.show();
    });

    //social navigatiuon buttons
    $(".loaded-buttons .close-loaded").on("click", function(){
      var wrap = $(this).parent().parent().parent();
      wrap.parent().addClass("unset");
      wrap.find(".open").removeClass("open").addClass("ready-open");
    });

    $(document).on("click", ".social-item", function(){
      if($(this).hasClass("selected")) $(this).removeClass("selected");
      else $(this).addClass("selected");
    });

    $(".loaded-buttons .upload-selected").on("click", function(){
      self.uploadSelected(this);
    });

    $("#facebook-loaded .loaded-buttons .reload").on("click", function(){
      $(this).addClass("load");
      self.facebook.loadbox = $('#facebook-loaded .items-container');
      self.facebook.content = "Albums";
      self.facebook.loadbox.empty();
      self.facebook.show();
    });

    $("#instagram-loaded .loaded-buttons .reload").on("click", function(){
      $(this).addClass("load");
      $("#instagram-loaded .items-container").empty();
      self.instagram.show();
    });
  });

  return self;
};
