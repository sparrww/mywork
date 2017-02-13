$(function(){
    $('#chkall').click(function(){
        if($(this).is(':checked')){
            $('input[name^=chkv]').attr('checked',true);
            var chknum = $('input[name^=chkv]').length;
            if(chknum>0){
                $('.chknum').text('已选中'+chknum+'个');
                $('#toolbar').fadeIn('fast');
            }
        }else{
            $('input[name^=chkv]').removeAttr('checked');
            $('#toolbar').fadeOut('fast');
        }
    });
    $('input[name^=chkv]').click(function(){
        $('#chkall').removeAttr('checked');
        var chknum = 0;
        $('input[name^=chkv]').each(function(){
            if($(this).is(':checked')){
                chknum++;
            }
        });
        if(chknum>0){
            $('.chknum').text('已选中'+chknum+'个');
            $('#toolbar').fadeIn('fast');
        }else{
            $('#toolbar').fadeOut('fast');
        }
    });
    $('.select').mouseover(function(){
        $(this).children('span').removeClass().addClass('uptria');
    }).mouseout(function(){
        $(this).children('span').removeClass().addClass('downtria');
    });
    //do select
    $('.selectcon a').on("click",function(){
        var sv = $(this).attr('v'),
            sstr = $(this).text();
        $(this).parent('div').find('a').removeClass('cur');
        $(this).parents('.select').removeClass('slcerr').children('a').text(sstr);
        $(this).parents('.select').children('input').val(sv);
        $(this).addClass('cur');
    });
    $('.menuc').click(function(){
        var mcon = $(this).children('.menucon');
        if(mcon.is(':hidden')){
            mcon.slideDown();
        }else{
            mcon.slideUp();
        }
    });
    /*conlist item textname width*/
    $('.c1').mouseover(function(){
        var _w = $(this).children('.ctlbar').width(),
            w = $(this).children('.textname').width(),
            _ = 0;
        _ = parseInt((1-_w/w)*100);
        $(this).children('.textname').css('width',_+'%');
        $(this).children('.ctlbar').css('display','inline-block');
    }).mouseout(function(){
        $(this).children('.ctlbar').hide();
        $(this).children('.textname').css('width','100%');
    });
    $('input').click(function(){
        $(this).removeClass('ipterr');
    })
    
    
})
function showmenu(id){
    var cur = 1;
    if(id<=0) return;
    $('.menucon a').each(function(){
        if(cur==id){
            $(this).addClass('cur');
            $(this).parent('.menucon').slideDown();
        }
        cur++;
    });
}
function confirmlogout(url){
    if (confirm("确认要退出系统?")){
        location.href=url;
    }
}
function subform(url){
    $.ajax({
        type:'post',
        dataType:'json',
        data:$('#newslistform').serializeArray(),
        url:url,
        success:function(rdata){
            var res = eval(rdata),
                ico = res.err==1?'fail.png':'succ.png';
            $.dialog.tips(res.msg, 2, ico,function(){
                if(res.url) location.href=res.url;
                if(res.tag){
                    $('#'+res.tag).addClass('ipterr');
                    $('#'+res.tag+'err').html(res.msg);
                }
            })
        },
        error:function(){$.dialog.tips('网络异常，请稍后再试', 2, 'fail.png')}
    });
}
function edit(obj,url){
    var id = obj.parents('.co1').find('input[name^=chkv]').val();
    $.ajax({
        type:'post',
        dataType:'json',
        data:'id='+id,
        url:url,
        success:function(rdata){
            var res = eval(rdata);
            if(res.err==100){location.href=res.url;return;}
            $.dialog.tips(res.msg, 2, 'fail.png');
        },
        error:function(){$.dialog.tips('网络异常，请稍后再试', 2, 'fail.png')}
    });
}
function del(url){
    var d = false;
    $('input[name^=chkv]').each(function(){
        if($(this).is(':checked')){
            d = true;
            return true;
        }
    });
    if(d==false){
        $.dialog.tips('你没有选择要删除的内容', 2, 'fail.png');
        return;
    }
    $.ajax({
        type:'post',
        dataType:'json',
        data:$('#newslistform').serializeArray(),
        url:url,
        success:function(rdata){
            var res = eval(rdata);
            if(res.err==100){
                //$.dialog.tips(res.msg, 2, 'succ.png');
                removedelline();
            }else{
                $.dialog.tips(res.msg, 2, 'fail.png');
            }
        },
        error:function(){
            $.dialog.tips('网络连接异常，请稍后再试', 2, 'fail.png');
        }
    })
}
function removedelline(){
    $('input[name^=chkv]').each(function(){
       if($(this).is(':checked')){
           var E = $(this).parents('.item');
           E.slideUp(500,function(){E.remove()});
       } 
    });
}
function loadCSS(url){
    var cssLink = document.createElement("link");
    cssLink.rel = "stylesheet";
    cssLink.rev = "stylesheet";
    cssLink.type = "text/css";
    cssLink.media = "screen";
    cssLink.href = url;
    $("head").append(cssLink);
}
function showdldata(ID){
    $('#'+ID).glDatePicker({
        showAlways: false,
        zIndex: 1000,
        width: 318,
        onClick: function(target, cell, date, data) {
            target.val(date.getFullYear()+'-'+
              (date.getMonth()+1)+'-' +
              date.getDate());
        }
    });
}