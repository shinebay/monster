<?php require('header.php'); ?>
    <div id="main_middle">
        <div id="user">
            <?php
            require('user_top.php');
            if($user_info['ban_view_collect']==1&&!isset($isProfile)):
            ?>
            <div class="wrapper shadow" id="zero" align="center">
                <div id="zero_inner"></div>
                对方关闭了查看收藏列表权限
            </div>
            <?php else:require('list.php'); endif; ?>
        </div>
    </div>
<?php require('footer.php'); ?>