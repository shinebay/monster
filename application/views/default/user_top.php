<div id="user_top" class="shadow">
    <div id="user_basic">
        <img src="<?php echo isset($isProfile)?get_avatar($_COOKIE[$this->config['cookie_prefix'].'avatar_big'],'big'):get_avatar($user_info['big_avatar'],'big'); ?>" class="user_avatar r"/>
        <div id="user_info">
            <div class="user_info_name"><?php echo $user_info['username']; ?></div>
            <div class="user_info_site">
                <i class="icon icon-home"></i>个人网址：
                <?php if($user_info['website']!=''): ?>
                <a rel="nofollow" href="<?php echo $user_info['website']; ?>" target="_blank"><?php echo $user_info['website']; ?></a>
                <?php else: ?>
                <a>暂无网址</a>
                <?php endif; ?>
            </div>
            <div class="user_info_slogan">
                <i class="icon icon-user"></i>
                <?php echo $user_info['intro']!=''?$user_info['intro']:'暂无个人介绍'; ?>
            </div>
        </div>
        <div class="user_history">
            <div class="user_history_num"><?php echo $user_info['thread_num']; ?></div>
            <div class="user_history_name">帖子</div>
        </div>
        <div class="user_history">
            <div class="user_history_num"><?php echo $user_info['reply_num']; ?></div>
            <div class="user_history_name">回复</div>
        </div>
        <div class="user_history">
            <div class="user_history_num"><?php echo $user_info['collect_num']; ?></div>
            <div class="user_history_name">收藏</div>
        </div>
    </div>
    <div class="clear"></div>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#del_user').click(function(){
                if(confirm('确定冻结此用户吗？冻结后该账户将不能再登录')){
                    $.post(U('user/freezeUser'),{uid:$(this).children('input').val()},function(callback){
                        if(callback.status){
                            ZENG.msgbox.show('冻结该账号成功',4,2000);
                        }else{
                            ZENG.msgbox.show(callback.msg,3,2000);
                        }
                    },'json')
                }
            })
        });
    </script>
    <div id="user_nav">
        <?php if(isset($isProfile)):?><a class="user_nav_link<?php if($user_cur=='notifications'): ?> user_cur<?php endif; ?>" href="<?php echo url('profile/notifications',array('last_id'=>0,'direction'=>1)); ?>">通知</a>
        <a class="user_nav_link<?php if($user_cur=='msg'): ?> user_cur<?php endif; ?>" href="<?php echo url('profile/msg'); ?>">私信</a><?php endif; ?>
        <a class="user_nav_link<?php if($user_cur=='thread'): ?> user_cur<?php endif; ?>" href="<?php echo isset($isProfile)?url('profile/thread',array('last_id'=>0,'direction'=>1)):url('user/thread',array('uid'=>$user_info['uid'],'last_id'=>0,'direction'=>1)); ?>">帖子</a>
        <a class="user_nav_link<?php if($user_cur=='reply'): ?> user_cur<?php endif; ?>" href="<?php echo isset($isProfile)?url('profile/reply',array('last_id'=>0,'direction'=>1)):url('user/reply',array('uid'=>$user_info['uid'],'last_id'=>0,'direction'=>1)); ?>">回复</a>
        <a class="user_nav_link<?php if($user_cur=='collect'): ?> user_cur<?php endif; ?>" href="<?php echo isset($isProfile)?url('profile/collect',array('last_id'=>0,'direction'=>1)):url('user/collect',array('uid'=>$user_info['uid'],'last_id'=>0,'direction'=>1)); ?>">收藏</a>
        <?php if(isset($isProfile)):?><a class="user_nav_link<?php if($user_cur=='setting'): ?> user_cur<?php endif; ?>" href="<?php echo url('profile/setting'); ?>">设置</a>
        <a class="user_nav_link<?php if($user_cur=='avatar'): ?> user_cur<?php endif; ?>" href="<?php echo url('profile/avatar'); ?>">头像</a><?php else: ?>
        <a class="user_nav_link<?php if($user_cur=='info'): ?> user_cur<?php endif; ?>" href="<?php echo url('user/index',array('uid'=>$user_info['uid'])); ?>">资料</a><?php endif; ?>
        <a href="<?php echo url('profile/msg'); ?>#<?php echo !isset($isProfile)?$user_info['username']:''; ?>" id="send_msg"><i class="icon icon-plus"></i>私信</a>
        <?php if(!isset($isProfile)&&$this->uid==1):?><a class="user_nav_link" id="del_user"><input type="hidden" value="<?php echo $user_info['uid']; ?>"/>冻结账号</a><?php endif;?>
    </div>
</div>