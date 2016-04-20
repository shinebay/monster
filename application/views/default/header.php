<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="author" content="http://www.2ma4.com/user/1" />
    <title><?php echo $title; ?></title>
    <?php if($keywords!=''): ?><meta name="keywords" content="<?php echo $keywords; ?>"/><?php endif; ?>
    <?php if($description!=''): ?><meta name="description" content="<?php echo $description; ?>"/><?php endif; ?>
    <?php if($prev!=''): ?><link rel="prev" href="<?php echo $prev; ?>" /><?php endif;?>
    <?php if($next!=''): ?><link rel="next" href="<?php echo $next; ?>" /><?php endif;?>
    <link href="<?php echo $this->config['public']; ?>/css/icon.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $this->config['public']; ?>/css/base.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="http://libs.baidu.com/jquery/1.7.0/jquery.min.js"></script>
    <script type="text/javascript">
        var site='<?php echo $this->config['site']; ?>';
        var uid='<?php echo $this->uid; ?>';
        var username='<?php echo $this->username; ?>';
        var cookie_prefix='<?php echo $this->config['cookie_prefix']; ?>';
        uid=uid==''?'':parseInt(uid);
    </script>
    <script type="text/javascript" src="<?php echo $this->config['public']; ?>/global.js"></script>
</head>
<body>
<div id="content">
    <div id="top">
        <div id="header">
            <div id="pattern"></div>
            <div id="nav">
                <a href="<?php echo $this->config['site']; ?>" id="logo">
                    <img src="<?php echo $this->config['public']; ?>/css/logo.png">
                </a>
                <div id="nav_item">
                    <a href="<?php echo url('main/threads',array('type'=>'hot','last_id'=>0,'direction'=>1)); ?>" id="nav_item_hot"<?php if($navbar_cur=='hot'): ?> class="current"<?php endif; ?>><i class="icon icon-topic"></i>默认排序</a>
                    <a href="<?php echo url('main/threads',array('type'=>'latest','last_id'=>0,'direction'=>1)); ?>" id="nav_item_latest"<?php if($navbar_cur=='latest'): ?> class="current"<?php endif; ?>><i class="icon icon-list"></i>最新帖子</a>
                </div>
                <div id="search_container">
                    <input type="text" id="search_input" placeholder="搜索全站帖子..."/>
                    <i class="icon icon-search" id="search_sub"></i>
                </div>
                <div id="header_user">
                    <?php if(!$this->uid): ?>
                        <a class="account login_modal">登录</a>
                        <a class="account register_modal">注册</a>
                    <?php else: ?>
                        <div id="user_panel">
                            <a href="<?php echo url('profile/setting'); ?>" id="my">
                                <i id="my_icon"></i>
                                <span><?php echo $this->username; ?></span>
                            </a>
                            <a href="<?php echo url('profile/msg'); ?>" class="header_tipsy" title="<?php if(count($new_inform['new_msg'])>0): echo count($new_inform['new_msg']).'人给您发来'.array_sum($new_inform['new_msg']).'条新私信';else:?>暂无新私信<?php endif; ?>" id="msg_icon">
                                <?php if(count($new_inform['new_msg'])>0): ?><div class="dot" id="msg_dot"></div><?php endif; ?>
                            </a>
                            <a href="<?php echo url('profile/notifications',array('last_id'=>0,'direction'=>1)); ?>" class="header_tipsy" title="<?php if($new_inform['new_notice']>0): ?>您有<?php echo $new_inform['new_notice']; ?>条新通知<?php else: ?>暂无新通知<?php endif; ?>" id="notice">
                                <i class="icon icon-bell"></i>
                                <?php if($new_inform['new_notice']>0): ?><div class="dot" id="notice_dot"></div><?php endif; ?>
                            </a>
                            <a id="logout" class="header_tipsy" title="退出本站"><i class="icon icon-logout"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="sub_header">
            <div id="sub_header_inner">
                <div id="breadcrumb">
                    <span>当前位置：</span>
                    <ol itemscope itemtype="http://schema.org/BreadcrumbList">
                        <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                            <a itemprop="item" rel="home" href="<?php echo $this->config['site']; ?>">
                                <span itemprop="name">首页</span>
                            </a>
                            <meta itemprop="position" content="1" />
                        </li><?php echo $breadcrumb; ?>
                    </ol>
                </div>
                <div id="site_info">
                    <div id="site_info_left">总会员：<?php echo $siteCounter['user_count']; ?>人</div>
                    <div id="site_info_right">总帖子数：<?php echo $siteCounter['thread_count']; ?>个</div>
                </div>
            </div>
        </div>
    </div>
    <div id="main">