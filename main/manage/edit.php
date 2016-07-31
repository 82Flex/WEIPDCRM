<?php
/**
 * DCRM Debian Editor
 *
 * This file is part of WEIPDCRM.
 * 
 * WEIPDCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * WEIPDCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start();
define("DCRM", true);
$activeid = 'edit';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	if (is_numeric($_GET['id'])) {
		$request_id = (int)$_GET['id'];
		if ($request_id < 1) {
			_e('Illegal request!');
			exit();
		}
	} else {
		_e('Illegal request!');
		exit();
	}

	require_once("header.php");
?>
			<input type="radio" name="package" value="<?php echo($request_id); ?>" style="display: none;" checked="checked" />
<?php
	if (!isset($_GET['action']) AND !empty($_GET['id'])) {
		$edit_info = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'");
?>
				<h2><?php _e('General Editing'); ?></h2>
				<br />
				<form class="form-horizontal" method="POST" action="edit.php?action=set&id=<?php echo $request_id; ?>">
					<fieldset>
						<div class="group-control">
							<label class="control-label">* <a onclick="javascript:autofill(1);" href="#"><?php _e('Identifier'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Package" value="<?php if (!empty($edit_info['Package'])) {echo htmlspecialchars($edit_info['Package']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a onclick="javascript:autofill(2);" href="#"><?php _e('Name'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Name" value="<?php if (!empty($edit_info['Name'])) {echo htmlspecialchars($edit_info['Name']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a onclick="javascript:autofill(3);" href="#"><?php _e('Version'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Version" value="<?php if (!empty($edit_info['Version'])) {echo htmlspecialchars($edit_info['Version']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a onclick="javascript:autofill(4);" href="#"><?php _e('Author'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" required="required" name="Author" value="<?php if (!empty($edit_info['Author'])) {echo htmlspecialchars($edit_info['Author']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label" required="required">* <a onclick="javascript:autofill(5);" href="#"><?php _e('Section'); ?></a></label>
							<div class="controls">
								<select name="Section" style="width: 400px;">
<?php
		$sections = DB::fetch_all("SELECT `ID`, `Name` FROM `".DCRM_CON_PREFIX."Sections` ORDER BY `ID` ASC");
		echo('<option value="' . htmlspecialchars($edit_info['Section']) . '" selected="selected">' . htmlspecialchars($edit_info['Section']) . '</option>');
		foreach($sections as $section){
			if ($section['Name'] != $edit_info['Section'])
				echo('<option value="' . htmlspecialchars($section['Name']) . '">' . htmlspecialchars($section['Name']) . '</option>');
		}
?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a onclick="javascript:autofill(10);" href="#"><?php _e('Protection'); ?></a></label>
							<div class="controls">
								<select name="Protection" style="width: 400px;" onchange="show_commercial(this.options[this.options.selectedIndex].value)">
									<?php if($protection_status = check_commercial_tag($edit_info['Tag'])) echo '<option value="1"  selected="selected">'.__('Enabled').'</option><option value="0">'.__('Disabled').'</option>';else echo '<option value="0" selected="selected">'.__('Disabled').'</option><option value="1">'.__('Enabled').'</option>' ?>
								</select>
							</div>
						</div>
						<br />
						<div id="commercial" <?php if(!$protection_status) echo 'style="display: none;"';?>>
							<div class="group-control">
								<label class="control-label"><?php _e('Level'); ?></label>
								<div class="controls">
									<input type="number" style="width: 400px;" name="Level" value="<?php echo empty($edit_info['Level']) ? '0' : htmlspecialchars($edit_info['Level']); ?>"/>
									<p class="help-block"><?php _e('Level take precedence over UDID Binding.') ?></p>
								</div>
							</div>
							<br />
						</div>
						<div class="group-control">
							<label class="control-label"><?php _e('Video Preview'); ?></label>
							<div class="controls">
								<input type="text" style="width: 400px;" name="Video_Preview" value="<?php if (!empty($edit_info['Video_Preview']))echo(htmlspecialchars($edit_info['Video_Preview'])); ?>"/>
								<p class="help-block"><?php _e('It will do not display in the page if leave a blank.'); ?></p>
							</div>
						</div>
						<br/>
						<div class="group-control">
							<label class="control-label"><a onclick="javascript:autofill(6);" href="#"><?php _e('Maintainer'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" name="Maintainer" value="<?php if (!empty($edit_info['Maintainer'])) {echo htmlspecialchars($edit_info['Maintainer']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a onclick="javascript:autofill(7);" href="#"><?php _e('Sponsor'); ?></a></label>
							<div class="controls">
								<input type="text" style="width: 400px;" name="Sponsor" value="<?php if (!empty($edit_info['Sponsor'])) {echo htmlspecialchars($edit_info['Sponsor']);} ?>"/>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a onclick="javascript:autofill(8);" href="#"><?php _e('Depiction'); ?></a></label>
							<div class="controls">
<?php
		$repourl = base64_decode(DCRM_REPOURL);
		$repourl = substr($repourl, -1) == '/' ? $repourl : $repourl.'/';
		$rewrite_mod = get_option('rewrite_mod');
		if ($rewrite_mod == 3)
			$depiction_url = $repourl . 'view/' . $request_id;
		else
			$depiction_url = $repourl . 'index.php?pid=' . $request_id;
?>
								<input id="urlinput" type="text" style="width: 400px;" name="Depiction" value="<?php echo !empty($edit_info['Depiction']) ? htmlspecialchars($edit_info['Depiction']) : '' ?>"/>
								<p class="help-block"><?php printf(__('Default Depiction: %s'), $depiction_url); ?></p>
								<p class="help-block"><a class="btn btn-warning" href="<?php echo($depiction_url); ?>" target="_blank"><?php _e('Preview Default Depiction'); ?></a></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><a onclick="javascript:autofill(9);" href="#"><?php _e('Description'); ?></a></label>
							<div class="controls">
								<textarea type="text" style="height: 40px; width: 400px;" name="Description"><?php if (!empty($edit_info['Description'])) {echo htmlspecialchars($edit_info['Description']);} ?></textarea>
							</div>
						</div>
						<br />
						<div>
							<div class="group-control">
								<label class="control-label"><?php _e('Detailed Description'); ?><?php if (DCRM_MULTIINFO != 2){ _e(' (Hidden)'); } ?></label>
								<div class="controls">
									<textarea id="kind" type="text" style="height: 200px; width: 408px; visibility: hidden;" name="Multi"><?php if (!empty($edit_info['Multi'])) {echo htmlspecialchars($edit_info['Multi']);} ?></textarea>
									<?php if (DCRM_MULTIINFO != 2){ ?><p class="help-block"><?php printf(__('It is hidden in the page, you should change \'Detailed Description\' option in <a href="%s">Settings</a> if you want show this.'), './settings.php#multiinfo'); ?></p><?php } ?>
								</div>
							</div>
							<br />
						</div>
						<div class="group-control">
							<label class="control-label"><?php _e('Changelog'); ?></label>
							<div class="controls">
								<textarea id="Changelog" type="text" style="height: 100px; width: 408px; visibility: hidden;" name="Changelog"><?php if (!empty($edit_info['Changelog'])) {echo htmlspecialchars($edit_info['Changelog']);} ?></textarea>
								<p class="help-block"><?php _e('It will do not display in the page if leave a blank.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Old Changelogs'); ?></label>
							<div class="controls">
								<input type="number" style="width: 400px;" name="Changelog_Older_Shows" value="<?php if (!empty($edit_info['Changelog_Older_Shows'])) {echo htmlspecialchars($edit_info['Changelog_Older_Shows']);} ?>"/>
								<p class="help-block"><?php _e('Please fill the number you want to display for old changelog, If leave a blank or zero will only display this version changelog.'); ?></p>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label"><?php _e('Compatibility'); ?></label>
							<div class="controls">
<?php
		if(!empty($edit_info['System_Support'])) $system_support = unserialize($edit_info['System_Support']);
?>
							iOS <input type="text" style="width: 30px;" name="Minimum_System_Support" value="<?php if (!empty($edit_info['System_Support'])) {echo htmlspecialchars($system_support['Minimum']);} ?>"/> ~ iOS <input type="text" style="width: 30px;" name="Maxmum_System_Support" value="<?php if (!empty($edit_info['System_Support'])) {echo htmlspecialchars($system_support['Maxmum']);} ?>"/>
							<p class="help-block"><?php _e('Please fill the iOS system version number, If leave a blank or zero will disable the system compatibility check.'); ?></p>
							
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Save'); ?></button>　
							</div>
						</div>
					</fieldset>
				</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "set" AND !empty($_GET['id'])) {
		$new_id = (int)$_GET['id'];
		$tag = DB::result_first("SELECT `Tag` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $new_id . "'");
		$_POST['Tag'] = string_handle($tag, $_POST['Protection']);
		unset($_POST['Protection']);

		if(!empty($_POST['Minimum_System_Support']))
			$_POST['System_Support'] = serialize(array('Minimum' => $_POST['Minimum_System_Support'], 'Maxmum' => $_POST['Maxmum_System_Support']));
		else
			$_POST['System_Support'] = null;
		unset($_POST['Minimum_System_Support']);
		unset($_POST['Maxmum_System_Support']);

		//	强制转换
		$_POST['Changelog_Older_Shows'] = (int)$_POST['Changelog_Older_Shows'];

		DB::update(DCRM_CON_PREFIX.'Packages', $_POST, array('ID' => $new_id));

		echo '<h2>'.__('Update Database').'</h2><br />';
		echo '<h3 class="alert">'.__('The package information edited!').'<br />'.__('After modify the fields with an asterisk, you must write into package then safely rebuild list.');
		echo '<br /><a href="output.php?id='.$new_id.'">'.__('Write Now').'</a>　<a href="javascript:history.go(-1);">'.__('Back').'</a></h3>';
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "advance" AND !empty($_GET['id'])) {
		$edit_info = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'");
		if (!$edit_info) {
			goto endlabel;
		}
		$protection_status = check_commercial_tag($edit_info['Tag']);
?>
				<h2><?php _e('Advance Editing'); ?></h2>
				<br />
				<form class="form-horizontal" method="POST" action="edit.php?action=advance_set&id=<?php echo $request_id; ?>">
					<fieldset>
						<div class="group-control">
							<label class="control-label">* <?php _ex('Field', 'Advance Editing'); ?></label>
							<div class="controls">
								<input type="hidden" id="item_id" value="<?php echo $request_id; ?>" />
								<select id="item_adv" style="width: 400px;" name="Advance" onChange="javascript:ajax();" >
<?php
		$columns = DB::fetch_all("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='".DCRM_CON_DATABASE."' and `TABLE_NAME`='".DCRM_CON_PREFIX."Packages' order by COLUMN_NAME");
		foreach($columns as $column) {
			echo('<option value="'.$column['COLUMN_NAME'].'">'.$column['COLUMN_NAME'].'</option>');
		}
?>
								</select>
							</div>
						</div>
						<br />
						<div class="group-control">
							<label class="control-label">* <a href="javascript:autofill(10)"><?php _ex('Content', 'Advance Editing'); ?></a></label>
							<div class="controls">
								<textarea type="text" style="height: 200px; width: 400px;" required="required" name="NewContents" id="contents" >iphoneos-arm</textarea>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Save'); ?></button>　
							</div>
						</div>
					</fieldset>
				</form>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "advance_set" AND !empty($_GET['id'])) {
		$a_key = DB::real_escape_string($_POST['Advance']);
		$a_value = DB::real_escape_string($_POST['NewContents']);
		$a_id = (int)$_GET['id'];
		if (strlen($a_value) >= 1 AND strlen($a_key) >= 1 AND $a_id >= 1) {
			if ($a_value == 'NULL') {
				$a_value = '';
			}
			DB::update(DCRM_CON_PREFIX.'Packages', array($a_key => $a_value), array('ID' => $a_id));
		}
		echo '<h2>'.__('Update Database').'</h2><br />';
		echo '<h3 class="alert">'.__('The package information edited!').'<br />'.__('After modify the fields with an asterisk, you must write into package then safely rebuild list.');
		echo '<br /><a href="output.php?id='.$a_id.'">'.__('Write Now').'</a>　<a href="javascript:history.go(-1);">'.__('Back').'</a></h3>';
	}
	endlabel:
?>
			</div>
		</div>
	</div>
	</div>
	<script charset="utf-8" src="./plugins/kindeditor/kindeditor.min.js"></script>
	<script charset="utf-8" src="./plugins/kindeditor/lang/<?php echo $kdlang = check_languages(array($locale), 'kind');?>.js"></script>
	<script type="text/javascript">
	<?php if($protection_status): ?>
	sli = document.getElementById('sli');
	sli.innerHTML = '<a href="udid.php?package=<?php echo htmlspecialchars($edit_info['Package']);?>"><?php _e('Binding UDID');?></a>';
	<?php endif; ?>
	KindEditor.ready(function(K) {
		K.each({
			'plug-align' : {
				name : '<?php _e('Align'); ?>',
				method : {
					'justifyleft' : '<?php _ex('Left', 'Align'); ?>',
					'justifycenter' : '<?php _ex('Center', 'Align'); ?>',
					'justifyright' : '<?php _ex('Right', 'Align'); ?>'
				}
			},
			'plug-order' : {
				name : '<?php _ex('List', 'Edit'); ?>',
				method : {
					'insertorderedlist' : '<?php _e('Ordered list'); ?>',
					'insertunorderedlist' : '<?php _e('Unordered list'); ?>'
				}
			},
			'plug-indent' : {
				name : '<?php _e('Indent'); ?>',
				method : {
					'indent' : '<?php _e('Increase indent'); ?>',
					'outdent' : '<?php _e('Decrease indent'); ?>'
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
			langType : '<?php echo $kdlang; ?>',
			themeType : 'qq',
			items : [
				'bold','italic','underline','fontname','fontsize','forecolor','hilitecolor','plug-align','plug-order','plug-indent','link','removeformat','|','source'
			]
		});
		K.create('#Changelog', {
			langType : '<?php echo $kdlang; ?>',
			themeType : 'qq',
			newlineTag : 'br',
			items : [
				'bold','italic','underline','fontname','fontsize','forecolor','hilitecolor','plug-align','plug-order','plug-indent','link','removeformat','|','source'
			]
		});
	});
	function jump() {
		var input = document.getElementById("urlinput");
		window.open(input.value,"_blank");
		return 0;
	}
	function ajax() {
		document.getElementById("contents").innerHTML="<?php _e('Data loading'); ?>";
		xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST","hint.php",true);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("action=adv_info&item=" + document.getElementById("item_id").value + "&col=" + document.getElementById("item_adv").value);
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4) {
				if (xmlhttp.status == 200) {
					document.getElementById("contents").innerHTML=xmlhttp.responseText;
				} else {
					document.getElementById("contents").innerHTML="<?php _e('Database Error'); ?>";
				}
				xmlhttp.close();
			}
		}
	}
	function show_commercial(stat) {
		commercial = document.getElementById('commercial');
		sli = document.getElementById('sli');
		if (stat == 1) {
			commercial.style.display = "";
			sli.innerHTML = '<a href="udid.php?package=<?php echo htmlspecialchars($edit_info['Package']);?>"><?php _e('Binding UDID');?></a>';
		} else {
			commercial.style.display = "none";
			sli.innerHTML = '';
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
				document.getElementsByName("Section")[0].value = "<?php echo defined("AUTOFILL_SECTION") ? AUTOFILL_SECTION : ''; ?>";
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
					document.getElementsByName("Depiction")[0].value = "";
				}
			} else {
				document.getElementsByName("Depiction")[0].value = "<?php echo($depiction_url); ?>";
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
</body>
</html>
<?php
} else {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
?>