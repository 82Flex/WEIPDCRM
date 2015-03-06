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
	
	/* DCRM Debian Simple View */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once('include/connect.inc.php');
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'view';
	$f_Package = "";
	
	if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
		header("Location: login.php");
		exit();
	}
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		httpinfo(500);
		exit();
	}
	mysql_query("SET NAMES utf8");
	$select  = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		httpinfo(500);
		exit();
	}
	if (is_numeric($_GET['id'])) {
		$request_id = (int)$_GET['id'];
	} else {
		httpinfo(405);
		exit();
	}
	$m_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'");
	if (!$m_query) {
		httpinfo(500);
		exit();
	}
	if (isset($_GET['action']) && $_GET['action'] == "image" && isset($_POST['image']) && strlen($_POST['image']) > 0) {
		mysql_query("INSERT INTO `".DCRM_CON_PREFIX."ScreenShots`(`PID`, `Image`) VALUES('".$request_id."', '".mysql_real_escape_string($_POST['image'])."')");
	}
	elseif (isset($_GET['action']) && $_GET['action'] == "del" && is_numeric($_GET['image'])) {
		mysql_query("DELETE FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `ID` = '".mysql_real_escape_string($_GET['image'])."'");
	}

	require_once("header.php");
?>
			<input type="radio" name="package" value="<?php echo($request_id); ?>" style="display: none;" checked="checked" />
			<h2>查看软件包信息</h2>
			<br />
<?php
	$m_array = mysql_fetch_assoc($m_query);
	if (!$m_array) {
		$alert = "查询不到指定的项目。";
	} else {
		unset($m_array['Multi']);
		foreach ($m_array as $m_key => $m_value) {
			if (!empty($m_value)) {
				$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
			}
		}
?>
			<div class="alert alert-info">
<?php echo nl2br(htmlspecialchars($f_Package)); ?>
			</div>
<?php
	}
	$m_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$request_id."'");
	if (!$m_query) {
		httpinfo(500);
		exit();
	}
	if (mysql_affected_rows() <= 0) {
?>
			<div class="alert" id="tips">
				该软件包暂无截图<br />
			</div>
<?php
	} else {
?>
			<div class="alert alert-success" id="tips">
				软件包截图 <?php echo(mysql_affected_rows()); ?> 张<br />
<?php
		while ($m_array = mysql_fetch_assoc($m_query)) {
?>
				<li><a href="<?php echo($m_array["Image"]); ?>"><?php if(strlen($m_array["Image"]) > 72){echo(mb_substr($m_array["Image"],0,72,"UTF-8").' ...');}else{echo($m_array["Image"]);} ?></a>&emsp;<a href="javascript:delimage(<?php echo($m_array["ID"]); ?>);">&times;</a></li>
<?php
		}
?>
			</div>
<?php
	}
?>
			<form class="form-horizontal" method="POST" action="view.php?id=<?php echo($request_id); ?>&action=image">
				<fieldset>
					<div class="group-control">
						<label class="control-label">* 新增截图</label>
						<div class="controls"><input type="button" id="image1" value="选择图片" />
							<input type="text" id="url1" style="width: 400px;" required="required" name="image" /><button action="view.php?id=<?php echo($request_id); ?>&action=image">确认</button>
						</div>
					</div>
				</fieldset>
			</form>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
		function delimage(pid) {
			if(confirm("您确定要彻底删除这张截图？")){
			   window.location.href = "view.php?id=<?php echo($request_id); ?>&action=del&image=" + pid;
			}
		}
	</script>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="js/themes/default/default.css" />
		<script charset="utf-8" src="js/kindeditor.min.js"></script>
		<script charset="utf-8" src="js/lang/zh_CN.js"></script>
	<script>
			KindEditor.ready(function(K) {
				var editor = K.editor({
					allowFileManager : true
				});
				K('#image1').click(function() {
					editor.loadPlugin('image', function() {
						editor.plugin.imageDialog({
							imageUrl : K('#url1').val(),
							clickFn : function(url, title, width, height, border, align) {
								K('#url1').val(url);
								editor.hideDialog();
							}
						});
					});
				});

			});
		</script>

</body>
</html>