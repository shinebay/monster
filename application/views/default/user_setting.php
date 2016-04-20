<?php require('header.php'); ?>
<script type="text/javascript">
    $(document).ready(function(){
        $('.setting_input').focus(function(){
            $(this).next('.setting_ok').css({marginRight:0});
        }).blur(function(){
            var _t=$(this);
            trim(_t.val())==_t.prev('.ini_val').val()&&_t.next('.setting_ok').css({marginRight:-90});
        });
        $('.setting_ok').click(function(){
            var _t=$(this);
            if(_t.hasClass('disabled')){
                return false;
            }
            _t.addClass('disabled').html('保存中...');
            var val=trim(_t.prev('.setting_input').val());
            var name=_t.prev('.setting_input').attr('name');
            $.post(U('profile/saveProfile'),{name:name,val:val},function(callback){
                if(callback.status){
                    _t.siblings('.ini_val').val(val);
                    _t.css({marginRight:-90});
                }else{
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
                _t.removeClass('disabled').html('确 定');
            },'json');
        });
        var elems = document.querySelectorAll('.js-switch');
        var s=[];
        for (var i = 0; i < elems.length; i++) {
            s[elems[i].getAttribute('name')]=new Switchery(elems[i],{size:'small',color:'#FFA83B'});
        }
        $('.user_setting_wrapper .js-switch').change(function(){
            var _t=$(this);
            var name=_t.attr('name');
            var val=this.checked?1:0;
            s[name].disable();
            $.post(U('profile/savePermission'),{name:name,val:val},function(callback){
                if(callback.status){
                    s[name].enable();
                }else{
                    ZENG.msgbox.show(callback.msg,3,2000);
                }
            },'json');
        });
    })
</script>
    <div id="main_middle">
        <div id="user">
            <?php require('user_top.php'); ?>
            <div id="user_setting" class="shadow">
                <div class="user_setting_wrapper"><div class="setting_name">个人介绍：</div><input type="hidden" value="<?php echo $user_info['intro']; ?>" class="ini_val"/><input type="text" name="intro" maxlength="30" value="<?php echo $user_info['intro']; ?>" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name">个人网址：</div><input type="hidden" value="<?php echo $user_info['website']; ?>" class="ini_val"/><input type="text" maxlength="70" value="<?php echo $user_info['website']; ?>" name="website" class="setting_input"/><div class="setting_ok unselect">确 定</div></div>
                <div class="user_setting_wrapper"><div class="setting_name">个人微博：</div><input type="hidden" value="<?php echo $user_info['weibo']; ?>" class="ini_val"/><input type="text" maxlength="70" value="<?php echo $user_info['weibo']; ?>" name="weibo" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name">Dribbble：</div><input type="hidden" value="<?php echo $user_info['dribbble']; ?>" class="ini_val"/><input type="text" maxlength="70" name="dribbble" value="<?php echo $user_info['dribbble']; ?>" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name">GitHub：</div><input type="hidden" value="<?php echo $user_info['github']; ?>" class="ini_val"/><input type="text" maxlength="70" value="<?php echo $user_info['github']; ?>" name="github" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name">Facebook：</div><input type="hidden" value="<?php echo $user_info['facebook']; ?>" class="ini_val"/><input type="text" maxlength="70" value="<?php echo $user_info['facebook']; ?>" name="facebook" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name">Twitter：</div><input type="hidden" value="<?php echo $user_info['twitter']; ?>" class="ini_val"/><input type="text" maxlength="70" value="<?php echo $user_info['twitter']; ?>" name="twitter" class="setting_input"/><div class="setting_ok unselect">确 定</div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">禁止网友在我的个人主页查看我的帖子列表</div><div class="user_checkbox"><input type="checkbox" name="ban_view_thread"<?php if($user_info['ban_view_thread']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">禁止网友在我的个人主页查看我的回复列表</div><div class="user_checkbox"><input type="checkbox" name="ban_view_reply"<?php if($user_info['ban_view_reply']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">禁止网友在我的个人主页查看我的收藏列表</div><div class="user_checkbox"><input type="checkbox" name="ban_view_collect"<?php if($user_info['ban_view_collect']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">禁止网友给我发送私信</div><div class="user_checkbox"><input type="checkbox" name="ban_send_msg"<?php if($user_info['ban_send_msg']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">当有人回复我时不通知我</div><div class="user_checkbox"><input type="checkbox" name="inform_without_reply"<?php if($user_info['inform_without_reply']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">当有人@我时不通知我</div><div class="user_checkbox"><input type="checkbox" name="inform_without_at"<?php if($user_info['inform_without_at']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
                <div class="user_setting_wrapper"><div class="setting_name_long">当有人赞我时不通知我</div><div class="user_checkbox"><input type="checkbox" name="inform_without_upvote"<?php if($user_info['inform_without_upvote']==1): ?> checked<?php endif; ?> class="js-switch"/></div> </div>
            </div>
        </div>
    </div>
<?php require('footer.php'); ?>