var facebook = function(params){
  var self = this;
  var accessToken = false;
  var userId = false;
  var nextUrl = false;
  var pageWrap = null;
  var loadbox = null;
  var more = null;
  var content = "Albums";
  var settings = {};
  settings.albumsLimit = 15;
  settings.photosLimit = 15;

  var init = function() {
    //set auth
    accessToken = Cookies.get("a_facebook");
    userId = Cookies.get("f_uid");
    //init page wrap & loadbox
    pageWrap = $(template("facebook", null));
    //add page
    main.uploadWrap.append(pageWrap);
    //init loadbox;
    loadbox = pageWrap.find(".items-container");
    //set params
    settings.clientId = params.clientId;
    settings.redirectUrl = params.redirectUrl;
    if(params.hasOwnProperty("albumsLimit")){
      settings.albumsLimit = params.albumsLimit;
    }
    if(params.hasOwnProperty("photosLimit")){
      settings.photosLimit = params.photosLimit;
    }
  };

  self.albums = function() {
    loadbox.addClass("load");
    $("body").css("overflow", "hidden");
    showData();
  };

  self.photos = function(album) {
    if($(album).parent().hasClass("open")){
      $(album).parent().removeClass("open").addClass("ready-open");
    }else{
      if($(album).parent().hasClass("ready-open")){
        $(album).parent().removeClass("ready-open").addClass("open");
      }else{
        nextUrl = $(album).attr("data-page");
        loadbox = $(album).parent().find(".social-album_content");
        content = "Photos";
        $(album).parent().addClass("load");
        showData();
      }
    }
  };

  self.loadMore = function(btnWrap) {
    nextUrl = $(btnWrap).find(".load-more").attr("data-page");
    loadbox = $(btnWrap).parent();

    if($(btnWrap).parent().hasClass("items-container"))
      content = "Albums";
    else
      content = "Photos";

    more = 1;
    showData();
  };

  self.reload = function() {
    loadbox = pageWrap.find(".items-container");
    loadbox.addClass("load");
    loadbox.empty();
    content = "Albums";
    showData();
  }

  var showData = function() {
    if (!accessToken && !userId){
      login();
    }else{
      if(loadbox.html() == '' || (nextUrl && loadbox)){
        render();
      }else{
        pageWrap.removeClass("unset");
        loadbox.removeClass("load");
      }
    }
  };

  var login  = function() {
    window.location = "https://www.facebook.com/v3.2/dialog/oauth?client_id=" + settings.clientId + "&redirect_uri=" + server + "api/facebook.php&response_type=code";
  };

  var render = function() {
    pageWrap.removeClass("unset");
    if(more == 1){
      loadbox.find(".social-item_load-more").addClass("loading");
    }
    $.ajax({
      url: nextUrl || "https://graph.facebook.com/v3.2/" + userId + "/albums?limit=" + settings.albumsLimit,
      data: {access_token: accessToken},
      type: "GET",
      crossDomain:true,
      dataType: "jsonp",
      beforeSend: () => {
        if(!onlineTrigger){
          self.error("Проверьте подключение к интернету и попробуйте еще раз.");
        }
      },
      success: data => {
        if(!data.hasOwnProperty("error")){
          eval("show" + content)(data);
          if (data.hasOwnProperty("paging") && data.paging.hasOwnProperty("next")) {
            loadbox.append(template("load-more-button", data.paging));
          }

          loadbox.removeClass("load");

          nextUrl = null;
        }else{
          login();
        }
      }
    });
  };

  var showPhotos = function(data) {
    var photos = {items: []};
    for (var i in data.data) {
      photos.items.push({
        id: data.data[i].id,
        url: data.data[i].images[data.data[i].images.length - 1].source,
        data_url: data.data[i].images[0].source
      });
    }

    if(more == 1){
      loadbox.find(".social-item_load-more").remove();
    }

    loadbox.append(template("loaded-items", photos));
    loadbox.parent().addClass("open").removeClass("load");
  };

  var showAlbums = data => {
    var albums = {items: []};
    for (var i in data.data) {
      albums.items.push({
        url: "https://graph.facebook.com/v3.2/" + data.data[i].id + "/photos?limit=" + settings.photosLimit + "&fields=images,id",
        name: data.data[i].name
      });
    }

    if(more == 1){
      loadbox.find(".social-item_load-more").remove();
    }

    loadbox.append(self.template("loaded-albums", albums));
  };

  init();

  return self;
};
