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
			
					if (!isset($_GET['action'])) {
				?>
				<h2>上传软件包</h2>
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
				<?php
					}
				?>
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
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>