(function(win) {
  function Dialog(params) {
    this.obj = null;
    this.tplId = "common_dialog_tpl";

    var self = this;
    this.loadtpl(function(){
       self.dialog(params);
    });
       
  }
  //加载模板，加载过不再加载
  Dialog.prototype.loadtpl = function(success,error){
      //配置服务器url
      var baseUrl = "/resource/wxh3.0/tpl/";     
      if($("#common_dialog_tpl").length > 0){
        success && success();
      }else{
         $.ajax({
            type:"GET",
            url: baseUrl + "dialog.html",
            data: {},
            dataType: "text",
            success: function(data) {
              $("body").append(data);
              success && success();
            },
            error: function(data) {
              error && error(datas)
            }
        });
      }
     
  };

  Dialog.prototype.dialog = function(params) {
    var defaultP = {
      title: "提示",
      content: "",
      confirm_text: "确定",
      cancle_text: "取消",
      beforeFn:function(){},
      confirmFn: function() {},
      cancleFn:function(){},
      closeFun: function() {},
      autodel:true,//自动删除
    };
    if (params) {
      for (key in params) {
        defaultP[key] = params[key];
      }
    }  
    if(defaultP.type == "0"){
      defaultP["cls"] = "msg";
    }else{
      defaultP["cls"] = "data";
    }
    var html = $.template(this.tplId, defaultP);
    var obj = $(html);
    this.obj = obj;
    $("body").append(obj);
    defaultP.beforeFn && defaultP.beforeFn(obj);
    obj.find(".confirm").bind("click", function() {
      if (defaultP && defaultP.confirmFn) defaultP.confirmFn();
      if(defaultP.autodel){
        obj.remove();  
      }      
    });
    obj.find(".cancle").bind("click", function() {
      if (defaultP && defaultP.cancleFn) defaultP.cancleFn();
      if(defaultP.autodel){
        obj.remove();  
      }
    });
    obj.find(".dialog_close").bind("click", function() {
      if (defaultP && defaultP.closeFun) defaultP.closeFun();
      if(defaultP.autodel){
        obj.remove();  
      }
    });
    //alert 只有一个确定按钮
    if(params && params.type == "0"){
      obj.find(".cancle").hide();
      obj.find(".confirm").hide();
    }
    
  } 
  Dialog.prototype.show = function(params) {
   this.obj.show();
  }
  Dialog.prototype.hide = function(params) {
   this.obj.hide();
  }
 
  win.Dialog = Dialog;
  win.alert = function(str){
    new Dialog({
      type:"0",              
      content:str
    });
  }
})(window);
