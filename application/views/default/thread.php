<?php require('header.php'); ?>
<link rel="stylesheet" href="http://cdn.staticfile.org/fancybox/2.1.5/jquery.fancybox.min.css" type="text/css" media="screen" />
<script type="text/javascript" src="http://cdn.staticfile.org/fancybox/2.1.5/jquery.fancybox.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("a.img_link").fancybox({
            padding: 0,
            helpers: {
                overlay: {
                  locked: false
                }
            }
        });
        $('.reply_info_comment').live('click',function(){
            var $textarea_container=$(this).parent('.reply_info').siblings('.textarea_container');
            $textarea_container.is(':visible')?$textarea_container.slideUp(400):$textarea_container.slideDown(400);
        });
        $('.reply_area,#reply_textarea').focus(function() {
            if(uid==''){
                $(this).blur();
                $('.login_modal').click();
                return false;
            }
        });
        $('.reply_btn').click(function(){
            login_pop();
            var _t=$(this);
            var content=trim(_t.siblings('textarea').val());
            var thread_id=$('#thread_id').val();
            if(thread_id>0&&content!=''){
                if(_t.hasClass('disabled')){
                    return false;
                }
                _t.addClass('disabled');
                $.post(U('thread/postReply'),{thread_id:thread_id,content:content,parent_id:_t.children('input').val()},function(callback){
                    ZENG.msgbox.show('正在提交回复数据，请稍后',6,600000);
                    if(callback.status){
                        delCookie(cookie_prefix+'user_info','/');
                        location.reload();
                    }else{
                        ZENG.msgbox.show(callback.msg,3,2500);
                        _t.removeClass('disabled');
                    }
                },'json');
            }
        });
        $('#collect').click(function(){
            login_pop();
            var _t=$(this);
            if(_t.hasClass('disabled')){
                return false;
            }
            _t.addClass('disabled');
            if(_t.html()=='添加收藏'){
                _t.html('取消收藏');
                var collect_num=parseInt($('#collect_num').html());
                $('#collect_num').html(collect_num+1);
                $.post(U('thread/collect'),{thread_id:$('#thread_id').val(),type:0},function(callback){
                    if(!callback.status){
                        ZENG.msgbox.show(callback.msg,3,2500);
                        _t.html('添加收藏');
                        $('#collect_num').html(collect_num);
                    }
                    delCookie(cookie_prefix+'user_info','/');
                    _t.removeClass('disabled');
                },'json');
            }else{
                _t.html('添加收藏');
                var collect_num=parseInt($('#collect_num').html());
                $('#collect_num').html(collect_num-1);
                $.post(U('thread/collect'),{thread_id:$('#thread_id').val(),type:1},function(callback){
                    if(!callback.status){
                        ZENG.msgbox.show(callback.msg,3,2500);
                        _t.html('取消收藏');
                        $('#collect_num').html(collect_num);
                    }
                    delCookie(cookie_prefix+'user_info','/');
                    _t.removeClass('disabled');
                },'json')
            }
        });

        $('.vote_btn').click(function(){
            login_pop();
            if(parseInt($('#thread_uid').val())==uid){
                ZENG.msgbox.show('不能给自己投票哟~', 3, 2000);
                return false;
            }
            var _t=$(this);
            var vote_type=1;
            if(_t.hasClass('downvote')){
                vote_type=2;
            }
            if(_t.hasClass('vote'+vote_type) || _t.siblings('.vote_btn').hasClass('vote'+(3-vote_type))){
                return false;
            }
            _t.addClass('vote'+vote_type);
            var $thread_id=_t.siblings('.thread_id');
            var $votenum=_t.siblings('.votenum');
            var thread_id=$thread_id.val();
            var votenum=parseInt($votenum.html());
            $.post(U('thread/threadVote'),{thread_id:thread_id,vote_type:vote_type},function(callback){
                if(callback.status){
                        if(vote_type==1){
                            $votenum.html(votenum+1);
                        }else{
                            $votenum.html(votenum-1);
                        }
                    }else{
                        _t.removeClass('vote'+vote_type);
                        ZENG.msgbox.show(callback.msg, 3, 2000);
                    }
                },'json');
        });
        $('.reply_vote').click(function() {
            login_pop();
            var _t=$(this);
            if(_t.hasClass('tipsy_south')){
                ZENG.msgbox.show(_t.attr('original-title'),3,2000);
                return false;
            }
            if(_t.siblings('.reply_uid').val()==uid){
                ZENG.msgbox.show('不能给自己投票哟~',3,2000);
                return false;
            }
            if(_t.hasClass('disabled')){
                return false;
            }
            if(_t.hasClass('reply_vote1')){
                var type=1;
                var replyclass=trim(_t.attr('class').replace('reply_vote1','').replace('reply_vote','').replace('disabled',''));
            }else{
                var type=2;
                var replyclass=trim(_t.attr('class').replace('reply_vote2','').replace('reply_vote','').replace('disabled',''));
            }
            $('.'+replyclass).each(function(){
                var $t=$(this);
                $t.hasClass('reply_vote'+type)&&$t.find('a').html(parseInt($t.find('a').html())+1);
            });
            $.post(U('thread/replyVote'),{reply_id:_t.siblings('.reply_id').val(),type:type},function(callback){
                if(!callback.status){
                    if(callback.has_vote!=''){
                        var arr=['赞','踩'];
                        $('.'+replyclass).addClass('disabled').addClass('tipsy_south').attr('title','您已经'+arr[callback.has_vote-1]+'过该回复啦~');
                    }
                    $('.'+replyclass).removeClass('disabled');
                    $('.'+replyclass).each(function(){
                        var $t=$(this);
                        $t.hasClass('reply_vote'+type)&&$t.find('a').html(parseInt($t.find('a').html())-1);
                    });
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
            },'json');
        });
        $('.del_reply').click(function(){
            var _t=$(this);
            if(confirm('确定删除此回复吗？')){
                $.post(U('thread/delReply'),{reply_id:_t.siblings('.reply_id').val()},function(callback){
                    if(callback.status){
                        _t.parents('.reply_info').prev('.reply_detail').html('<i>此回复因不友善内容被删除</i>');
                    }else{
                        ZENG.msgbox.show(callback.msg,3,2000);
                    }
                },'json')
            }
        });
    })
</script>
        <div id="main_middle">
            <div class="wrapper shadow" style="margin-bottom: 0">
                <div class="title">
                    <a href="<?php echo url('user/index',array('uid'=>$thread['uid'])); ?>" class="title_avatar">
                        <img src="<?php echo $avatar; ?>">
                    </a>
                    <a class="title_content">
                        <?php echo $thread['thread_title']; ?>
                    </a>
                    <div class="clear"></div>
                </div>
                <div class="description">
                    <?php echo nl2br(replace_link($thread['thread_content']));?>
                </div>
                <div class="bottom">
                    <div class="bottom_list">
                        <a class="pub_time"><?php echo $thread['username']; ?> 发表于<?php echo friendlyDate($thread['ctime']); ?></a>
                        <input type="hidden" id="thread_uid" value="<?php echo $thread['uid']; ?>"/>
                        <i class="devider">&bull;</i>
                        <span class="bottominfo">浏览(<?php echo $thread['view_num']; ?>)</span><i class="devider">&bull;</i>
                        <span class="bottominfo">回复(<?php echo $thread['reply_num']; ?>)</span><i class="devider">&bull;</i>
                        <span class="bottominfo"><a id="collect"><?php echo $is_collect?'取消收藏':'添加收藏'; ?></a>(<a id="collect_num"><?php echo $thread['collect_num']; ?></a>)</span>
                        <div class="vote">
                            <div class="downvote vote_btn<?php if($vote==2): ?> vote2<?php endif; ?>"></div>
                            <div class="votenum"><?php echo $thread['vote_num']; ?></div>
                            <input type="hidden" class="thread_id" id="thread_id" value="<?php echo $thread['thread_id']; ?>"/>
                            <div class="upvote vote_btn<?php if($vote==1): ?> vote1<?php endif; ?>"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="reply">
                <?php if($thread['reply_num']==0): ?>
                <div class="reply_wrapper"></div>
                <?php else: foreach($reply as $k=>$v): ?>
                <div class="reply_wrapper">
                    <a href="<?php echo url('user/index',array('uid'=>$v['reply_user_info']['uid'])); ?>" class="reply_avatar">
                        <img src="<?php echo get_avatar($v['reply_user_info']['small_avatar'],'small'); ?>" class="avatar_hover" uid="<?php echo $v['reply_user_info']['uid']; ?>">
                    </a>
                    <div class="circle<?php if($v['reply_user_info']['uid']==$thread['uid']): ?> blue tipsy_south" title="楼主回复" <?php else: ?>"<?php endif; ?>></div>
                    <div class="reply_inner">
                        <div class="reply_top"></div>
                        <div class="reply_mid">
                            <p class="reply_detail">
                                <?php echo nl2br(replace_link($v['content'])); ?>
                            </p>
                            <div class="reply_info">
                                <span class="reply_info_name">
                                    <i class="icon icon-user"></i>
                                    <a href="<?php echo url('user/index',array('uid'=>$v['reply_user_info']['uid'])); ?>" class="avatar_hover" uid="<?php echo $v['reply_user_info']['uid']; ?>"><?php echo $v['reply_user_info']['username']; ?></a> 回复于<?php echo friendlyDate($v['ctime']); ?>
                                </span>
                                <span class="reply_info_devider">&bull;</span>
                                <span class="reply_vote1 reply_vote voteclass_<?php echo $k; ?>" title="亮了">
                                    <i class="icon icon-agree"></i>
                                    <a><?php echo $v['upvote_num']; ?></a>
                                </span>
                                <span class="reply_info_devider">&bull;</span>
                                <input type="hidden" value="<?php echo $k; ?>" class="reply_id"/>
                                <input type="hidden" value="<?php echo $v['uid']; ?>" class="reply_uid"/>
                                <span class="reply_vote2 reply_vote voteclass_<?php echo $k; ?>" title="呵呵">
                                    <i class="icon icon-disagree"></i>
                                    <a><?php echo $v['downvote_num']; ?></a>
                                </span>
                                <?php if($this->uid==1): ?>
                                <span class="del_reply">
                                    <a class="reply_info_devider">&bull;</a>
                                    删除
                                </span>
                                <?php endif; ?>
                                <span class="reply_info_comment">
                                    <i class="icon icon-comment"></i>
                                    回复
                                </span>
                            </div>
                            <div class="textarea_container">
                                <textarea class="reply_area" placeholder="这里输入回复 <?php echo $v['reply_user_info']['username']; ?> 的内容"></textarea>
                                <div class="reply_opt">
                                    <i class="icon icon-image tipsy_south pub_upload"></i>
                                    <form class="img_form tipsy_south" title="在光标处插入图片">
                                        <input name="Token" value="<?php echo $this->config['tietuku_token']; ?>" type="hidden">
                                        <input type="file" name="file" class="img_input" accept="image/*"/>
                                    </form>
                                </div>
                                <div class="reply_btn">回 复<input type="hidden" value="<?php echo $k; ?>"/></div>
                            </div>
                            <?php if($v['parent_reply_info']!=''): foreach($v['parent_reply_info'] as $k1=>$v1):?>
                                <div class="child_comment">
                                    <span class="child_comment_top"><i></i></span>
                                    <div class="child_content">
                                        <p class="reply_detail">
                                            <?php echo nl2br(replace_link($v1['content'])); ?>
                                        </p>
                                        <div class="reply_info">
                                        <span class="reply_info_name">
                                            <i class="icon icon-user"></i>
                                            <a class="avatar_hover" uid="<?php echo $v1['uid']; ?>" href="<?php echo url('user/index',array('uid'=>$v1['uid'])); ?>"><?php echo $v1['username']; ?></a> 回复于<?php echo friendlyDate($v1['ctime']) ?>
                                        </span>
                                        <span class="reply_info_devider">&bull;</span>
                                        <span class="reply_vote1 reply_vote voteclass_<?php echo $k1; ?>" title="亮了">
                                            <i class="icon icon-agree"></i>
                                            <a><?php echo $v1['upvote_num'];?></a>
                                        </span>
                                        <input type="hidden" value="<?php echo $k1; ?>" class="reply_id"/>
                                        <input type="hidden" value="<?php echo $v1['uid']; ?>" class="reply_uid"/>
                                        <span class="reply_info_devider">&bull;</span>
                                        <span class="reply_vote2 reply_vote voteclass_<?php echo $k1; ?>" title="呵呵">
                                            <i class="icon icon-disagree"></i>
                                            <a><?php echo $v1['downvote_num'];?></a>
                                        </span>
                                        <?php if($this->uid==1): ?>
                                        <span class="del_reply">
                                            <a class="reply_info_devider">&bull;</a>
                                            删除
                                        </span>
                                        <?php endif; ?>
                                        <span class="reply_info_comment">
                                            <i class="icon icon-comment"></i>
                                            回复
                                        </span>
                                        </div>
                                        <div class="textarea_container">
                                            <textarea class="reply_area" placeholder="这里输入回复 <?php echo $v1['username']; ?> 的内容"></textarea>
                                            <div class="reply_opt">
                                                <i class="icon icon-image tipsy_south pub_upload"></i>
                                                <form class="img_form tipsy_south" title="在光标处插入图片">
                                                    <input name="Token" value="<?php echo $this->config['tietuku_token']; ?>" type="hidden">
                                                    <input type="file" name="file" class="img_input" accept="image/*"/>
                                                </form>
                                            </div>
                                            <div class="reply_btn">回 复<input type="hidden" value="<?php echo $k1; ?>"/></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <div class="reply_bottom"></div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
                <div class="reply_wrapper">
                    <?php if($this->uid): ?>
                    <a href="<?php echo url('profile/setting'); ?>" class="reply_avatar">
                        <img src="<?php echo get_avatar($_COOKIE[$this->config['cookie_prefix'].'avatar_small'],'small'); ?>">
                    </a>
                    <?php endif; ?>
                    <div class="circle"></div>
                    <div class="reply_inner">
                        <div class="reply_top"></div>
                        <div class="reply_mid">
                            <textarea id="reply_textarea" placeholder="<?php if($thread['reply_num']==0): ?>你忍心看到该帖子已经<?php echo str_replace('前','',friendlyDate($thread['ctime'])); ?>了都没人回复吗？<?php else: ?>在这里我们友好互助，请礼貌回复帖子，并确保自己的回答对楼主有所帮助<?php endif; ?>"></textarea>
                            <div class="reply_opt">
                                <i class="icon icon-image tipsy_south pub_upload" title="在光标处插入图片"></i>
                                <form class="img_form tipsy_south" title="在光标处插入图片">
                                    <input name="Token" value="<?php echo $this->config['tietuku_token']; ?>" type="hidden">
                                    <input type="file" name="file" class="img_input" accept="image/*"/>
                                </form>
                            </div>
                            <div class="reply_btn">回 复<input type="hidden" value=""/></div>
                        </div>
                        <div class="reply_bottom"></div>
                    </div>
                </div>
            </div>
            <div class="reply_pagination">
                <?php require('page_btn.php') ?>
            </div>
        </div>
<?php require('footer.php'); ?>