var instagram = function(params) {
  var self = this;
  var accessToken = false;
  var nextUrl = false;
  var settings = {};
  settings.photosLimit = 15;

  var init = function() {
    accessToken = Cookies.get("a_instagram");
    settings.clientId = params.clientId;
  }

  var login = function() {
    window.location = "https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=" + settings.serverRedirect + "api/instagram.php&response_type=code";
  }

  //instagram old api
  // self.instagram = {};
  // self.instagram.accessToken = false;
  // self.instagram.nextUrl = null;
  //
  // self.instagram.login  = () => {
  //   window.location = "https://instagram.com/oauth/authorize/?client_id=d103bb3cf5c84baca2a28a5a502ec7be&redirect_uri=" + settings.serverRedirect + "api/instagram.php&response_type=code";
  // };
  //
  // self.instagram.show = function(page = null) {
  //   if (!self.instagram.accessToken){
  //     self.instagram.login();
  //   }else{
  //     self.instagram.nextUrl = page;
  //     if($('#instagram-loaded .items-container').html() == '' || self.instagram.nextUrl){
  //       self.instagram.renderPhotos();
  //     }else{
  //       $("#instagram-loaded").removeClass("unset");
  //       $("#instagram-loaded").find(".items-container").removeClass("load");
  //     }
  //   }
  // };
  //
  // self.instagram.renderPhotos = function() {
  //   $("#instagram-loaded").removeClass("unset");
  //   if(self.instagram.nextUrl){
  //     $("#instagram-loaded .items-container").find(".social-item_load-more").addClass("loading");
  //   }
  //   $.ajax({
  //     url: self.instagram.nextUrl || "https://api.instagram.com/v1/users/self/media/recent/",
  //     data: {access_token: self.instagram.accessToken, count: 15},
  //     type: "GET",
  //     xhrFields: { withCredentials: true },
  //     crossDomain:true,
  //     dataType: "jsonp",
  //     beforeSend: () => {
  //       if(!onlineTrigger){
  //         self.error("Проверьте подключение к интернету и попробуйте еще раз.");
  //       }
  //     },
  //     success: photos => {
  //       //console.log(request.getAllResponseHeaders());
  //       if(photos.hasOwnProperty("meta") && photos.meta.hasOwnProperty("code") && photos.meta.code == 200){
  //         var data = {items: []};
  //         var i, item;
  //         for (i in photos.data) {
  //           var img = new Image;
  //           img.src = photos.data[i].link + "media/?size=l";
  //           img.complete = function(){
  //             console.log(img.src);
  //           };
  //           if(photos.data[i].type == "image" || photos.data[i].type == "carousel"){
  //             data.items.push({
  //               id: photos.data[i].id,
  //               url: photos.data[i].images.low_resolution.url,
  //               data_url: photos.data[i].link + "media/?size=l"
  //             });
  //           }
  //         }
  //
  //         if(self.instagram.nextUrl){
  //           $("#instagram-loaded .items-container").find(".social-item_load-more").remove();
  //         }
  //
  //         $("#instagram-loaded .items-container").append(self.template("loaded-items", data));
  //
  //         if (photos.hasOwnProperty("pagination") && photos.pagination.hasOwnProperty("next_url")) {
  //           $("#instagram-loaded .items-container").append(self.template("load-more-button", photos.pagination));
  //         }
  //
  //         $("#instagram-loaded").find(".items-container").removeClass("load");
  //
  //         self.instagram.nextUrl = null;
  //       }else{
  //         self.instagram.login();
  //       }
  //     }
  //   });
  // };
};
