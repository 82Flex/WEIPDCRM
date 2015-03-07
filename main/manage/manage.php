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
	
	/* DCRM Upload List */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'manage';

	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {

		require_once("header.php");

					if (!isset($_GET['action'])) {
				?>
				<h2>导入软件包</h2>
				<br />
				<?php
						$folder = opendir("../upload/");
						$files = array();
						while ($element = readdir($folder)) {
							if (preg_match("#.\.deb#", $element)) {
								$files[] = $element;
							}
						}
						if (empty($files)) {
				?>
						<h3 class="alert alert-info">
							上传目录为空。<br />
							您可以通过上传功能或 FTP 服务将软件包上传到根目录的 upload 目录下。
						</h3>
				<?php
						}
						else {
							sort($files);
				?>
						<h3 class="navbar">文件列表　<a href="manage.php?action=force">强制继承</a></h3>
						<table class="table"><thead><tr>
						<th><ul class="ctl">删除</ul></th>
						<th><ul class="ctl">继承</ul></th>
						<th><ul class="ctl" style="width:20%;">名称</ul></th>
						<th><ul class="ctl" style="width:100px;">尺寸</ul></th>
						</tr></thead><tbody>
				<?php
							foreach ($files as $file) {
								$filesize = filesize("../upload/" . $file);
								$filesize_withext = sizeext($filesize);
				?>
						<tr>
						<td><a href="manage.php?action=delete_confirmation&file=<?php echo(urlencode($file)); ?>" class="close" style="line-height: 12px;">&times;</a></td>
						<td><a href="manage.php?action=force&file=<?php echo(urlencode($file)); ?>" class="close" style="line-height: 12px;">&equiv;</a></td>
						<td><a href = "import.php?filename=<?php echo(urlencode($file)); ?>"><ul class="ctl"><?php echo($file); ?></a></ul></td>
						<td><ul class="ctl"><?php echo($filesize_withext); ?></ul></td>
						</tr>
				<?php
							}
				?>
						</tbody></table>
				<?php
						}
					} elseif ($_GET['action'] == "force") {
				?>
						<h2>导入软件包</h2>
						<br />
						<h3 class="navbar"><a href="manage.php">文件列表</a>　强制继承</h3>
						<div class="form-horizontal">
							<div class="group-control">
								<label class="control-label">文件名</label>
								<div class="controls">
									<input class="input-xlarge" id="filename" required="required" value="<?php if(!empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))){echo(htmlspecialchars(urldecode($_GET['file'])));} ?>" />
								</div>
							</div>
							<br />
							<div class="group-control">
								<label class="control-label">目标编号</label>
								<div class="controls">
									<input class="input-xlarge" id="forceid" required="required" />
								</div>
							</div>
							<br />
							<div class="form-actions">
								<div class="controls">
									<button type="submit" class="btn btn-success" onclick="javascript:jump();">提交</button>
								</div>
							</div>
						</div>
				<?php
					} elseif ($_GET['action'] == "delete_confirmation" AND !empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))) {
				?>
						<h3 class="alert">您确定要删除：<?php echo(urldecode($_GET['file'])); ?>？<br />该操作无法撤销！</h3>
						<a class="btn btn-warning" href="manage.php?action=delete&file=<?php echo($_GET['file']); ?>">确定</a>　
						<a class="btn btn-success" href="manage.php">取消</a>
				<?php
					} elseif ($_GET['action'] == "delete" AND !empty($_GET['file']) AND file_exists("../upload/" . urldecode($_GET['file']))) {
						if (is_writable("../upload/" . urldecode($_GET['file']))) {
							unlink("../upload/" . urldecode($_GET['file']));
							header("Location: manage.php");
							exit();
						} else {
				?>
						<h3 class="alert alert-error">
							无法删除，请检查文件系统权限。<br />
							<a href="sections.php">返回</a>
						</h3>
				<?php
						}
					} else {
				?>
						<h3 class="alert alert-error">
							无效的请求。<br />
							<a href="sections.php">返回</a>
						</h3>
				<?php
					}
				?>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
		function jump() {
			var fname = document.getElementById("filename");
			var fid = document.getElementById("forceid");
			if (fname.value.length > 0 && fid.value.length > 0) {
				window.location.href = "import.php?filename=" + fname.value + "&force=" + fid.value;
			}
			return 0;
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