<?php require('header.php'); ?>
    <link rel="stylesheet" href="http://cdn.staticfile.org/fancybox/2.1.5/jquery.fancybox.min.css" type="text/css" media="screen" />
    <script type="text/javascript" src="http://cdn.staticfile.org/fancybox/2.1.5/jquery.fancybox.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("a.img_link").fancybox({
                padding: 0,
                helpers: {
                    overlay: {
                        locked: false
                    }
                }
            });
        })
    </script>
    <div id="main_middle">
        <div id="user">
            <?php
            require('user_top.php');
            if($user_info['ban_view_reply']==1):
            ?>
                <div class="wrapper shadow" id="zero" align="center">
                    <div id="zero_inner"></div>
                    对方关闭了回复列表浏览权限
                </div>
            <?php
            elseif(count($reply)>0):foreach($reply as $v):
            ?>
            <div class="wrapper shadow">
                <div class="description">
                    <?php echo replace_link($v['content']); ?>
                </div>
                <div class="ori_thread">
                    <span class="child_comment_top"><i></i></span>
                    <div class="wrapper text_shadow">
                        <div class="title">
                            <a href="<?php echo url('user/index',array('uid'=>$v['thread_info']['uid'])); ?>" class="title_avatar avatar_hover">
                                <img src="<?php echo get_avatar($v['thread_info']['user_avatar']); ?>">
                            </a>
                            <a class="title_content" href="<?php echo url('thread/view',array('category_name'=>$this->config['category'][$v['thread_info']['category_id']],'thread_title'=>$v['thread_info']['thread_title'],'thread_id'=>$v['thread_id'],'last_id'=>0,'direction'=>1)); ?>">
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
            <?php endforeach;?>
            <div class="thread_pager">
                <?php require('page_btn.php'); ?>
            </div>
            <?php else:?>
            <div class="wrapper shadow" id="zero" align="center">
                <div id="zero_inner"></div>
                暂未回复任何帖子
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php require('footer.php'); ?>