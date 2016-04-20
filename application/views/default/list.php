<?php if(count($thread_list)>0):foreach($thread_list as $k=>$v):?>
<div class="wrapper shadow">
    <div class="title">
        <a href="<?php echo url('user/index',array('uid'=>$v['uid'])); ?>" class="title_avatar avatar_hover" uid="<?php echo $v['uid']; ?>">
            <img src="<?php echo $v['user_avatar']; ?>">
        </a>
        <a class="title_content" href="<?php echo url('thread/view',array('thread_id'=>$v['thread_id'],'last_id'=>0,'direction'=>1)); ?>">
            <h2><?php echo $v['thread_title']; ?></h2>
        </a>
        <div class="clear"></div>
    </div>
    <div class="description">
        <?php echo cutstr(get_plain_text($v['thread_content']),140); ?>
    </div>
    <div class="bottom">
        <div class="bottom_list">
            <span class="bottominfo">浏览(<?php echo $v['view_num']; ?>)</span><i class="devider">&bull;</i>
            <span class="bottominfo">回复(<?php echo $v['reply_num']; ?>)</span><i class="devider">&bull;</i>
            <span class="bottominfo">投票(<?php echo $v['vote_num']; ?>)</span><i class="devider">&bull;</i>
            <span class="bottominfo">收藏(<?php echo $v['collect_num']; ?>)</span>
            <?php if($this->uid==1): ?><span class="bottominfo del_thread"><input type="hidden" class="tid" value="<?php echo $v['thread_id']; ?>"/><i class="devider">&bull;</i>删除</span><?php endif; ?>
            <a class="list_pub_time"><?php echo $v['username']; ?> 发表于<?php echo friendlyDate($v['ctime']); ?></a>
        </div>
    </div>
</div>
<?php endforeach;else: ?>
    <div class="wrapper shadow" id="zero" align="center">
        <div id="zero_inner"></div>
        暂无相关帖子
    </div>
<?php
    endif;
    require('page_btn.php');
?>