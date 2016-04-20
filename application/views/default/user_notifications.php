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
        $('.notice_del').click(function(){
            var _t=$(this);
            if(_t.hasClass('disabled')){
                return false;
            }
            _t.addClass('disabled');
            $.post(U('profile/removeNotification'),{notifiction_id:_t.find('.notice_id').val()},function(callback){
                if(callback.status){
                    _t.parents('.wrapper').remove();
                }else{
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
                _t.removeClass('disabled');
            },'json');
        });
        $('#clear_all').click(function(){
            if(confirm('确定清空所有通知信息吗？'))
            var _t=$(this);
            if(_t.hasClass('disabled')){
                return false;
            }
            _t.addClass('disabled');
            ZENG.msgbox.show('正在清空所有通知信息', 6, 120000);
            $.post(U('profile/clearNotifications'),function(callback){
                if(callback.status){
                    location.reload();
                }else{
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
            },'json')
        })
    });
</script>
    <div id="main_middle">
        <div id="user">
            <?php require('user_top.php'); if(count($notifications)>0):?>
            <span id="clear_all" class="wrapper shadow">清除所有通知信息</span>
            <?php foreach($notifications as $k=>$v): ?>
                <div class="wrapper shadow">
                    <div class="notice_header">
                        <span class="read_state tipsy_north<?php if($v['status']==0): ?> unread" title="此消息未读"<?php else: ?>" title="此消息已读"<?php endif; ?>>&bull;</span>
                        <a href="<?php echo url('user/index',array('uid'=>$v['from_uid'])); ?>" class="title_avatar">
                            <img src="<?php echo get_avatar($v['from_uid_avatar']); ?>"/>
                        </a>
                        <a class="notice_title" href="<?php echo url('thread/setstate',array('key'=>$k,'url'=>url('thread/view',array('category_name'=>$this->config['category'][$v['thread_info']['category_id']],'thread_title'=>remove_puntuation($v['thread_info']['thread_title']),'thread_id'=>$v['thread_info']['thread_id'],'last_id'=>0,'direction'=>1)))); ?>">
                            <?php echo $v['msg']; ?>：
                        </a>
                        <div class="notice_del">
                            <input type="hidden" value="<?php echo $k; ?>" class="notice_id"/>
                            <span class="notice_del_inner"></span>
                        </div>
                        <div class="notice_time"><?php echo date('Y-m-d H:i',$v['ctime']); ?></div>
                        <div class="clear"></div>
                    </div>
                    <?php if($v['from_reply_id']!=''): ?>
                    <div class="description notice_description">
                        <a href="<?php echo url('user/index',array('uid'=>$v['from_uid'])); ?>" class="notice_user"><?php echo $v['from_username']; ?>回复您：</a>
                        <?php echo replace_link($v['from_reply_info']['content']); ?>
                    </div>
                    <?php endif;if($v['to_reply_id']!=''): ?>
                    <div class="bottom notice_bottom">
                        <div class="ori_reply text_shadow">
                            <a class="notice_user">您对帖子的回复：</a>
                            <?php echo replace_link($v['to_reply_info']['content']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="ori_thread">
                        <span class="child_comment_top"><i></i></span>
                        <div class="wrapper text_shadow">
                            <div class="title">
                                <a href="<?php echo url('user/index',array('uid'=>$v['thread_info']['uid'])); ?>" class="title_avatar avatar_hover">
                                    <img src="<?php echo $v['thread_info']['user_avatar']; ?>"/>
                                </a>
                                <a class="title_content" href="<?php echo url('thread/setstate',array('key'=>$k,'url'=>url('thread/view',array('category_name'=>$this->config['category'][$v['thread_info']['category_id']],'thread_title'=>remove_puntuation($v['thread_info']['thread_title']),'thread_id'=>$v['thread_info']['thread_id'],'last_id'=>0,'direction'=>1)))); ?>">
                                    <?php echo $v['thread_info']['thread_title']; ?>
                                </a>
                                <div class="clear"></div>
                            </div>
                            <div class="description">
                                <?php echo replace_link($v['thread_info']['thread_content']); ?>
                            </div>
                            <div class="bottom">
                                <div class="bottom_list">
                                    <span class="bottominfo">浏览(<?php echo $v['thread_info']['view_num']; ?>)</span><i class="devider">•</i>
                                    <span class="bottominfo">回复(<?php echo $v['thread_info']['reply_num']; ?>)</span><i class="devider">•</i>
                                    <span class="bottominfo">投票(<?php echo $v['thread_info']['vote_num']; ?>)</span><i class="devider">•</i>
                                    <span class="bottominfo">收藏(<?php echo $v['thread_info']['collect_num']; ?>)</span>
                                    <a class="list_pub_time"><?php echo $v['thread_info']['username']; ?> 发表于<?php echo friendlyDate($v['thread_info']['ctime']); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
                require('page_btn.php');
                else:
            ?>
            <div class="wrapper shadow" id="zero" align="center">
                <div id="zero_inner"></div>
                暂无新通知
            </div>
            <?php endif;?>
        </div>
    </div>
<?php require('footer.php'); ?>