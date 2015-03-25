<?php
/**
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */

/* DCRM Upload Page */

session_start();
define("DCRM",true);
$activeid = 'upload';
$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';

function upload($file, $path = '../upload/', $name = '') {
	if ($file["size"] <= 0) {
		return __('File size incorrect!');
	}
	if (pathinfo($_FILES['deb']['name'], PATHINFO_EXTENSION) != "deb") {
		return __('File type incorrect!');
	}
	if ($file["error"] > 0) {
		return sprintf(__('Upload failed, Error Code: %s.'), $file["error"]);
	}
	if (file_exists($path . $file["name"])) {
		return sprintf(__('%s already exists.'), $file["name"]);
	}
	$name = ($name == '') ? $file["name"] : $name;
	move_uploaded_file($file["tmp_name"], $path . $name);
	return sprintf(__('Uploaded successfully: %s.'), $path . $name);
}

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	if (isset($_GET['action']) && $_GET['action'] == "upload" && !empty($_FILES)) {
		echo upload($_FILES["deb"]);
		exit();
	}

	require_once("header.php");
	if (!isset($_GET['action']) && !isset($_GET['mode'])) {
?>
<script type="text/javascript">
function flashChecker(){
var hasFlash=0;

if(document.all){
	var swf = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
	if(swf){
		hasFlash=1;
	}
}else{
	if (navigator.plugins && navigator.plugins.length > 0){
		var swf=navigator.plugins["Shockwave Flash"];
		if (swf){
			hasFlash=1;
		}
	}
}
return {f:hasFlash};
}

var fls=flashChecker();
var s="";
if(fls.f) window.location.replace('./upload.php?mode=multiple');
else window.location.replace('./upload.php?mode=classic');
</script>
<?php
		exit();
	}
	if (isset($_GET['mode']) && $_GET['mode'] == 'multiple') {
?>
				<div class="subtitle"><h2><?php _e('Upload Packages'); ?></h2><span><?php printf(__('Unable to upload files? Try %s.'), '<a href="./upload.php?mode=classic">'.__('classic uploader').'</a>'); ?></span></div>
				<br />
				<div class="form-horizontal">
					<fieldset>
						<div class="group-control">
							<label class="control-label"><?php _e('Please select packages'); ?></label>
							<div class="controls" style="width: 400px;">
								<input type="file" name="file_upload" id="file_upload" />
								<p class="help-block" id="tips"><?php _e('Tip: Select multiple packages by hold down Ctrl(⌘) key while you click the packages.'); ?></p>
							</div>
						</div>
						<div class="form-actions">
							<div class="controls">
								<a class="btn btn-success" href="javascript:$('#file_upload').uploadify('settings', 'formData', {'typeCode':document.getElementById('id_file').value});$('#file_upload').uploadify('upload','*')"><?php _e('Upload'); ?></a>
								<a class="btn btn-success" href="javascript:$('#file_upload').uploadify('cancel','*')"><?php _ex('Reset', 'Upload'); ?></a>
							</div>
							<input type="hidden" value="1215154" name="tmpdir" id="id_file">
						</div>
					</fieldset>
				</div>
				<h3><?php _e('Tips'); ?></h3>
				<br />
				<h4 class="alert alert-info">· <?php _e('Allowed data type: '); ?>application/x-deb<?php _ex(';', 'Separator'); ?>
				<br />· <?php _e('Upload max filesize: '); ?>10M<?php _ex(';', 'Separator'); ?>
				<br />· <?php _e('Please do not upload disrupt the Internet security data'); _ex(';', 'Separator'); ?>
				<br />· <?php _e('Please upload the standard package of Debian software package, otherwise it may cause loss of data.'); ?></h4>
			</div>
		</div>
	</div>
	</div>
<script charset='utf-8' type="text/javascript" src="js/jquery.uploadify.js"></script>
<link rel="stylesheet" type="text/css" href="css/uploadify.css"/>
<script charset='utf-8' type="text/javascript">
var img_id_upload=new Array();//初始化数组，存储已经上传的图片名
var i=0;//初始化数组下标
$(function() {
    $('#file_upload').uploadify({
    	'auto'     : false,//关闭自动上传
    	'removeTimeout' : 1,//文件队列上传完成1秒后删除
        'swf'      : 'js/uploadify.swf',
        'uploader' : 'js/uploadify.php',
        'method'   : 'post',//方法，服务端可以用$_POST数组获取数据
        'buttonText' : '<?php _e('SELECT FILES'); ?>',//设置按钮文本
        'multi'    : true,//允许同时上传多文件
        'uploadLimit' : 10,//一次最多只允许上传10个文件
        'fileTypeDesc' : 'Debian Software Package',//只允许上传的文件种类
        'fileTypeExts' : '*.deb',//限制允许上传的后缀
        'fileSizeLimit' : '10MB',//限制上传的文件不得超过10MB 
        'onUploadSuccess' : function(file, data, response) {//每次成功上传后执行的回调函数，从服务端返回数据到前端
               img_id_upload[i]=data;
               i++;
               alert(data);
        },
        'onQueueComplete' : function(queueData) {//上传队列全部完成后执行的回调函数
           // if(img_id_upload.length>0)
           // alert('成功上传的文件有：'+encodeURIComponent(img_id_upload));
        }  
        // Put your options here
    });
});
</script>
<?php
	}
	if (isset($_GET['mode']) && $_GET['mode'] == 'classic') {
?>
				<div class="subtitle"><h2><?php _e('Upload Packages'); ?></h2><span><?php printf(__('Unable to upload files? Try %s.'), '<a href="./upload.php?mode=multiple">'.__('multiple uploader').'</a>'); ?></span></div>
				<br />
				<h3><?php _e('Select File'); ?></h3>
				<br />
				<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="upload.php?action=upload">
					<fieldset>
						<div class="group-control">
							<label class="control-label"><?php _e('Please select a package'); ?></label>
							<div class="controls">
								<input type="file" id="deb" class="span6" name="deb" accept="application/x-deb" />
								<p class="help-block" id="tips"><?php _e('Ready'); ?></p>
							</div>
						</div>
						<div class="form-actions">
							<div class="controls">
								<button type="button" class="btn btn-success" onclick="return ajaxFileUpload();"><?php _e('Upload'); ?></button>
							</div>
						</div>
					</fieldset>
				</form>
				<h3><?php _e('Tips'); ?></h3>
				<br />
				<h4 class="alert alert-info">· <?php _e('Allowed data type: '); ?>application/x-deb<?php _ex(';', 'Separator'); ?>
				<br />· <?php _e('Upload max filesize: '); ?><?php echo(ini_get("file_uploads") ? ini_get("upload_max_filesize") : __('Disabled')); _ex(';', 'Separator'); ?>
				<br />· <?php _e('Please do not upload disrupt the Internet security data'); _ex(';', 'Separator'); ?>
				<br />· <?php _e('Please upload the standard package of Debian software package, otherwise it may cause loss of data.'); ?></h4>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript" src="js/ajaxfileupload.js"></script>
	<script type="text/javascript">
		function ajaxFileUpload() {
			fakepath = document.getElementById("deb").value;
			if (fakepath != "") {
				if (/\.[^\.]+$/.exec(fakepath) == ".deb") {
					$("#tips").html("<?php _e('Please wait for uploading...'); ?>");
					$.ajaxFileUpload(
						{
							url: "upload.php?action=upload",
							secureuri: false,
							fileElementId: 'deb',
							dataType: 'text',
							success: function(data) {
								$("#tips").html(data);
							}
						}
					);
					return true;
				} else {
					$("#tips").html("<?php _e('Invalid file type!'); ?>");
					return false;
				}
			} else {
				$("#tips").html("<?php _e('Please select a package!');?>");
				return false;
			}
		}
	</script>
<?php
	}
?>
</body>
</html>
<?php
} else {
	header("Location: login.php");
	exit();
}
?>