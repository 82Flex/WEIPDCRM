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
	
	/* DCRM Debian Edit */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/autofill.inc.php");
	require_once("include/connect.inc.php");
	require_once("include/func.php");
	header("Content-Type: text/html; charset=UTF-8");
	
	if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
		$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
		if (is_numeric($_GET['id'])) {
			$request_id = (int)$_GET['id'];
			if ($request_id < 1) {
				echo("非法请求！");
				exit();
			}
		} else {
			echo("非法请求！");
			exit();
		}
		if (!$con) {
			echo("数据库出现错误：".mysql_error());
			exit();
		}
		mysql_query("SET NAMES utf8");
		$select  = mysql_select_db(DCRM_CON_DATABASE);
		if (!$select) {
			echo("数据库出现错误：".mysql_error());
			exit();
		}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>DCRM - 源管理系统</title>
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<script src="js/mbar.js" type="text/javascript"></script>
	<script charset="utf-8" src="js/kindeditor.min.js"></script>
	<script charset="utf-8" src="js/lang/zh_CN.js"></script>
	<script type="text/javascript">
	KindEditor.ready(function(K) {
		K.each({
			'plug-align' : {
				name : '对齐方式',
				method : {
					'justifyleft' : '左对齐',
					'justifycenter' : '居中对齐',
					'justifyright' : '右对齐'
				}
			},
			'plug-order' : {
				name : '编号',
				method : {
					'insertorderedlist' : '数字编号',
					'insertunorderedlist' : '项目编号'
				}
			},
			'plug-indent' : {
				name : '缩进',
				method : {
					'indent' : '向右缩进',
					'outdent' : '向左缩进'
				}
			}
		},function( pluginName, pluginData ){
			var lang = {};
			lang[pluginName] = pluginData.name;
			KindEditor.lang( lang );
			KindEditor.plugin( pluginName, function(K) {
				var self = this;
				self.clickToolbar( pluginName, function() {
					var menu = self.createMenu({
							name : pluginName,
							width : pluginData.width || 100
						});
					K.each( pluginData.method, function( i, v ){
						menu.addItem({
							title : v,
							checked : false,
							iconClass : pluginName+'-'+i,
							click : function() {
								self.exec(i).hideMenu();
							}
						});
					})
				});
			});
		});
		K.create('#kind', {
			themeType : 'qq',
			items : [
				'source','bold','italic','underline','fontname','fontsize','forecolor','hilitecolor','plug-align','plug-order','plug-indent','link','removeformat'
			]
		});
	});
	function jump() {
		var input = document.getElementById("urlinput");
		window.open(input.value,"_blank");
		return 0;
	}
	function ajax() {
		document.getElementById("contents").innerHTML="数据加载中";
		xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST","hint.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("action=adv_info&item=" + document.getElementById("item_id").value + "&col=" + document.getElementById("item_adv").value);
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4) {
				if (xmlhttp.status == 200) {
					document.getElementById("contents").innerHTML=xmlhttp.responseText;
				} else {
					document.getElementById("contents").innerHTML="数据库错误";
				}
				xmlhttp.close();
			}
		}
	}
	function changeCase(frmObj) {
		var index;
		var tmpStr;
		var tmpChar;
		var preString;
		var postString;
		var strlen;
		tmpStr = frmObj.value.toLowerCase();
		strLen = tmpStr.length;
		if (strLen > 0) {
			for (index = 0; index < strLen; index++) {
				if (index == 0) {
					tmpChar = tmpStr.substring(0, 1).toUpperCase();
					postString = tmpStr.substring(1, strLen);
					tmpStr = tmpChar + postString;
				} else {
					tmpChar = tmpStr.substring(index, index + 1);
					if (tmpChar == " " && index < (strLen - 1)) {
						tmpChar = tmpStr.substring(index + 1, index + 2).toUpperCase();
						preString = tmpStr.substring(0, index + 1);
						postString = tmpStr.substring(index + 2, strLen);
						tmpStr = preString + tmpChar + postString;
					}
				}
			}
		}
		frmObj.value = tmpStr;
	}
	function autofill(opt) {
		if (opt == 1) {
			var pstr = document.getElementsByName("Package")[0].value;
			if (pstr.length > 0) {
				var pstrs = new Array();
				pstrs = pstr.split(".", 4);
				if (pstrs.length >= 1) {
					var save = pstrs[pstrs.length - 1];
				}
				document.getElementsByName("Package")[0].value = "<?php echo AUTOFILL_PRE; ?>" + save;
			} else {
				document.getElementsByName("Package")[0].value = "<?php echo AUTOFILL_PRE; ?>";
			}
		} else if (opt == 2) {
			if (document.getElementsByName("Name")[0].value.length > 0) {
				changeCase(document.getElementsByName("Name")[0]);
			} else {
				document.getElementsByName("Name")[0].value = "<?php echo AUTOFILL_NONAME; ?>";
			}
		} else if (opt == 3) {
			var pstr = document.getElementsByName("Version")[0].value;
			if (pstr.length > 0) {
				if (pstr.indexOf("-") == -1) {
					var pstrs = new Array();
					pstrs = pstr.split(".", 4);
					if (pstrs.length >= 1) {
						var save = parseInt(pstrs[pstrs.length - 1]);
					}
					save++;
					pstr = "";
					for (var i = 0; i < pstrs.length - 1; i++) {
						pstr = pstr + pstrs[i] + ".";
					}
					document.getElementsByName("Version")[0].value = pstr + save.toString();
				} else {
					var pstrs = new Array();
					pstrs = pstr.split("-", 2);
					if (pstrs.length == 2) {
						var save = parseInt(pstrs[1]);
						save++;
						document.getElementsByName("Version")[0].value = pstrs[0] + "-" + save.toString();
					}
				}
			} else {
				document.getElementsByName("Version")[0].value = "0.0.1-1";
			}
		} else if (opt == 4) {
			var pstr = document.getElementsByName("Author")[0].value;
			if (pstr.length == 0) {
				document.getElementsByName("Author")[0].value = "<?php echo AUTOFILL_MASTER; ?> <<?php echo AUTOFILL_EMAIL; ?>>";
			} else {
				if (pstr.indexOf("<") == -1) {
					document.getElementsByName("Author")[0].value = pstr + " <<?php echo AUTOFILL_EMAIL; ?>>";
				}
			}
		} else if (opt == 5) {
			if (document.getElementsByName("Section")[0].value.length == 0) {
				document.getElementsByName("Section")[0].value = "<?php echo AUTOFILL_SECTION; ?>";
			}
		} else if (opt == 6) {
			var pstr = document.getElementsByName("Maintainer")[0].value;
			if (pstr.length == 0) {
				document.getElementsByName("Maintainer")[0].value = "<?php echo AUTOFILL_MASTER; ?> <<?php echo AUTOFILL_EMAIL; ?>>";
			} else {
				if (pstr.indexOf("<") == -1) {
					document.getElementsByName("Maintainer")[0].value = pstr + " <<?php echo AUTOFILL_EMAIL; ?>>";
				}
			}
		} else if (opt == 7) {
			var pstr = document.getElementsByName("Sponsor")[0].value;
			if (pstr.length == 0) {
				document.getElementsByName("Sponsor")[0].value = "<?php echo AUTOFILL_MASTER; ?> <<?php echo AUTOFILL_SITE; ?>>";
			} else {
				if (pstr.indexOf("<") == -1) {
					document.getElementsByName("Sponsor")[0].value = pstr + " <<?php echo AUTOFILL_SITE; ?>>";
				}
			}
		} else if (opt == 8) {
			var pstr = document.getElementsByName("Depiction")[0].value;
			if (pstr.length != 0) {
				if (pstr.indexOf("http://") == -1) {
					document.getElementsByName("Depiction")[0].value = "http://" + pstr;
				} else {
					document.getElementsByName("Depiction")[0].value = "NULL";
				}
			} else {
				document.getElementsByName("Depiction")[0].value = "<?php echo(base64_decode(DCRM_REPOURL)."/index.php?pid=".$_GET['id']); ?>";
			}
		} else if (opt == 9) {
			if (document.getElementsByName("Description")[0].value.length > 0) {
				changeCase(document.getElementsByName("Description")[0]);
			} else {
				document.getElementsByName("Description")[0].value = "<?php echo AUTOFILL_DESCRIPTION; ?>";
			}
		} else if (opt == 10) {
			document.getElementsByName("NewContents")[0].value = "NULL";
		} else {
			alert("What!?");
		}
		return 0;
	}
	</script>
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
							<li><a href="center.php">管理软件包</a></li>
						<li class="nav-header">REPOSITORY</li>
							<li><a href="sections.php">分类管理</a></li>
							<li><a href="release.php">源信息设置</a></li>
						<li class="nav-header">SYSTEM</li>
							<li><a href="stats.php">运行状态</a></li>
							<li><a href="about.php">关于程序</a></li>
					</ul>
				</div>
				<div class="well sidebar-nav">
					<ul class="nav nav-list">
						<li class="nav-header">OPERATIONS</li>
							<li><a href="javascript:opt(1)">查看详情</a></li>
							<?php
								if (isset($_GET['action']) && ($_GET['action'] == 'advance' || $_GET['action'] == 'advance_set')) {
							?>
							<li><a href="javascript:opt(2)">常规编辑</a></li>
							<li class="active"><a href="javascript:opt(3)">高级编辑</a></li>
							<?php
								} else {
							?>
							<li class="active"><a href="javascript:opt(2)">常规编辑</a></li>
							<li><a href="javascript:opt(3)">高级编辑</a></li>
							<?php
								}
							?>
					</ul>
				</div>
			</div>
			<div class="span10">
			<input type="radio" name="package" value="<?php echo($request_id); ?>" style="display: none;" checked="checked" />
				<?php
					if (!isset($_GET['action']) AND !empty($_GET['id'])) {
						$e_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'",$con);
						if (!$e_query) {
							goto endlabel;
						}
						$edit_info = mysql_fetch_assoc($e_query);
						if (!$edit_info) {
							goto endlabel;
						}
				?>
				<h2>常规编辑</h2>
				<br />
				<form class="form-horizontal" method="POST" action="edit.php?action=set&id=<?php echo $request_id; ?>">
					<fieldset>
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(1);">标识符</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Package" value="<?php if (!empty($edit_info['Package'])) {echo htmlspecialchars($edit_info['Package']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(2);">名称</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Name" value="<?php if (!empty($edit_info['Name'])) {echo htmlspecialchars($edit_info['Name']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(3);">版本</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Version" value="<?php if (!empty($edit_info['Version'])) {echo htmlspecialchars($edit_info['Version']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(4);">作者</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Author" value="<?php if (!empty($edit_info['Author'])) {echo htmlspecialchars($edit_info['Author']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" required="required">* <a href="javascript:autofill(5);">分类</a></label>
							<div class="controls">
								<select name="Section" style="width: 400px;">
								<?php
									$s_query = mysql_query("SELECT `ID`, `Name` FROM `".DCRM_CON_PREFIX."Sections` ORDER BY `ID` ASC",$con);
									if (!$s_query) {
										goto endlabel;
									}
									echo '<option value="' . htmlspecialchars($edit_info['Section']) . '" selected="selected">' . htmlspecialchars($edit_info['Section']) . '</option>';
									while($s_list = mysql_fetch_assoc($s_query)) {
										if ($s_list['Name'] != $edit_info['Section']) {
											echo '<option value="' . htmlspecialchars($s_list['Name']) . '">' . htmlspecialchars($s_list['Name']) . '</option>';
										}
									}
								?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a href="javascript:autofill(6);">提供者</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" name="Maintainer" value="<?php if (!empty($edit_info['Maintainer'])) {echo htmlspecialchars($edit_info['Maintainer']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a href="javascript:autofill(7);">保证人</a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" name="Sponsor" value="<?php if (!empty($edit_info['Sponsor'])) {echo htmlspecialchars($edit_info['Sponsor']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a href="javascript:autofill(8);">预览页</a></label>
							<div class="controls">
								<input id="urlinput" type="text" style="width: 400px;" name="Depiction" value="<?php if (!empty($edit_info['Depiction'])) {echo htmlspecialchars($edit_info['Depiction']);} ?>"/>
								<p class="help-block"><a class="btn btn-warning" href="javascript:jump()">单击此处预览</a></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a href="javascript:autofill(9);">描述</a></label>
							<div class="controls">
								<textarea type="text" style="height: 40px; width: 400px;" name="Description"><?php if (!empty($edit_info['Description'])) {echo htmlspecialchars($edit_info['Description']);} ?></textarea>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">详细描述</label>
							<div class="controls">
								<textarea id="kind" type="text" style="height: 400px; width: 408px; visibility: hidden;" name="Multi"><?php if (!empty($edit_info['Multi'])) {echo htmlspecialchars($edit_info['Multi']);} ?></textarea>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">保存</button>　
							</div>
						</div>
					</fieldset>
				</form>
				<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "set" AND !empty($_GET['id'])) {
						foreach ($_POST AS $p_key => $p_value) {
							$new_key = mysql_real_escape_string($p_key);
							$new_value = mysql_real_escape_string($p_value);
							$new_id = (int)$_GET['id'];
							if (strlen($new_value) >= 1 AND strlen($new_key) >= 1 AND $new_id >= 1) {
								if ($new_value == 'NULL') {
									$new_value = '';
								}
								$new_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `" . $new_key . "` = '" . $new_value . "' WHERE `ID` = '" . $new_id . "'",$con);
								if (!$new_query) {
									goto endlabel;
								}
							}
						}
						echo '<h2>更新数据库</h2><br />';
						echo '<h3 class="alert">软件包信息常规编辑完成！<br />修改带星号的字段后，您必须将其写入安装包才能安全地刷新列表。';
						echo '<br /><a href="output.php?id='.$new_id.'">立即写入</a>　<a href="javascript:history.go(-1);">返回</a></h3>';
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "advance" AND !empty($_GET['id'])) {
						$e_query = mysql_query("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'",$con);
						if (!$e_query) {
							goto endlabel;
						}
						$edit_info = mysql_fetch_assoc($e_query);
						if (!$edit_info) {
							goto endlabel;
						}
						?>
				<h2>高级编辑</h2>
				<br />
				<form class="form-horizontal" method="POST" action="edit.php?action=advance_set&id=<?php echo $request_id; ?>">
					<fieldset>
						<div class="group-control">
							<label class="control-label">* 修改字段</label>
							<div class="controls">
								<input type="hidden" id="item_id" value="<?php echo $request_id; ?>" />
								<select id="item_adv" style="width: 400px;" name="Advance" onChange="javascript:ajax();" >
								<?php
									$z_query = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".DCRM_CON_DATABASE."' and `TABLE_NAME`='Packages' order by COLUMN_NAME");
									while($z_list = mysql_fetch_assoc($z_query)) {
										echo '<option value="'.$z_list['COLUMN_NAME'].'">'.$z_list['COLUMN_NAME'].'</option>';
									}
								?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(10)">修改内容</a></label>
							<div class="controls">
								<textarea type="text" style="height: 200px; width: 400px;" required="required" name="NewContents" id="contents" >iphoneos-arm</textarea>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success">保存</button>　
							</div>
						</div>
					</fieldset>
				</form>
						<?php
					}
					elseif (!empty($_GET['action']) AND $_GET['action'] == "advance_set" AND !empty($_GET['id'])) {
						$a_key = mysql_real_escape_string($_POST['Advance']);
						$a_value = mysql_real_escape_string($_POST['NewContents']);
						$a_id = (int)$_GET['id'];
						if (strlen($a_value) >= 1 AND strlen($a_key) >= 1 AND $a_id >= 1) {
							if ($a_value == 'NULL') {
								$a_value = '';
							}
							$a_query = mysql_query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `" . $a_key . "` = '" . $a_value . "' WHERE `ID` = '" . $a_id . "'",$con);
							if (!$a_query) {
								goto endlabel;
							}
						}
						echo '<h2>更新数据库</h2><br />';
						echo '<h3 class="alert">软件包信息高级编辑完成！<br />修改带星号的字段后，您必须将其写入安装包才能安全地刷新列表。';
						echo '<br /><a href="output.php?id='.$a_id.'">立即写入</a>　<a href="javascript:history.go(-1);">返回</a></h3>';
					}
					if (!$con) {
						endlabel:
						echo '<h3 class="alert alert-error">数据库出现错误！<br /><a href="javascript:history.go(-2);">返回</a></h3>';
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