<?php require('header.php'); ?>
    <div id="main_middle">
        <div id="user">
            <?php require('user_top.php'); ?>
            <div id="user_setting" class="shadow">
                <div class="user_setting_wrapper"><div class="setting_name">个人介绍：</div><div class="user_info_wrapper"><?php echo $user_info['intro']!=''?$user_info['intro']:'该用户暂无自我介绍'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">个人网址：</div><div class="user_info_wrapper"><?php echo $user_info['website']!=''?$user_info['website']:'该用户暂未添加个人网址'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">个人微博：</div><div class="user_info_wrapper"><?php echo $user_info['weibo']!=''?$user_info['weibo']:'该用户暂未添加个人微博'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">Dribbble：</div><div class="user_info_wrapper"><?php echo $user_info['dribbble']!=''?$user_info['dribbble']:'该用户暂未添加个人dribbble地址'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">GitHub：</div><div class="user_info_wrapper"><?php echo $user_info['github']!=''?$user_info['github']:'该用户暂未添加个人GitHub地址'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">Facebook：</div><div class="user_info_wrapper"><?php echo $user_info['facebook']!=''?$user_info['facebook']:'该用户暂未添加个人Facebook地址'; ?></div></div>
                <div class="user_setting_wrapper"><div class="setting_name">Twitter：</div><div class="user_info_wrapper"><?php echo $user_info['twitter']!=''?$user_info['twitter']:'该用户暂未添加个人Twitter地址'; ?></div></div>
                <?php if($this->uid!=$user_info['uid']): ?>
                    <div class="user_setting_wrapper"><div class="setting_name" style="width: auto"><?php echo $user_info['username']; ?>共回复过我次数：</div><div class="user_info_wrapper"><?php echo !$this->uid?'尚未登录，请登录后再查看统计次数':$reply_interaction; ?> 次</div></div>
                    <div class="user_setting_wrapper"><div class="setting_name" style="width: auto"><?php echo $user_info['username']; ?>共赞过我次数：</div><div class="user_info_wrapper"><?php echo !$this->uid?'尚未登录，请登录后再查看统计次数':$vote_interaction; ?> 次</div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php require('footer.php'); ?>