<div id="pub_outer" class="shadow">
    <div id="pub_area">
        <div class="pub_header">
            <input type="text" id="pub_title" placeholder="在此输入帖子标题，请控制字数在100字以内"/>
            <div id="pub_close" class="tipsy_south" title="关闭帖子发布页面"></div>
        </div>
        <div id="pub_category">选择该帖子分类</div>
        <?php if($this->config['category']!=''): ?>
            <div id="pub_category_container">
                <?php foreach($this->config['category'] as $k=>$v):?>
                    <div class="pub_category_item" id="category<?php echo $k; ?>"><?php echo $v; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif;?>
        <input type="hidden" id="pub_category_id" value=""/>
        <textarea id="pub_content" placeholder="在此输入帖子详细内容"></textarea>
        <div id="pub_bottom_left">
            <i class="icon icon-image tipsy_south pub_upload" title="在光标处插入图片" id="pub_upload"></i>
            <form class="img_form tipsy_south" title="在光标处插入图片">
                <input name="Token" value="<?php echo $this->config['tietuku_token']; ?>" type="hidden">
                <input type="file" name="file" class="img_input" accept="image/*"/>
            </form>
        </div>
        <div id="pub_post">发 布</div>
    </div>
</div>
<div id="main_right">
    <?php if(!$this->uid): ?>
        <div id="login_panel" class="shadow">
            <div id="account_tab">
                <div class="login_tab cur_tab">登录</div>
                <div class="register_tab">注册</div>
            </div>
            <div class="clear"></div>
            <div class="login_input_container" id="login">
                <div class="login_input_outer">
                    <i class="icon icon-inbox"></i>
                    <input type="text" id="login_email" placeholder="用户邮箱"/>
                </div>
                <div class="login_input_outer">
                    <i class="icon icon-lock"></i>
                    <input type="password" id="login_pwd" placeholder="用户密码"/>
                            <span class="remember tipsy_south save_pwd" title="是否记住密码">
                                <input class="remember_switch" type="checkbox"/>
                                <input type="hidden" value="0" id="login_remember"/>
                            </span>
                </div>
                <?php if($this->config['show_login_captcha']): ?>
                    <div class="login_input_outer">
                        <i class="icon icon-image"></i>
                        <input type="text" id="login_verify_input" placeholder="验证码" />
                        <img src="<?php echo $this->config['site'];?>/application/securimage/securimage_show.php" class="captcha tipsy_south" id="login_captcha" title="点击刷新该验证码" onclick="this.src='<?php echo $this->config['site'];?>/application/securimage/securimage_show.php?'+Math.random()" />
                    </div>
                <?php endif; ?>
                <div class="account_sub login_sub">登 录</div>
            </div>

            <div class="login_input_container none" id="register">
                <div class="login_input_outer">
                    <i class="icon icon-user"></i>
                    <input type="text" id="register_username" placeholder="用户昵称" maxlength="20"/>
                </div>
                <div class="login_input_outer">
                    <i class="icon icon-inbox"></i>
                    <input type="text" id="register_email" placeholder="用户邮箱"maxlength="100"/>
                </div>
                <div class="login_input_outer">
                    <i class="icon icon-lock"></i>
                    <input type="password" id="register_pwd" placeholder="用户密码" maxlength="30"/>
                            <span class="remember tipsy_south save_pwd" title="是否记住密码">
                                <input class="remember_switch" type="checkbox"/>
                                <input type="hidden" value="0" id="register_remember"/>
                            </span>
                </div>
                <?php if($this->config['show_register_captcha']): ?>
                    <div class="login_input_outer">
                        <i class="icon icon-image"></i>
                        <input type="text" id="register_verify_input" placeholder="验证码" maxlength="5" />
                        <img src="<?php echo $this->config['site'];?>/application/securimage/securimage_show.php" class="captcha tipsy_south" id="register_captcha" title="点击刷新该验证码" onclick="this.src='<?php echo $this->config['site'];?>/application/securimage/securimage_show.php?'+Math.random()" />
                    </div>
                <?php endif;?>
                <div class="account_sub register_sub">注 册</div>
            </div>
        </div>
    <?php else: ?>
        <div id="left_user" class="shadow" align="center">
            <div id="avatar_bg">
                <img src="<?php echo get_avatar($_COOKIE[$this->config['cookie_prefix'].'avatar_big'],'big'); ?>" id="avatar_left"/>
                <a id="avatar_upload" href="<?php echo url('profile/avatar'); ?>" class="avatar_upload_js">更改头像</a>
            </div>
            <div id="left_info">
                <?php $u_info=explode('_',$_COOKIE[$this->config['cookie_prefix'].'user_info']); ?>
                <a href="<?php echo url('profile/thread',array('last_id'=>0,'direction'=>1)); ?>">帖子(<span id="my_thread_num"><?php echo $u_info[0]; ?></span>)</a>
                <a href="<?php echo url('profile/reply',array('last_id'=>0,'direction'=>1)); ?>">回复(<span id="my_reply_num"><?php echo $u_info[1];?></span>)</a>
                <a href="<?php echo url('profile/collect',array('last_id'=>0,'direction'=>1)); ?>">收藏(<span id="my_collect_num"><?php echo $u_info[2];?></span>)</a>
            </div>
        </div>
    <?php endif; ?>
    <div class="clear"></div>
    <div id="post_new" class="rbtn login_modal unselect">发布新帖</div>
    <?php if($this->config['category']!=''): ?>
        <div id="category" class="shadow">
            <?php foreach($this->config['category'] as $k=>$v): ?>
                <a href="<?php echo url('main/category',array('category_id'=>$k,'last_id'=>0,'direction'=>1)); ?>" class="category_wrapper<?php if($category==$v): ?> category_cur<?php endif; ?>"><?php echo $v; ?></a>
            <?php endforeach;?>
        </div>
    <?php endif;?>
    <div class="shadow right_aside">
        <div class="aside_title">关于爱码士</div>
        <p>
            爱码士是国内第一个只为极客和爱代码人士盖楼交流的社区，也是第一个采用nosql+php编写并开源的社区，希望这个社区不要贴吧化，而是一个独特的小众纯净论坛，这里我们交流技术，分享经验，探讨硬件，咨询算法，吐槽职场，大家能友爱互助，不做伸手党，不鄙视小白，一起闲聊，一起进步
        </p>
    </div>
    <div class="shadow right_aside none" id="loli">
        <div class="aside_title">按Ctrl+d隐藏下方萝莉</div>
        <img src="http://i3.tietuku.cn/acdcfaad6aeffbeb.jpg"/>
    </div>
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
<div id="footer">
    <div id="footer_inner">
        <span>Based on</span>
        <a href="http://ssdb.io/zh_cn/" rel="nofollow" target="_blank">SSDB</a>
        <span>&bull;</span>
        <span>Images hosted on</span>
        <a href="http://tietuku.cn" rel="nofollow" target="_blank">tietuku.cn</a>
        <span>&bull;</span>
        <span>
            &copy;2016 2ma4.com All Rights Reserved.
        </span>
        <span id="hosthatch" class="right">
            Website is hosted on <a href="https://portal.hosthatch.com/aff.php?aff=595" target="_blank" rel="nofollow">hosthatch.com</a> in Hong Kong node
            <a href="https://portal.hosthatch.com/aff.php?aff=595" target="_blank" rel="nofollow"><img src="http://i13.tietuku.cn/9c343a37a67b4bff.png"/></a>
        </span>
    </div>
</div>
<table class="cover_table">
    <tr>
        <td align="center">
            <?php if($this->uid==''): ?>
            <div id="pop_account">
                <div id="pop_register">
                    <div class="register_div">
                        <div class="register_div_top">
                            <div class="account_header">注册本站<span class="pop_account_close">&times;</span></div>
                            <input type="text" id="pop_register_username" maxlength="20" class="account_input" placeholder="用户名">
                        </div>
                        <div class="register_div_mid">
                            <div class="input_wrapper">
                                <input type="text" id="pop_register_email" class="account_input" name="email" style="margin-left:10px" placeholder="邮箱">
                            </div>
                            <div class="input_code">
                                <input type="text" id="pop_register_verify" class="account_code focus" name="code" style="margin-left:10px" placeholder="验证码">
                                <img id="pop_register_captcha" src="<?php echo $this->config['site'];?>/application/securimage/securimage_show.php"  title="点击刷新该验证码" class="tipsy_south pop_code" onclick="this.src='<?php echo $this->config['site'];?>/application/securimage/securimage_show.php?'+Math.random()">
                            </div>
                        </div>
                        <div class="register_div_bottom">
                            <input type="password" name="pwd" class="account_pwd" placeholder="密码" id="pop_register_pwd">
                            <span class="pop_remember tipsy_south save_pwd" title="是否记住密码">
                                <input class="remember_switch" type="checkbox"/>
                                <input type="hidden" value="0" id="pop_register_remember"/>
                            </span>
                            <div class="clear"></div>
                            <div class="pop_switch" id="to_login">已有账号？点此登录</div>
                            <div class="submit" id="register_submit">注 册</div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div id="pop_login">
                    <div class="register_div">
                        <div class="register_div_top">
                            <div class="account_header">登录本站<span class="pop_account_close">&times;</span></div>
                            <input type="text" id="pop_login_email" maxlength="25" class="account_input" placeholder="用户邮箱">
                        </div>
                        <?php if($this->config['show_login_captcha']): ?>
                            <div class="register_div_mid">
                                <div class="input_code" style="border-top: 0px;">
                                    <input type="password" class="account_pwd" placeholder="密码" id="pop_login_pwd">
                                    <span class="pop_remember tipsy_south save_pwd" title="是否记住密码">
                                        <input class="remember_switch" type="checkbox"/>
                                        <input type="hidden" value="0" id="pop_login_remember"/>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="register_div_bottom login_bottom">
                            <?php if($this->config['show_login_captcha']):?>
                                <div id="login_code">
                                    <input type="text" id="pop_login_code" class="account_code" name="code" style="margin-left:10px" placeholder="验证码">
                                    <img id="pop_login_captcha" src="<?php echo $this->config['site'];?>/application/securimage/securimage_show.php"  title="点击刷新该验证码" class="tipsy_south pop_code" onclick="this.src='<?php echo $this->config['site'];?>/application/securimage/securimage_show.php?'+Math.random()">
                                </div>
                            <?php else:?>
                                <input type="password" class="account_pwd" placeholder="密码" id="pop_login_pwd">
                                <span class="pop_remember tipsy_south save_pwd" title="是否记住密码">
                                    <input class="remember_switch" type="checkbox"/>
                                    <input type="hidden" value="0" id="pop_login_remember"/>
                                </span>
                            <?php endif; ?>
                            <div class="clear"></div>
                            <?php if($this->config['close_register']): ?>
                                <div class="pop_switch_off">抱歉，本站目前仅能登录，暂时无法注册</div>
                            <?php else: ?>
                                <div class="pop_switch" id="to_register">还没账号？点此注册</div>
                            <?php endif; ?>
                            <div class="submit" id="login_submit">登 录</div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </td>
    </tr>
</table>
<div id="pop_user_outer"></div>
<script type="text/javascript" src="<?php echo $this->config['public']; ?>/switchery.min.js"></script>
<script>
    var elems = Array.prototype.slice.call(document.querySelectorAll('.remember_switch'));
    elems.forEach(function(html) {
        var switchery = new Switchery(html,{size:'small',color:'#FFA83B'});
    });
</script>
</body>
</html>