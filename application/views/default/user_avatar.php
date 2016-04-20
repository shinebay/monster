<?php require('header.php'); ?>
    <!--<script src="http://open.web.meitu.com/sources/xiuxiu.js" type="text/javascript"></script>
    <script type="text/javascript">
        window.onload=function(){
            /*第1个参数是加载编辑器div容器，第2个参数是编辑器类型，第3个参数是div容器宽，第4个参数是div容器高*/
            xiuxiu.embedSWF("xiuxiu",5,"627","600");
            //修改为您自己的图片上传接口
            xiuxiu.setUploadURL("http://web.upload.meitu.com/image_upload.php");
            xiuxiu.setUploadType(2);
            xiuxiu.setUploadDataFieldName("upload_file");
            xiuxiu.onInit = function ()
            {
                xiuxiu.loadPhoto("http://open.web.meitu.com/sources/images/1.jpg");
            }
            xiuxiu.onUploadResponse = function (data)
            {
                //alert("上传响应" + data);  可以开启调试
            }
        }
    </script>-->
    <div id="main_middle">
        <div id="user">
            <?php require('user_top.php'); ?>
            <div id="user_setting" class="shadow" align="center">
                <!--<div id="xiuxiu"></div>-->
                <div id="avatar_preupload">
                    <script type="text/javascript" src="<?php echo $this->config['public'];?>/cropbox.js"></script>
                    <div class="container none" id="avatar_select">
                        <div class="imageBox">
                            <div class="thumbBox"></div>
                            <div class="spinner" style="display: none">Loading...</div>
                        </div>
                        <div class="action">
                            <a class="post btn btn14 post_avatar" id="crop_btn">
                                <span class="text">裁剪并上传头像</span>
                            </a>
                        </div>
                    </div>
                    <div id="avatar_pre">
                        <img class="round r" src="<?php echo get_avatar($_COOKIE[$this->config['cookie_prefix'].'avatar_big'],'big'); ?>"/><br/><br/><br/>
                        <a class="post btn btn14" id="avatar_btn">
                            <span class="text">点击上传您的头像</span>
                            <input type="file" accept="image/png,image/jpeg" id="html5_upload"/>
                        </a>
                        <br/><br/><br/>
                        <p class="grey">支持的图片格式：jpeg jpg png. 图片大小<1MB</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php require('footer.php'); ?>