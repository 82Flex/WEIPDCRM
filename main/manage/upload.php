<?php
	/*
	    This file is part of WEIPDCRM.
	
	    WEIPDCRM is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License, or
	    (at your option) any later version.
	
	    WEIPDCRM is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.
	
	    You should have received a copy of the GNU General Public License
	    along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	/* DCRM Upload Page */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'upload';
	
	function upload($file, $path = '../upload/', $name = '') {
		if ($file["size"] <= 0) {
			return "文件尺寸错误！";
		}
		if (pathinfo($_FILES['deb']['name'], PATHINFO_EXTENSION) != "deb") {
			return "文件类型错误！";
		}
		if ($file["error"] > 0) {
			return "上传失败，错误代码：" . $file["error"];
		}
		if (file_exists($path . $file["name"])) {
			return $file["name"] . " 已经存在";
		}
		$name = ($name == '') ? $file["name"] : $name;
		move_uploaded_file($file["tmp_name"], $path . $name);
		return "上传成功：". $path . $name;
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
				<div class="subtitle"><h2>上传软件包</h2><span>无法上传文件？尝试<a href="./upload.php?mode=classic">普通方式上传</a></span></div>
				<br />
				<div class="form-horizontal">
					<fieldset>
						<div class="group-control">
							<label class="control-label">请选择要上传的软件包</label>
							<div class="controls" style="width: 400px;">
								<input type="file" name="file_upload" id="file_upload" />
								<p class="help-block" id="tips">提示：按住Ctrl(⌘)键可多选</p>
							</div>
						</div>
						<div class="form-actions">
							<div class="controls">
								<a class="btn btn-success" href="javascript:$('#file_upload').uploadify('settings', 'formData', {'typeCode':document.getElementById('id_file').value});$('#file_upload').uploadify('upload','*')">上传</a>
								<a class="btn btn-success" href="javascript:$('#file_upload').uploadify('cancel','*')">重置上传队列</a>
							</div>
							<input type="hidden" value="1215154" name="tmpdir" id="id_file">
						</div>
					</fieldset>
				</div>
				<h3>操作提示</h3>
				<br />
				<h4 class="alert alert-info">· 允许的数据类型：application/x-deb；
				<br />· 单文件最大上传限制：10M
				<br />· 请勿上传可能扰乱互联网安全秩序的数据；
				<br />· 请上传标准封装的 Debian 软件包，否则可能会导致数据丢失。</h4>
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
        'buttonText' : '选择文件',//设置按钮文本
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
				<div class="subtitle"><h2>上传软件包</h2><span>无法上传文件？尝试<a href="./upload.php?mode=multiple">更多方式上传</a></span></div>
				<br />
				<h3>选择文件</h3>
				<br />
				<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="upload.php?action=upload">
					<fieldset>
						<div class="group-control">
							<label class="control-label">请选择一个软件包</label>
							<div class="controls">
								<input type="file" id="deb" class="span6" name="deb" accept="application/x-deb" />
								<p class="help-block" id="tips">准备就绪</p>
							</div>
						</div>
						<div class="form-actions">
							<div class="controls">
								<button type="button" class="btn btn-success" onclick="return ajaxFileUpload();">上传</button>
							</div>
						</div>
					</fieldset>
				</form>
				<h3>操作提示</h3>
				<br />
				<h4 class="alert alert-info">· 允许的数据类型：application/x-deb；
				<br />· 服务器最大上传限制：<?php echo(ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled"); ?>
				<br />· 请勿上传可能扰乱互联网安全秩序的数据；
				<br />· 请上传标准封装的 Debian 软件包，否则可能会导致数据丢失。</h4>
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
					$("#tips").html("上传中，请稍等……");
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
					$("#tips").html("不允许的文件类型！");
					return false;
				}
			} else {
				$("#tips").html("请选择一个软件包！");
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
	}
	else {
		header("Location: login.php");
		exit();
	}
?>