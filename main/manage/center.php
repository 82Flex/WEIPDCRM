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
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/func.php");
	require_once("include/corepage.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (isset($_SESSION['connected'])) {
		$con = mysql_connect($server,$username,$password);
		if (!$con) {
			goto endlabel;
		}
		mysql_query("SET NAMES utf8",$con);
		$select  = mysql_select_db($database,$con);
		if (!$select) {
			$alert = mysql_error();
			goto endlabel;
		}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - 源管理系统</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<style type="text/css">
	.ctl {text-overflow:ellipsis;overflow:hidden;white-space: nowrap;padding:2px}
	.page {font:16px}
	.page span{float:left;margin:0px 3px;}
	.page a{float:left;margin:0 3px;border:1px solid #ddd;padding:3px 7px; text-decoration:none;color:#666}
	.page a.now_page,#page a:hover{color:#fff;background:#0088cc}
	</style>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="span6" id="logo">
				<p class="title">DCRM</p>
				<h6 class="underline">Darwin Cydia Repository Manager</h6>
			</div>
			<div class="span6">
				<div class="btn-group pull-right">
					<a href="build.php" class="btn btn-inverse">刷新列表</a>
					<a href="settings.php" class="btn btn-info">设置</a>
					<a href="login.php?action=logout" class="btn btn-info">注销</a>
				</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="span2.5" style="margin-left:0!important;">
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
						<li class="nav-header">PACKAGES</li>
							<li><a href="upload.php">上传软件包</a></li>
							<li><a href="manage.php">导入软件包</a></li>
							<li class="active"><a href="center.php">管理软件包</a></li>
						<li class="nav-header">REPOSITORY</li>
							<li><a href="sections.php">分类管理</a></li>
							<li><a href="release.php">源信息设置</a></li>
						<li class="nav-header">SYSTEM</li>
							<li><a href="stats.php">运行状态</a></li>
							<li><a href="about.php">关于程序</a></li>
					</ul>
				</div>
			</div>
			<div class="span10">
				<?php
					if (!isset($_GET['action'])) {
						if (isset($_GET['search'])) {
						?>
				<h2>管理软件包</h2>
				<br />
				<h3 class="navbar"><a href="center.php">所有软件包</a>　搜索软件包</h3>
					<br />
					<form class="form-horizontal" method="GET" action="center.php" >
					<div class="group-control">
						<label class="control-label">搜索内容</label>
						<div class="controls">
							<input type="hidden" name="action" value="search" />
							<input class="input-xlarge" name="contents" required="required" />
						</div>
						<br/>
						<label class="control-label">搜索类型</label>
						<div class="controls">
							<select name="type" >
							<option value="1" selected="selected">标识符</option>
							<option value="2">名称</option>
							<option value="3">作者</option>
							<option value="4">描述</option>
							<option value="5">提供者</option>
							<option value="6">保证人</option>
							<option value="7">分类</option>
							</select>
						</div>
					</div>
					<br />
					<div class="form-actions">
						<div class="controls">
							<button type="submit" class="btn btn-success">搜索</button>
						</div>
					</div>
					</form>
				<br />
				<?php
						}
						else {
							echo '<h2>管理软件包</h2>';
							echo '<br />';
							echo '<h3 class="navbar">所有软件包　<a href="center.php?search=yes">搜索软件包</a></h3>';
							if (isset($_GET['page'])) {
									$page = $_GET['page'];
							}
							elseif (isset($_SESSION['page'])) {
								$page = $_SESSION['page'];
							}
							else {
								$page = 1;
							}
							if ($page <= 0 OR $page >= 100) {
								$page = 1;
							}
							unset($_SESSION['contents']);
							unset($_SESSION['type']);
							$_SESSION['page'] = $page;
							$page_a = $page * 10 - 10;
							if ($page == 1) {
								$page_b = $page;
							}
							else {
								$page_b = $page - 1;
							}
							$list_query = mysql_query("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat` FROM `Packages` ORDER BY `Stat` DESC, `ID` DESC LIMIT " . (string)$page_a. ",10",$con);
							if ($list_query == FALSE) {
								goto endlabel;
							}
							else {
								echo '<table class="table"><thead><tr>';
								echo '<th><ul class="ctl">显示</ul></th>';
								echo '<th><ul class="ctl">编辑</ul></th>';
								echo '<th><ul class="ctl">删除</ul></th>';
								echo '<th><ul class="ctl">名称</ul></th>';
								// echo '<th><ul class="ctl">标记</ul></th>';
								echo '<th><ul class="ctl">版本</ul></th>';
								echo '<th><ul class="ctl">下载次数</ul></th>';
								echo '<th><ul class="ctl">历史</ul></th>';
								echo '</tr></thead><tbody>';
								$i = 0;
								while ($list = mysql_fetch_assoc($list_query)) {
									$i++;
									if ((int)$list['Stat'] != 1) {
										$submit_icon = "▽";
									}
									else {
										$submit_icon = "▼";
									}
									echo '<tr>';
									echo '<td><a href="center.php?action=submit&id=' . $list['ID'] . '" class="close" style="line-height: 20px;">' . $submit_icon . '</a></td>';
									echo '<td><a href="edit.php?id=' . $list['ID'] . '" class="close" style="line-height: 20px;">◎</a></td>';
									echo '<td><a href="center.php?action=delete_confirm&name=' . $list['Package'] . '&id=' . $list['ID'] . '" class="close" style="line-height: 20px;">&times;</a></td>';
									if (empty($list['Name'])) {
										$list['Name'] = "[未命名]";
									}
									echo '<td><a href = "view.php?id=' . $list['ID'] . '"><ul class="ctl" style="width:300px;">' . htmlspecialchars($list['Name']) . '</ul></a></td>';
									// echo '<td><ul class="ctl" style="width:200px;">' . htmlspecialchars($list['Package']) . '</ul></td>';
									echo '<td><ul class="ctl" style="width:60px;">' . htmlspecialchars($list['Version']) . '</ul></td>';
									echo '<td><ul class="ctl" style="width:50px;">' . $list['DownloadTimes'] . '</ul></td>';
									echo '<td><a href="center.php?action=search&contents=' . $list['Package'] . '&type=1" class="close" style="line-height: 20px;">&raquo;</a></td>';
									echo '</tr>';	
								}
								if ($i < 10) {
									$page_c = $page;
								}
								else {
									$page_c = $page + 1;
								}
								echo '</tbody></table>';
								
								$q_info = mysql_query("SELECT count(*) FROM `Packages`");
								$info = mysql_fetch_row($q_info);
								$totalnum = (int)$info[0];
								$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'center.php?page=%page', 'now_page'  =>$page, 'list_rows' =>10);
								$page = new Core_Lib_Page($params);
								echo '<div class="page">' . $page->show(2) . '</div>';
							}
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "search" AND !empty($_GET['contents']) AND !empty($_GET['type'])) {
						unset($_SESSION['page']);
						$_SESSION['contents'] = $_GET['contents'];
						$_SESSION['type'] = $_GET['type'];
						echo '<h2>管理软件包</h2>';
						echo '<br />';
						echo '<h3 class="navbar">搜索软件包：'.$_GET['contents'].'</h3>';
						$search_type = (int)$_GET['type'];
						switch ($search_type) {
							case 1:
								$t = 'Package';
								break;
							case 2:
								$t = 'Name';
								break;
							case 3:
								$t = 'Author';
								break;
							case 4:
								$t = 'Description';
								break;
							case 5:
								$t = 'Maintainer';
								break;
							case 6:
								$t = 'Sponsor';
								break;
							case 7:
								$t = 'Section';
								break;
							default:
								goto endlabel;
						}
						$r_value = mysql_real_escape_string($_GET['contents']);
						$list_query = mysql_query("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat` FROM `Packages` WHERE `" . $t . "` LIKE '%" . $r_value . "%' ORDER BY `Stat` DESC, `ID` DESC LIMIT 25",$con);
						if ($list_query == FALSE) {
							goto endlabel;
						}
						else {
							echo '<table class="table"><thead><tr>';
							echo '<th><ul class="ctl">显示</ul></th>';
							echo '<th><ul class="ctl">编辑</ul></th>';
							echo '<th><ul class="ctl">删除</ul></th>';
							echo '<th><ul class="ctl">名称</ul></th>';
							// echo '<th><ul class="ctl">标记</ul></th>';
							echo '<th><ul class="ctl">版本</ul></th>';
							echo '<th><ul class="ctl">下载次数</ul></th>';
							echo '</tr></thead><tbody>';
								while ($list = mysql_fetch_assoc($list_query)) {
									if ((int)$list['Stat'] != 1) {
										$submit_icon = "▽";
									}
									else {
										$submit_icon = "▼";
									}
									echo '<tr>';
									echo '<td><a href="center.php?action=submit&id=' . $list['ID'] . '" class="close" style="line-height: 20px;">' . $submit_icon . '</a></td>';
									echo '<td><a href="edit.php?id=' . $list['ID'] . '" class="close" style="line-height: 20px;">◎</a></td>';
									echo '<td><a href="center.php?action=delete_confirm&name=' . $list['Package'] . '&id=' . $list['ID'] . '" class="close" style="line-height: 20px;">&times;</a></td>';
									if (empty($list['Name'])) {
										$list['Name'] = "[未命名]";
									}
									echo '<td><a href = "view.php?id=' . $list['ID'] . '"><ul class="ctl" style="width:300px;">' . htmlspecialchars($list['Name']) . '</ul></a></td>';
									// echo '<td><ul class="ctl" style="width:200px;">' . htmlspecialchars($list['Package']) . '</ul></td>';
									echo '<td><ul class="ctl" style="width:60px;">' . htmlspecialchars($list['Version']) . '</ul></td>';
									echo '<td><ul class="ctl" style="width:50px;">' . $list['DownloadTimes'] . '</ul></td>';
									echo '</tr>';	
								}
							echo '</tbody></table>';
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirm" AND !empty($_GET['name']) AND !empty($_GET['id'])) {
						echo '<h3 class="alert">您确定要执行删除操作： ' . htmlspecialchars($_GET['name']) . ' ？<br />该操作不可逆，且下载次数将被清空！</h3>';
						echo '<a class="btn btn-danger" href="center.php?action=delete&id='.$_GET['id'].'">删除</a>';
						echo '　';
						echo '<a class="btn btn-warning" href="center.php?action=submit&id='.$_GET['id'].'">隐藏</a>';
						echo '　';
						echo '<a class="btn btn-success" href="center.php?';
						if (!empty($_SESSION['page'])) {
							echo "page=" . $_SESSION['page'];
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							echo "action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type'];
						}
						echo '">取消</a>';
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
						$delete_id = (int)$_GET['id'];
						$f_query = mysql_query("SELECT `Filename` FROM `Packages` WHERE `ID` = '" . $delete_id . "'",$con);
						if (!$f_query) {
							goto endlabel;
						}
						else {
							$f_filename = mysql_fetch_assoc($f_query);
							if (!$f_filename) {
								goto endlabel;
							}
							unlink($f_filename['Filename']);
							$d_query = mysql_query("DELETE FROM `Packages` WHERE `ID` = '" . $delete_id . "'",$con);
						}
						if (!$d_query) {
							goto endlabel;
						}
						elseif (!empty($_SESSION['page'])) {
							header("Location: center.php?page=" . $_SESSION['page']);
							exit();
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
							exit();
						}
						else {
							header("Location: center.php");
							exit();
						}
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "submit" AND !empty($_GET['id'])) {
						$submit_id = (int)$_GET['id'];
						$s_query = mysql_query("SELECT `Stat` FROM `Packages` WHERE `ID` = '" . $submit_id . "'",$con);
						if (!$s_query) {
							goto endlabel;
						}
						else {
							$s_info = mysql_fetch_assoc($s_query);
							if (!$s_info) {
								goto endlabel;
							}
						}
						if ((int)$s_info['Stat'] != 1) {
							$s_query = mysql_query("UPDATE `Packages` SET `Stat` = '1' WHERE `ID` = '" . $submit_id . "'",$con);
						}
						else {
							$s_query = mysql_query("UPDATE `Packages` SET `Stat` = '-1' WHERE `ID` = '" . $submit_id . "'",$con);
						}
						if (!$s_query) {
							goto endlabel;
						}
						elseif (!empty($_SESSION['page'])) {
							header("Location: center.php?page=" . $_SESSION['page']);
							exit();
						}
						elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
							header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
							exit();
						}
						else {
							header("Location: center.php");
							exit();
						}
					}
					if (!$con) {
						endlabel:
						echo '<h3 class="alert alert-error">数据库出现错误！</h3><code>'.mysql_error().'</code>';
					}
					else {
						mysql_close($con);
					}
				?>
			</div>
		</div>
	</div>
</body>
</html>
<?php
	}
	else {
		header("Location: login.php");
		exit();
	}
?>