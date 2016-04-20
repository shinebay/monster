//basic UI components,including common js and css components
window.ZENG=window.ZENG||{};ZENG.dom={getById:function(id){return document.getElementById(id)},get:function(e){return(typeof(e)=="string")?document.getElementById(e):e},createElementIn:function(tagName,elem,insertFirst,attrs){var _e=(elem=ZENG.dom.get(elem)||document.body).ownerDocument.createElement(tagName||"div"),k;if(typeof(attrs)=='object'){for(k in attrs){if(k=="class"){_e.className=attrs[k]}else if(k=="style"){_e.style.cssText=attrs[k]}else{_e[k]=attrs[k]}}}insertFirst?elem.insertBefore(_e,elem.firstChild):elem.appendChild(_e);return _e},getStyle:function(el,property){el=ZENG.dom.get(el);if(!el||el.nodeType==9){return null}var w3cMode=document.defaultView&&document.defaultView.getComputedStyle,computed=!w3cMode?null:document.defaultView.getComputedStyle(el,''),value="";switch(property){case"float":property=w3cMode?"cssFloat":"styleFloat";break;case"opacity":if(!w3cMode){var val=100;try{val=el.filters['DXImageTransform.Microsoft.Alpha'].opacity}catch(e){try{val=el.filters('alpha').opacity}catch(e){}}return val/100}else{return parseFloat((computed||el.style)[property])}break;case"backgroundPositionX":if(w3cMode){property="backgroundPosition";return((computed||el.style)[property]).split(" ")[0]}break;case"backgroundPositionY":if(w3cMode){property="backgroundPosition";return((computed||el.style)[property]).split(" ")[1]}break}if(w3cMode){return(computed||el.style)[property]}else{return(el.currentStyle[property]||el.style[property])}},setStyle:function(el,properties,value){if(!(el=ZENG.dom.get(el))||el.nodeType!=1){return false}var tmp,bRtn=true,w3cMode=(tmp=document.defaultView)&&tmp.getComputedStyle,rexclude=/z-?index|font-?weight|opacity|zoom|line-?height/i;if(typeof(properties)=='string'){tmp=properties;properties={};properties[tmp]=value}for(var prop in properties){value=properties[prop];if(prop=='float'){prop=w3cMode?"cssFloat":"styleFloat"}else if(prop=='opacity'){if(!w3cMode){prop='filter';value=value>=1?'':('alpha(opacity='+Math.round(value*100)+')')}}else if(prop=='backgroundPositionX'||prop=='backgroundPositionY'){tmp=prop.slice(-1)=='X'?'Y':'X';if(w3cMode){var v=ZENG.dom.getStyle(el,"backgroundPosition"+tmp);prop='backgroundPosition';typeof(value)=='number'&&(value=value+'px');value=tmp=='Y'?(value+" "+(v||"top")):((v||'left')+" "+value)}}if(typeof el.style[prop]!="undefined"){el.style[prop]=value+(typeof value==="number"&&!rexclude.test(prop)?'px':'');bRtn=bRtn&&true}else{bRtn=bRtn&&false}}return bRtn},getScrollTop:function(doc){var _doc=doc||document;return Math.max(_doc.documentElement.scrollTop,_doc.body.scrollTop)},getClientHeight:function(doc){var _doc=doc||document;return _doc.compatMode=="CSS1Compat"?_doc.documentElement.clientHeight:_doc.body.clientHeight}};ZENG.string={RegExps:{trim:/^\s+|\s+$/g,ltrim:/^\s+/,rtrim:/\s+$/,nl2br:/\n/g,s2nb:/[\x20]{2}/g,URIencode:/[\x09\x0A\x0D\x20\x21-\x29\x2B\x2C\x2F\x3A-\x3F\x5B-\x5E\x60\x7B-\x7E]/g,escHTML:{re_amp:/&/g,re_lt:/</g,re_gt:/>/g,re_apos:/\x27/g,re_quot:/\x22/g},escString:{bsls:/\\/g,sls:/\//g,nl:/\n/g,rt:/\r/g,tab:/\t/g},restXHTML:{re_amp:/&amp;/g,re_lt:/&lt;/g,re_gt:/&gt;/g,re_apos:/&(?:apos|#0?39);/g,re_quot:/&quot;/g},write:/\{(\d{1,2})(?:\:([xodQqb]))?\}/g,isURL:/^(?:ht|f)tp(?:s)?\:\/\/(?:[\w\-\.]+)\.\w+/i,cut:/[\x00-\xFF]/,getRealLen:{r0:/[^\x00-\xFF]/g,r1:/[\x00-\xFF]/g},format:/\{([\d\w\.]+)\}/g},commonReplace:function(s,p,r){return s.replace(p,r)},format:function(str){var args=Array.prototype.slice.call(arguments),v;str=String(args.shift());if(args.length==1&&typeof(args[0])=='object'){args=args[0]}ZENG.string.RegExps.format.lastIndex=0;return str.replace(ZENG.string.RegExps.format,function(m,n){v=ZENG.object.route(args,n);return v===undefined?m:v})}};ZENG.object={routeRE:/([\d\w_]+)/g,route:function(obj,path){obj=obj||{};path=String(path);var r=ZENG.object.routeRE,m;r.lastIndex=0;while((m=r.exec(path))!==null){obj=obj[m[0]];if(obj===undefined||obj===null){break}}return obj}};var ua=ZENG.userAgent={},agent=navigator.userAgent;ua.ie=9-((agent.indexOf('Trident/5.0')>-1)?0:1)-(window.XDomainRequest?0:1)-(window.XMLHttpRequest?0:1);if(typeof(ZENG.msgbox)=='undefined'){ZENG.msgbox={}}ZENG.msgbox._timer=null;ZENG.msgbox.loadingAnimationPath=ZENG.msgbox.loadingAnimationPath||("loading.gif");ZENG.msgbox.show=function(msgHtml,type,timeout,opts){if(typeof(opts)=='number'){opts={topPosition:opts}}opts=opts||{};var _s=ZENG.msgbox,template='<span class="zeng_msgbox_layer" style="display:none;z-index:10000;" id="mode_tips_v2"><span class="gtl_ico_{type}"></span>{loadIcon}{msgHtml}<span class="gtl_end"></span></span>',loading='<span class="gtl_ico_loading"></span>',typeClass=[0,0,0,0,"succ","fail","clear"],mBox,tips;_s._loadCss&&_s._loadCss(opts.cssPath);mBox=ZENG.dom.get("q_Msgbox")||ZENG.dom.createElementIn("div",document.body,false,{className:"zeng_msgbox_layer_wrap bounceIn animated d4"});mBox.id="q_Msgbox";mBox.style.display="";mBox.innerHTML=ZENG.string.format(template,{type:typeClass[type]||"hits",msgHtml:msgHtml||"",loadIcon:type==6?loading:""});_s._setPosition(mBox,timeout,opts.topPosition)};ZENG.msgbox._setPosition=function(tips,timeout,topPosition){timeout=timeout||5000;var _s=ZENG.msgbox,bt=ZENG.dom.getScrollTop(),ch=ZENG.dom.getClientHeight(),t=Math.floor(ch/2)-40;ZENG.dom.setStyle(tips,"top",((document.compatMode=="BackCompat"||ZENG.userAgent.ie<7)?bt:0)+((typeof(topPosition)=="number")?topPosition:t)+"px");clearTimeout(_s._timer);tips.firstChild.style.display="";timeout&&(_s._timer=setTimeout(_s.hide,timeout))};ZENG.msgbox.hide=function(timeout){var _s=ZENG.msgbox;if(timeout){clearTimeout(_s._timer);_s._timer=setTimeout(_s._hide,timeout)}else{_s._hide()}};ZENG.msgbox._hide=function(){var _mBox=ZENG.dom.get("q_Msgbox"),_s=ZENG.msgbox;clearTimeout(_s._timer);if(_mBox){var _tips=_mBox.firstChild;ZENG.dom.setStyle(_mBox,"display","none")}};
(function($){function maybeCall(thing,ctx){return(typeof thing=='function')?(thing.call(ctx)):thing};function isElementInDOM(ele){while(ele=ele.parentNode){if(ele==document)return true}return false};function Tipsy(element,options){this.$element=$(element);this.options=options;this.enabled=true;this.fixTitle()};Tipsy.prototype={show:function(){var title=this.getTitle();if(title&&this.enabled){var $tip=this.tip();$tip.find('.tipsy-inner')[this.options.html?'html':'text'](title);$tip[0].className='tipsy';$tip.remove().css({top:0,left:0,visibility:'hidden',display:'block'}).prependTo(document.body);var pos=$.extend({},this.$element.offset(),{width:this.$element[0].offsetWidth,height:this.$element[0].offsetHeight});var actualWidth=$tip[0].offsetWidth,actualHeight=$tip[0].offsetHeight,gravity=maybeCall(this.options.gravity,this.$element[0]);var tp;switch(gravity.charAt(0)){case'n':tp={top:pos.top+pos.height+this.options.offset,left:pos.left+pos.width/2-actualWidth/2};break;case's':tp={top:pos.top-actualHeight-this.options.offset,left:pos.left+pos.width/2-actualWidth/2};break;case'e':tp={top:pos.top+pos.height/2-actualHeight/2,left:pos.left-actualWidth-this.options.offset};break;case'w':tp={top:pos.top+pos.height/2-actualHeight/2,left:pos.left+pos.width+this.options.offset};break}if(gravity.length==2){if(gravity.charAt(1)=='w'){tp.left=pos.left+pos.width/2-15}else{tp.left=pos.left+pos.width/2-actualWidth+15}}$tip.css(tp).addClass('tipsy-'+gravity);$tip.find('.tipsy-arrow')[0].className='tipsy-arrow tipsy-arrow-'+gravity.charAt(0);if(this.options.className){$tip.addClass(maybeCall(this.options.className,this.$element[0]))}if(this.options.fade){$tip.stop().css({opacity:0,display:'block',visibility:'visible'}).animate({opacity:this.options.opacity})}else{$tip.css({visibility:'visible',opacity:this.options.opacity})}}},hide:function(){if(this.options.fade){this.tip().stop().fadeOut(function(){$(this).remove()})}else{this.tip().remove()}},fixTitle:function(){var $e=this.$element;if($e.attr('title')||typeof($e.attr('original-title'))!='string'){$e.attr('original-title',$e.attr('title')||'').removeAttr('title')}},getTitle:function(){var title,$e=this.$element,o=this.options;this.fixTitle();var title,o=this.options;if(typeof o.title=='string'){title=$e.attr(o.title=='title'?'original-title':o.title)}else if(typeof o.title=='function'){title=o.title.call($e[0])}title=(''+title).replace(/(^\s*|\s*$)/,"");return title||o.fallback},tip:function(){if(!this.$tip){this.$tip=$('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"></div>');this.$tip.data('tipsy-pointee',this.$element[0])}return this.$tip},validate:function(){if(!this.$element[0].parentNode){this.hide();this.$element=null;this.options=null}},enable:function(){this.enabled=true},disable:function(){this.enabled=false},toggleEnabled:function(){this.enabled=!this.enabled}};$.fn.tipsy=function(options){if(options===true){return this.data('tipsy')}else if(typeof options=='string'){var tipsy=this.data('tipsy');if(tipsy)tipsy[options]();return this}options=$.extend({},$.fn.tipsy.defaults,options);function get(ele){var tipsy=$.data(ele,'tipsy');if(!tipsy){tipsy=new Tipsy(ele,$.fn.tipsy.elementOptions(ele,options));$.data(ele,'tipsy',tipsy)}return tipsy}function enter(){var tipsy=get(this);tipsy.hoverState='in';if(options.delayIn==0){tipsy.show()}else{tipsy.fixTitle();setTimeout(function(){if(tipsy.hoverState=='in')tipsy.show()},options.delayIn)}};function leave(){var tipsy=get(this);tipsy.hoverState='out';if(options.delayOut==0){tipsy.hide()}else{setTimeout(function(){if(tipsy.hoverState=='out')tipsy.hide()},options.delayOut)}};if(!options.live)this.each(function(){get(this)});if(options.trigger!='manual'){var binder=options.live?'live':'bind',eventIn=options.trigger=='hover'?'mouseenter':'focus',eventOut=options.trigger=='hover'?'mouseleave':'blur';this[binder](eventIn,enter)[binder](eventOut,leave)}return this};$.fn.tipsy.defaults={className:null,delayIn:0,delayOut:0,fade:false,fallback:'',gravity:'n',html:false,live:false,offset:0,opacity:0.8,title:'title',trigger:'hover'};$.fn.tipsy.revalidate=function(){$('.tipsy').each(function(){var pointee=$.data(this,'tipsy-pointee');if(!pointee||!isElementInDOM(pointee)){$(this).remove()}})};$.fn.tipsy.elementOptions=function(ele,options){return $.metadata?$.extend({},options,$(ele).metadata()):options};$.fn.tipsy.autoNS=function(){return $(this).offset().top>($(document).scrollTop()+$(window).height()/2)?'s':'n'};$.fn.tipsy.autoWE=function(){return $(this).offset().left>($(document).scrollLeft()+$(window).width()/2)?'e':'w'};$.fn.tipsy.autoBounds=function(margin,prefer){return function(){var dir={ns:prefer[0],ew:(prefer.length>1?prefer[1]:false)},boundTop=$(document).scrollTop()+margin,boundLeft=$(document).scrollLeft()+margin,$this=$(this);if($this.offset().top<boundTop)dir.ns='n';if($this.offset().left<boundLeft)dir.ew='w';if($(window).width()+$(document).scrollLeft()-$this.offset().left<margin)dir.ew='e';if($(window).height()+$(document).scrollTop()-$this.offset().top<margin)dir.ns='s';return dir.ns+(dir.ew?dir.ew:'')}}})(jQuery);
var IE9=navigator.userAgent.indexOf("MSIE")!=-1&&navigator.userAgent.indexOf("MSIE 9")==-1&&navigator.userAgent.indexOf("MSIE 10")==-1&&navigator.userAgent.indexOf("MSIE 11")==-1;
var width,height;
var cur_textarea_obj=document.getElementById('pub_content');
$.fn.lazyhover = function(fuc_on, fuc_out, de_on, de_out) {
    var self = $(this);
    var flag = 1;
    var h;
    var handle = function(elm){
        clearTimeout(h);
        if(!flag) self.removeData('timer');
        return flag ? fuc_on.apply(elm) : fuc_out.apply(elm);
    };
    var time_on  = de_on  || 500;
    var time_out = 0;
    var timer = function(elm){
        h && clearTimeout(h);
        h = setTimeout(function() { handle(elm);  }, flag ? time_on : time_out);
        self.data('timer', h);
    }
    self.live('mouseover',function(){
        flag = 1 ;
        timer(this);
    })
    self.live('mouseleave',function(){
        flag = 0 ;
        timer(this);
    })
}
var avatar=decodeURIComponent(getCookie(cookie_prefix+'avatar_small'));
$(document).ready(function(){
    width=$(window).width();
    height=$(window).height();
    //jquery tipsy basic setting
    $('.tipsy_south').tipsy({gravity:'s',trigger:'hover',fade:true,live:true});
    $('.tipsy_north').tipsy({gravity:'n',trigger:'hover',fade:true,live:true});
    $('.header_tipsy').tipsy({gravity:'n',trigger:'hover',fade:true,live:true});
    var avatar_big=decodeURIComponent(getCookie(cookie_prefix+'avatar_big'));
    if($('#avatar_left').attr('src')!=avatar_big&&avatar_big!=''){
        $('#avatar_left').attr('src',avatar_big);
    }
    var user_info=decodeURIComponent(getCookie(cookie_prefix+'user_info'));
    if(user_info!=''){
        var user_info_arr=user_info.split('_');
        user_info_arr[0]!=$('#my_thread_num').html()&&$('#my_thread_num').html(user_info_arr[0]);
        user_info_arr[1]!=$('#my_reply_num').html()&&$('#my_reply_num').html(user_info_arr[1]);
        user_info_arr[2]!=$('#my_collect_num').html()&&$('#my_collect_num').html(user_info_arr[2]);
    }
    //login & register tab switch
    $('#account_tab div').mousedown(function(){
        !$(this).hasClass('cur_tab')&&$(this).addClass('cur_tab').siblings('div').removeClass('cur_tab');
        $('.login_input_container').eq($(this).index()).removeClass('none').siblings('.login_input_container').addClass('none');
        if($(this).hasClass('register_tab')){
            $('#register_captcha').size()>0&&$('#register_captcha').click();
        }else{
            $('#login_captcha').size()>0&&$('#login_captcha').click();
        }
    });
    //register action
    $('.register_sub').click(function(){
        var reg_username=$('#register_username').val().replace(/\s/g,"");
        var reg_email=$('#register_email').val().replace(/\s/g,"");
        var reg_pwd=$('#register_pwd').val();
        var reg_verify='';
        if($('#register_captcha').size()>0){
            reg_verify=$('#register_verify_input').val();
        }
        var _t=$(this);
        register(reg_username,reg_email,reg_pwd,reg_verify,$('#register_remember').val(),$('#register_captcha'),_t);
    });
    $('#register_submit').click(function(){
        var reg_username=$('#pop_register_username').val().replace(/\s/g,"");
        var reg_email=$('#pop_register_email').val().replace(/\s/g,"");
        var reg_pwd=$('#pop_register_pwd').val();
        var reg_verify='';
        if($('#pop_register_captcha').size()>0){
            reg_verify=$('#pop_register_verify').val();
        }
        var _t=$(this);
        register(reg_username,reg_email,reg_pwd,reg_verify,$('#pop_register_remember').val(),$('#pop_register_captcha'),_t);
    });
    $('.save_pwd').click(function(){
        var $hidden=$(this).children('input[type=hidden]');
        if($hidden.val()==0){
            $hidden.val(1)
        }else{
            $hidden.val(0);
        }
    })
    //login action
    $('.login_sub').click(function(){
        var _t=$(this);
        var login_email=trim($('#login_email').val());
        var login_pwd=$('#login_pwd').val();
        var remember=$('#login_remember').val();
        if($('#login_verify_input').size()>0){
            var login_captcha=$('#login_verify_input').val();
        }else{
            var login_captcha='';
        }
        login(login_email,login_pwd,remember,login_captcha,_t,$('#login_captcha'));
    });
    $('#login_submit').click(function(){
        var _t=$(this);
        var login_email=trim($('#pop_login_email').val());
        var login_pwd=$('#pop_login_pwd').val();
        var remember=$('#pop_login_remember').val();
        var login_captcha='';
        if($('#pop_login_code').size()>0){
            var login_captcha=$('#pop_login_code').val();
        }
        login(login_email,login_pwd,remember,login_captcha,_t,$('#pop_login_captcha'));
    });
    $('.login_modal').click(function(e){
        if(uid==''){
            $('.cover_table').addClass('pop_up_show');
            $('#content').addClass('gaussian_blur');
            $('#pop_up').hide();
            $('#pop_login .register_div').css({visibility:'visible',opacity:1});
            $('#pop_login_captcha').click();
            e.preventDefault();
            return false;
        }
    })
    $('.register_modal').click(function(e){
        if(uid==''){
            $('.cover_table').addClass('pop_up_show');
            $('#content').addClass('gaussian_blur');
            $('#pop_register .register_div').css({visibility:'visible',opacity:1});
            $('#pop_register_captcha').click();
            $('#pop_up').hide();
            e.preventDefault();
            return false;
        }
    })
    //log out action
    $('#logout').click(function(){
		if(confirm("是否退出本站？")){
			window.location.href=U('account/logout');
		}
    });
    //set pub textarea height
    $('#pub_content').css({height:height-270});
    //category container dropdown
    $('#pub_category').toggle(function(){
        $('#pub_category_container').slideDown(100);
    },function(){
        $('#pub_category_container').slideUp(100);
    });
    //when clicking category item
    $('.pub_category_item').click(function(){
        $('#pub_category').html($(this).html());
        var category_id=$(this).attr('id').replace('category','');
        $('#pub_category_id').val(category_id);
        $('#pub_category').click();
        $('#pub_content').focus();
    });
    //post thread action
    $('#pub_post').click(function(){
        var thread_title=trim($('#pub_title').val());
        var thread_category_id=$('#pub_category_id').val();
        var thread_content=trim($('#pub_content').val());
        var _t=$(this);
        if(thread_title==''){
            ZENG.msgbox.show('请输入帖子标题哟~', 3, 2000);
        }else if(thread_category_id==''){
            ZENG.msgbox.show('请选择帖子分类哟~', 3, 2000);
        }else if(thread_content.replace('[img]','').replace('[/img]')==''){
            ZENG.msgbox.show('请输入帖子具体内容哟~', 3, 2000);
        }else{
            if(!_t.hasClass('disabled')){
                _t.addClass('disabled');
                ZENG.msgbox.show('正在发布帖子...', 6, 120000);
                $.post(U('thread/postThread'),{thread_title:thread_title,thread_category_id:thread_category_id,thread_content:thread_content},function(callback){
                    if(callback.status){
                        delCookie(cookie_prefix+'user_info','/');
                        location.href=callback.msg;
                    }else{
                        ZENG.msgbox.show(callback.msg, 3, 2000);
                        _t.removeClass('disabled');
                    }
                },'json');
            }
        }
    });
    $('.del_thread').click(function(){
        if(confirm('确定删除此帖吗？')){
            var _t=$(this);
            $.post(U('thread/delThread'),{thread_id:_t.children('.tid').val()},function(callback){
                if(callback.status){
                    _t.parents('.wrapper').remove();
                }else{
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
            },'json');
        }
    });
    //set current textarea id for inserting text
    /*$('.pub_upload').click(function(){
        if($(this).attr('id')=='pub_upload'){
            cur_textarea_obj=document.getElementById('pub_content');
        }else{
            cur_textarea_obj=$(this).parent('div').siblings('textarea')[0];
        }
        pop_up('上传图片',500,360,'','#upload_img');
    });*/
    $('.img_form').change(function(){
        var _t=$(this);
        _t.hide();
        _t_textarea=_t.parent('div').prev('textarea');
        ZENG.msgbox.show('正在上传图片...', 6, 120000);
        $.ajax({
            url: 'http://up.tietuku.cn/',
            type: 'POST',
            cache: false,
            data: new FormData(_t[0]),
            processData: false,
            contentType: false
        }).done(function(res) {
            if(res.s_url!=''){
                ZENG.msgbox.show('插入图片成功', 4,2000);
                insertText(_t_textarea[0],res.s_url);
            }else{
                ZENG.msgbox.show('插入图片失败，请稍后重试', 3, 2000);
            }
            _t.show();
        }).fail(function(res) {
            ZENG.msgbox.show('上传失败，请稍后重试', 3, 2000);
            _t.show();
        });
    });
    $('#pop_close').click(function(){
        $('.cover_table').removeClass('pop_up_show');
        $('#content').removeClass('gaussian_blur');
        $('#pop_data>div').hide();
    });
    $('#post_new').click(function(){
        if(uid!=''){
            $('#pub_content').css({height:$(window).height()-340});
            $('#main_middle').fadeOut(150,function(){
                $('#pub_outer').fadeIn(200);
            });
        }
    })
    $('#pub_close').click(function(){
        $('#pub_outer').fadeOut(150,function(){
            $('#main_middle').fadeIn(200);
        })
    });
    var $pop_user_outer=$('#pop_user_outer');
    var is_mouseover=false;
    $pop_user_outer.hover(function(){
        is_mouseover=true;
        $pop_user_outer.css({visibility:'visible',opacity:1});
    },function(){
        is_mouseover=false;
        $pop_user_outer.css({visibility:'hidden',opacity:0});
    })
    $('.avatar_hover').lazyhover(function(){
        var _t=$(this);
        var avatar_uid=_t.attr('uid');
        if(avatar_uid==uid){
            return false;
        }
        var this_width=_t.width();
        var this_height=_t.height();
        $.post(U('user/avatarHover'),{uid:avatar_uid},function(callback){
            if(callback.status){
                var $pop_user_outer=$('#pop_user_outer');
                var left=_t.offset().left;
                var top=_t.offset().top;
                if(top-$(window).scrollTop()+250>$(window).height()){
                    top=top-255;
                }else{
                    top=top+this_height+10;
                }
                $pop_user_outer.html('\
                <div class="arrow_layer"></div>\
                <div id="pop_user">\
                    <img src="'+callback.user_info.big_avatar+'" class="pop_avatar_bg">\
                    <a class="pop_ava_container" href="'+callback.user_info.profile_url+'" target="_blank">\
                    <img src="'+callback.user_info.big_avatar+'" class="pop_ava">\
                    </a>\
                    <div class="pop_center">\
                    <div class="pop_center_name">'+callback.user_info.username+'</div>\
                    <div class="pop_center_info'+(uid==''?' avatar_unlogin':'')+'">\
                        '+callback.user_info.intro+'\
                    </div>'+(uid>0?'<div class="pop_center_active">'+callback.user_info.username+'共赞过我'+callback.user_info.vote_interaction+'次，回复过我'+callback.user_info.reply_interaction+'次</div>':'')+'</div>\
                <div class="pop_bo">\
                    <div class="pop_bo_top">\
                    <a class="pop_bo_wrapper" href="'+callback.user_info.thread_url+'" target="_blank">\
                    <div class="bo_wrapper_top">'+callback.user_info.thread_num+'</div>\
                    <div class="bo_wrapper_bo">帖 子</div>\
                </a>\
                <div class="pop_bo_devider"></div>\
                    <a class="pop_bo_wrapper" href="'+callback.user_info.reply_url+'" target="_blank">\
                    <div class="bo_wrapper_top">'+callback.user_info.reply_num+'</div>\
                    <div class="bo_wrapper_bo">回 复</div>\
                </a>\
                <div class="pop_bo_devider"></div>\
                    <a class="pop_bo_wrapper" href="'+callback.user_info.collect_url+'" target="_blank">\
                    <div class="bo_wrapper_top">'+callback.user_info.collect_num+'</div>\
                    <div class="bo_wrapper_bo">收 藏</div>\
                </a>\
                </div>\
                </div>\
                </div>');
                $pop_user_outer.css({left:left-145+this_width/2,top:top,visibility:'visible',opacity:1});
            }
        },'json');
    },function(){
        setTimeout(function(){
            !is_mouseover&&$pop_user_outer.css({visibility:'hidden',opacity:0});
            is_mouseover=false;
        },500);
    });
    $('#to_login').click(function(){
        $('#pop_register .register_div').css({visibility:'hidden',marginLeft:'+=25',opacity:0});
        $('#pop_login .register_div').css({visibility:'visible',marginLeft:'+=25',opacity:1});
        $('#pop_login_captcha').click();
    });
    $('#to_register').click(function(){
        $('#pop_login .register_div').css({visibility:'hidden',marginLeft:'-=25',opacity:0});
        $('#pop_register .register_div').css({visibility:'visible',marginLeft:'-=25',opacity:1});
        $('#pop_register_captcha').click();
    })
    $('.pop_account_close').click(function(){
        $('.cover_table').removeClass('pop_up_show');
        $('#content').removeClass('gaussian_blur');
        $('#pop_data>div').hide();
        $('.register_div').css({visibility:'hidden'});
        if(!$('.login_tab').hasClass('cur_tab')){
            $('#register_captcha').click();
        }else{
            $('#login_captcha').click();
        }
    });
    $('#search_sub').click(function(){
        var _t=$(this);
        if(_t.hasClass('clicked')){
            return false;
        }
        _t.addClass('clicked');
        var search_val=trim($('#search_input').val());
        if(search_val!=''){
            ZENG.msgbox.show('正在处理查询数据...',6,60000);
            $.post(U('thread/wordSegment'),{sentence:search_val},function(data){
                if(data.status){
                    location.href=data.url;
                }else{
                    ZENG.msgbox.show('网络繁忙，请稍后重试...',3,2000);
                }
                _t.removeClass('clicked');
            },'json')
        }
    });
    $('#search_input').keydown(function(e){
        if(e.keyCode==13){
            $('#search_sub').click();
        }
    });
    getCookie('loli')!=1&&$('#loli').removeClass('none');
});
function login_pop(){
    if(uid==''){
        $('.cover_table').addClass('pop_up_show');
        $('#content').addClass('gaussian_blur');
        $('#pop_up').hide();
        $('#pop_login .register_div').css({visibility:'visible',opacity:1});
        $('#pop_login_captcha').click();
        e.preventDefault();
        return false;
    }
}
function login(login_email,login_pwd,remember,captcha,_t,captcha_img){
    if(login_email.match( /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/)==null){
        ZENG.msgbox.show('邮箱地址貌似不正确哟~', 3, 2000);
    }else if(login_pwd==''){
        ZENG.msgbox.show('请输入密码哟~', 3, 2000);
    }else if(captcha_img.size()>0&&captcha==''){
        ZENG.msgbox.show('请输入验证码哟~', 3, 2000);
    }else{
        if(_t.hasClass('disabled')){
            return false;
        }
        if(captcha_img.size()>0){
            var code=captcha;
        }else{
            var code='';
        }
        _t.addClass('disabled');
        $.post(U('account/loginProcess'),{login_email:login_email,login_pwd:login_pwd,login_captcha:code,remember:remember},function(callback){
            if(callback.status){
                location.reload();
            }else{
                ZENG.msgbox.show(callback.msg, 3, 2000);
                captcha_img.click();
                _t.removeClass('disabled');
            }
        },'json');
    }
}
function register(reg_username,reg_email,reg_pwd,reg_verify,remember,register_captcha_img,_t){
    var _t=$(this);
    if(reg_username=='')
    {
        ZENG.msgbox.show('用户名不能为空呀~', 3, 2000);
    }else if(reg_username.length>20)
    {
        ZENG.msgbox.show('用户名不能超过20位哟~', 3, 2000);
    }else if(reg_email.match( /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/)==null)
    {
        ZENG.msgbox.show('邮箱地址貌似不正确哟~', 3, 2000);
    }else if(reg_pwd.length<6)
    {
        ZENG.msgbox.show('密码需不小于6位哟~',3, 2000);
    }else if(register_captcha_img.size()>0&&reg_verify.length!=4)
    {
        ZENG.msgbox.show('验证码是4位哟亲~',3, 2000);
    }else{
        if(_t.hasClass('disabled')){
            return false;
        }
        _t.addClass('disabled');
        $.post(U('account/registerProcess'),{username:reg_username,email:reg_email,pwd:reg_pwd,captcha:reg_verify,remember:remember},function(callback){
            if(callback.status){
                location.href=callback.url;
            }else{
                register_captcha_img.click();
                ZENG.msgbox.show(callback.msg, 3, 3000);
                _t.removeClass('disabled');
            }
        },'json');
    }
}
function pop_up(title,width,height,html,dom){
    $('#pop_title_detail').html(title);
    $('#pop_up').css({width:width,height:height});
    html!=''&&$('#pop_content').html(html);
    dom!=''&&$(dom).show().siblings('div').hide();
    $('.cover_table').addClass('pop_up_show');
    $('#content').addClass('gaussian_blur');
}
function trim(a) {
    return a.replace(/(^\s*)|(\s*$)/g, "");
}
function setCookie(name,value){
    var Days = 1000;
    var exp  = new Date();
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString()+"; path=/";
}
function getCookie(cookieName){
    var cookieObj={},
        cookieSplit=[],
        cookieArr=document.cookie.split(";");
    for(var i=0,len=cookieArr.length;i<len;i++)
        if(cookieArr[i]) {
            // 以等号（=）分组
            cookieSplit=cookieArr[i].split("=");
            // Trim() 是自定义的函数，用来删除字符串两边的空格
            cookieObj[trim(cookieSplit[0])]=trim(cookieSplit[1]);
        }
    return cookieObj[cookieName];
}
function delCookie( name,path,domain ) {
    document.cookie = name + "=" +
        ((path) ? ";path="+path:"")+
        ((domain)?";domain="+domain:"") +
        ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
}
function insertText(obj,str) {
    if (document.selection) {
        var sel = document.selection.createRange();
        sel.text = str;
    } else if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
        var startPos = obj.selectionStart,
            endPos = obj.selectionEnd,
            cursorPos = startPos,
            tmpStr = obj.value;
        obj.value = tmpStr.substring(0, startPos) + str + tmpStr.substring(endPos, tmpStr.length);
        //cursorPos += startPos;
        obj.selectionStart = obj.selectionEnd = cursorPos;
        return startPos;
    } else {
        obj.value += str;
    }
}
function U(url,params){
    var website = site+'/index.php';
    url = url.split('/');
    website = website+'?c='+url[0]+'&m='+url[1];
    if(params){
        params = params.join('&');
        website = website + '&' + params;
    }
    return website;
}
var textSelect = function(o, a, b){
    //o是当前对象，例如文本域对象
    //a是起始位置，b是终点位置
    var a = parseInt(a, 10), b = parseInt(b, 10);
    var l = o.value.length;
    if(l){
        //如果非数值，则表示从起始位置选择到结束位置
        if(!a){
            a = 0;
        }
        if(!b){
            b = l;
        }
        //如果值超过长度，则就是当前对象值的长度
        if(a > l){
            a = l;
        }
        if(b > l){
            b = l;
        }
        //如果为负值，则与长度值相加
        if(a < 0){
            a = l + a;
        }
        if(b < 0){
            b = l + b;
        }
        if(o.createTextRange){//IE浏览器
            var range = o.createTextRange();
            range.moveStart("character",-l);
            range.moveEnd("character",-l);
            range.moveStart("character", a);
            range.moveEnd("character",b);
            range.select();
        }else{
            o.setSelectionRange(a, b);
            o.focus();
        }
    }
};
$(document).keydown(function(e){e.ctrlKey&&e.which==68&&$('#loli').fadeOut(200)+setCookie('loli',1);});