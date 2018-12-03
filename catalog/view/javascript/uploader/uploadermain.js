var uploader = (settings) => {
  var self = this;

  var settingsUpload = {
    rules: {
      allowedFileTypes: settings.allowedFormats,
    },
    script: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
    singleFileUploads: true
  };

  var views;
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
    var message = $('<div class="uploader-error_message"><span class="message-text">" + data + "</span><button class="remove-message"><i class="far fa-times-circle"></i></button></div>');
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
      if($(trigger).attr("name") == "copy_count")
        items[0] = $(trigger).parent().parent().parent().parent().attr("data-name");
      else
        items[0] = $(trigger).parent().parent().parent().attr("data-name");

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
    var itemsView = $(trigger).parent().parent().parent();
        items = itemsView.find(".selected"),
        social_upload = {};

    items.each((i, item) => {
      social_upload[i] = $(item).attr("data-url")
    });

    if(items.length > 0){
      $.ajax({
        url: "index.php?route=module/" + settings.uploaderType + "_uploader/upload",
        type: "post",
        dataType: "json",
        data: "social_upload=" + encodeURIComponent(JSON.stringify(social_upload)),
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

            items.removeClass("selected");
            itemsView.addClass("unset");
            views.find("#uploaded-images").removeClass("unset");
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
  self.instagram.doRequest = false;

  self.instagram.login  = () => {
    //console.log('https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=' + encodeURI(window.location) + '&response_type=code');
    window.location = 'https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=http://photoradost.loc/social/redirect.php&response_type=code';
    //window.open('https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=http://photoradost.loc/social/redirect.php?referer=' + window.location + '&response_type=code', 'instagram', '_blank');
    //window.open('https://instagram.com/oauth/authorize/?client_id=9a7fb1211f9749ce8fb7f7cc0b40b040&redirect_uri=https://fotis.su/api/instagram/redirect?referer=' + encodeURI(window.location) + '&response_type=code', 'instagram', 'width=650,height=350');
    //window.open('https://instagram.com/oauth/authorize/?client_id=9a7fb1211f9749ce8fb7f7cc0b40b040&redirect_uri=https://fotis.su/api/instagram/redirect?referer=' + window.location + '&response_type=code', 'instagram', '_blank');
  }

  self.instagram.show = () => {
    console.log(self.instagram.accessToken);
    var insAcc = self.getCookie('insAcc');
    if (!insAcc){
      self.instagram.login();
    }else{
      self.instagram.setData(insAcc);
    }
  }

  self.instagram.scrollHandler = wrap => {
  	if(wrap.scrollHeight - wrap.scrollTop <= wrap.offsetHeight && self.instagram.nextUrl !== null && !self.instagram.doRequest) {
      self.instagram.renderPhotos();
  	}
  }

  self.instagram.setData = (data) => {
    self.instagram.accessToken = data;
    $('#instagram-loaded .loaded-content').empty();
    self.instagram.renderPhotos();
  }

  self.instagram.renderPhotos = () => {
    self.instagram.doRequest = true;
    $.ajax({
      url: self.instagram.nextUrl || 'https://api.instagram.com/v1/users/self/media/recent/',
      data: {access_token: self.instagram.accessToken, count: 30},
      type: 'GET',
      crossDomain: true,
      dataType: 'jsonp',
      success: function (photos) {

        var data = {items: []};
        var i, item;
        for (i in photos.data) {
          if(photos.data[i].type == "image" || photos.data[i].type == "carousel"){
            data.items.push({
              id: photos.data[i].id,
              url: photos.data[i].images.low_resolution.url,
              data_url: photos.data[i].link + 'media/?size=l'
            });
          }
        }

        views.find(".items").addClass("unset");
        views.find("#instagram-loaded").removeClass("unset");

        $("#instagram-loaded .items-container").append(self.template("loaded-items-instagram", data));

        self.instagram.nextUrl = null;
        if (photos.hasOwnProperty('pagination') && photos.pagination.hasOwnProperty('next_url')) {
          self.instagram.nextUrl = photos.pagination.next_url;
        }

        self.instagram.doRequest = false;
      }
    });
  }

  $(document).on("ready", () => {
    views = $(".items-wrap");
    itemsWrap = views.find("#uploaded-images .items-container");
    formatWrap = $(".format-count-container");
    total = $(".summary");
    massChange = $(".mass-change");

    if(typeof settings.ratio != "undefined"){
      itemsWrap.find(".item").each((i, item) => {
        calculateMask(item);
      });
    }

    window.addEventListener("message", function(event) {
      try {
        var data = JSON.parse(event.data);
        if(data.hasOwnProperty('instagram')){
          self.instagram.setData(data.instagram);
        }
      } catch (err) {
          console.log(err);
      }
    }, false);

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

    $(document).on("click", ".inst-upload", function(e){
      e.preventDefault();
      self.instagram.show();
    });

    $('#instagram-loaded .items-container').on('scroll', function() {
        self.instagram.scrollHandler(this);
        console.log(1);
    });

    $('#instagram-loaded .items-container').on('touchmove', function(e) {
        self.instagram.scrollHandler($('body')[0]);
    });

    $(document).on("click", ".close-loaded", function(){
      $(this).parent().parent().parent().addClass("unset");
      views.find("#uploaded-images").removeClass("unset");
    });

    $(document).on("click", ".social-item", function(){
      if($(this).hasClass("selected")) $(this).removeClass("selected");
      else $(this).addClass("selected");
    });

    $(document).on("click", ".upload-selected", function(){
      self.uploadSelected(this);
    });
  });

  return self;
};
