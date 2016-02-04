<?php
/**
 * DCRM Section Manage
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
$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';
class_loader('tar');
class_loader('CorePage');
$activeid = 'sections';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {
?>
				<h2><?php _e('Manage Sections'); ?></h2>
				<br />
				<h3 class="navbar"><span><?php _e('Sections List'); ?></span>　<span><a href="sections.php?action=add"><?php _e('Add Section'); ?></a></span>　<span><a href="sections.php?action=create"><?php _e('Create Icon Package'); ?></a></span></h3>
<?php
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			$page = 1;
		}
		if ($page <= 0 OR $page >= 6) {
			$page = 1;
		}
		$page_a = $page * 10 - 10;
		if ($page == 1) {
			$page_b = $page;
		} else {
			$page_b = $page - 1;
		}
		$list = DB::fetch_all("SELECT * FROM `".DCRM_CON_PREFIX."Sections` ORDER BY `ID` DESC LIMIT ".(string)$page_a.",10");
?>
					<table class="table"><thead><tr>
					<th><ul class="ctl"><?php _e('Delete'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Edit'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Name'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Icon'); ?></ul></th>
					<th><ul class="ctl"><?php _e('Last Change'); ?></ul></th>
					</tr></thead><tbody>
<?php
		if(!empty($list)){
			foreach($list as $section) {
?>
					<tr>
					<td><a href="sections.php?action=delete_confirmation&amp;id=<?php echo($section['ID']); ?>&amp;name=<?php echo($section['Name']); ?>" class="close" style="line-height: 12px;">&times;</a></td>
					<td><a href="sections.php?action=edit&amp;id=<?php echo($section['ID']); ?>" class="close">✎</a></td>
					<td><ul class="ctl" style="width:400px;"><a title="<?php _e('Click to view packages in this section.'); ?>" href="center.php?action=search&amp;contents=<?php echo(urlencode($section['Name'])); ?>&amp;type=7"><?php echo(htmlspecialchars($section['Name'])); ?></a></ul></td>
<?php
				if ($section['Icon'] != '') {
?>
					<td><ul class="ctl" style="width:150px;"><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>/icon/<?php echo($section['Icon']); ?>"><?php echo($section['Icon']); ?></a></ul></td>
<?php
				} else {
?>
					<td><ul class="ctl" style="width:150px;"><?php _e('No Icon'); ?></ul></td>
<?php
				}
?>
					<td><ul class="ctl" style="width:150px;"><?php echo($section['TimeStamp']); ?></ul></td>
					</tr>
<?php
			}
?>
					</tbody></table>
<?php
			$totalnum = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
			$params = array('total_rows'=>$totalnum, 'method'=>'html', 'parameter' =>'sections.php?page=%page', 'now_page'  =>$page, 'list_rows' =>10);
			$page = new Core_Lib_Page($params);
			echo '<div class="page">' . $page->show(2) . '</div>';
		}
	} elseif (!empty($_GET['action']) AND ($_GET['action'] == "add" || $_GET['action'] == "edit")) {
		// 获取编辑信息
		if($_GET['action'] == "edit"){
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$request_id = (int)$_GET['id'];
				if ($request_id < 1) {
					_e('Illegal request!');
					goto endlabel;
				}
			} else {
				_e('Illegal request!');
				goto endlabel;
			}
			$edit_info = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Sections` WHERE `ID` = '" . $request_id . "'");
			if (null == $edit_info) {
				_e('Illegal request!');
				goto endlabel;
			}
		}
?>
						<h2><?php _e('Manage Sections'); ?></h2>
						<br />
						<h3 class="navbar"><span><a href="sections.php"><?php _e('Sections List'); ?></a></span>　<span><?php $_GET['action'] == "edit" ? _e('Edit Section') : _e('Add Section'); ?></span>　<span><a href="sections.php?action=create"><?php _e('Create Icon Package'); ?></a></span></h3>
						<br />
						<form class="form-horizontal" method="POST" enctype="multipart/form-data" action="sections.php?action=add_now" >
						<div class="group-control">
							<label class="control-label"><?php _e('Section Name'); ?></label>
							<div class="controls">
								<input class="input-xlarge" name="contents" required="required"  value="<?php if (!empty($edit_info['Name'])) echo $edit_info['Name']; ?>"/>
							</div>
						</div>
						<br/>
						<div class="group-control">
							<label class="control-label"><?php _e('Section Icon'); ?></label>
							<div class="controls">
								<input type="file" class="span6" name="icon" accept="image/x-png" />
								<p class="help-block">
<?php
		if($_GET['action'] == "edit"){
			if ($edit_info['Icon'] != "") {
				printf(__('The current icon is %s.'), "<a href='".base64_decode(DCRM_REPOURL)."/icon/{$edit_info['Icon']}'>{$edit_info['Icon']}</a>");
				echo('<br/>');
				printf(__('You can click <a href="%s">Here</a> to delete current icon.'), "sections.php?action=delete_icon&amp;id={$edit_info['ID']}&amp;name={$edit_info['Name']}");
				echo("<input type='hidden' name='exist_icon' value='{$edit_info['Icon']}' />");
			} else {
				_e('There are currently no icon.');
			}
			echo("<input type='hidden' name='id' value='{$edit_info['ID']}' />");
		}
?>
								</p>
							</div>
						</div>
						<br />
						<div class="form-actions">
							<div class="controls">
								<button type="submit" class="btn btn-success"><?php _e('Submit'); ?></button>
							</div>
						</div>
						</form>
						<h3><?php _e('Tips'); ?></h3>
						<br />
						<h4 class="alert alert-info">
							· <?php printf(__('Allowed upload format: png, save in %s directory of root directory.'), 'icon'); ?>
							<?php if($_GET['action'] == "edit"){ ?><br/>· <?php _e('If you change the section name, you may need to change section option in package infomation for many packages.'); ?><?php } ?>
						</h4>
<?php 
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "add_now" AND !empty($_POST['contents'])) {
		$new_name = DB::real_escape_string($_POST['contents']);
		$num = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
		if ($num <= 50) {
			if (pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION) == "png") {
				if (file_exists("../icon/" . $_FILES['icon']['name'])) {
					unlink("../icon/" . $_FILES['icon']['name']);
				}
				$move = rename($_FILES['icon']['tmp_name'],"../icon/" . $_FILES['icon']['name']);
				if (!$move) {
					$alert = __('Upload failed, please check the file permissions.');
					goto endlabel;
				} else {
					if(isset($_POST['id'])){
						$n_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Sections` SET `Name` = '{$new_name}', `Icon` = '{$_FILES['icon']['name']}' WHERE `ID` = ".$_POST['id']);
					} else {
						$n_query = DB::query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`, `Icon`) VALUES('" . $new_name . "', '" . $_FILES['icon']['name'] . "')");
					}
				}
			} else {
				if(isset($_POST['id'])){
					if(isset($_POST['exist_icon'])){
						$n_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Sections` SET `Name` = '{$new_name}', `Icon` = '{$_POST['exist_icon']}' WHERE `ID` = ".$_POST['id']);
					} else {
						$n_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Sections` SET `Name` = '{$new_name}' WHERE `ID` = ".$_POST['id']);
					}
				} else {
					$n_query = DB::query("INSERT INTO `".DCRM_CON_PREFIX."Sections`(`Name`) VALUES('" . $new_name . "')");
				}
			}
		} else {
			$alert = __('You can add a maximum of 50 sections!');
			goto endlabel;
		}
		if (!$n_query) {
			goto endlabel;
		} else {
			header("Location: sections.php");
			exit();
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "create") {
		if (defined("AUTOFILL_SEO") && defined("AUTOFILL_PRE")) {
			$alert = sprintf(__('Are you sure want to create the %s icon package?'), AUTOFILL_SEO) . '<br /><a href="sections.php?action=createnow">'.__('Create Now').'</a>';
			$alert_tag = 'alert-success';
		} else {
			$alert = __('You have not filled in SEO and autofill information, unable to use this function!');
		}
		goto endlabel;
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "createnow") {
		$new_name = DB::real_escape_string($_POST['contents']);
		$num = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections` WHERE `Icon` != ''");
		if ($num < 1) {
			$alert = __('Cannot find any existing icon of section, please add an icon first, then create icon package.');
			goto endlabel;
		}
		if (file_exists(CONF_PATH.'empty_icon.deb')) {
			$r_id = randstr(40);
			if (!is_dir("../tmp/")) {
				$result = mkdir("../tmp/");
			}
			if (!is_dir("../tmp/" . $r_id)) {
				$result = mkdir("../tmp/" . $r_id);
				if (!$result) {
					$alert = __('Cannot create temporary directory, please check the file permissions!');
					goto endlabel;
				}
			}
			$deb_path = "../tmp/" . $r_id . "/icon_" . time() . ".deb";
			$result = copy(CONF_PATH.'empty_icon.deb', $deb_path);
			if (!$result) {
				$alert = __('Icon package template copy failed, please check the file permissions!');
				goto endlabel;
			}
			$raw_data = new phpAr($deb_path);
			$new_tar = new Tar();
			$new_path = "../tmp/" . $r_id . "/data.tar.gz";
			$icon_query = DB::fetch_all("SELECT * FROM `".DCRM_CON_PREFIX."Sections`");
			mkdir("../tmp/" . $r_id . "/Applications");
			mkdir("../tmp/" . $r_id . "/Applications/Cydia.app");
			mkdir("../tmp/" . $r_id . "/Applications/Cydia.app/Sections");
			foreach($icon_query as $icon_assoc){
				if ($icon_assoc['Icon'] != "") {
					// Compatible with earlier Cydia version and special situations
					$new_filename = $new_filenames[] = str_replace(" ", "_", $icon_assoc['Name']) . '.png';
					if(substr($new_filename, 0, 1) == '[' && substr($new_filename, -5, -4) == ']')
						$new_filenames[] = substr($new_filename, 1, -5) . '.png';
					$new_filepath = "../tmp/" . $r_id . "/Applications/Cydia.app/Sections/" . $new_filename;
					copy("../icon/" . $icon_assoc['Icon'], $new_filepath);
					chmod($new_filepath, 0755);
					foreach($new_filenames as $filename){
						$new_tar -> add_file("/Applications/Cydia.app/Sections/" . $filename, 0755, file_get_contents($new_filepath));
					}
				}
			}
			$new_tar -> save($new_path);
			$result = $raw_data -> replace("data.tar.gz", $new_path);

			if (!$result) {
				$alert = __('Icon package template rewriting failed!');
				goto endlabel;
			} else {
				$control_path = "../tmp/" . $r_id . "/control.tar.gz";
				$control_tar = new Tar();
				$f_Package = "Package: ".(defined("AUTOFILL_PRE") ? AUTOFILL_PRE : '')."sourceicon\nArchitecture: iphoneos-arm\nName: Source Icon\nVersion: 0.1-1\nAuthor: ".(defined("AUTOFILL_SEO")?AUTOFILL_SEO.(defined("AUTOFILL_EMAIL")?' <'.AUTOFILL_EMAIL.'>':''):'DCRM <i.82@me.com>')."\nSponsor: ".(defined("AUTOFILL_MASTER")?AUTOFILL_MASTER.(defined("AUTOFILL_EMAIL")?' <'.AUTOFILL_EMAIL.'>':''):'i_82 <http://82flex.com>')."\nMaintainer: ".(defined("AUTOFILL_MASTER")?AUTOFILL_MASTER.(defined("AUTOFILL_EMAIL")?' <'.AUTOFILL_EMAIL.'>':''):'i_82 <http://82flex.com>')."\nSection: Repositories\nDescription: Custom Empty Source Icon Package\n";
				$control_tar -> add_file("control", "", $f_Package);
				$control_tar -> save($control_path);
				$result = $raw_data -> replace("control.tar.gz", $control_path);
				if (!$result) {
					$alert = __('Icon package template rewriting failed!');
					goto endlabel;
				} else {
					$result = rename($deb_path, "../upload/" . AUTOFILL_PRE . "sourceicon_" . time() . ".deb");
					if (!$result) {
						$alert = __('Icon package template repositioning failed!');
						goto endlabel;
					}
					header("Location: manage.php");
					exit();
				}
			}
		} else {
			$alert = __('Icon package template missing, please upload DCRM Pro again!');
			goto endlabel;
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirmation" AND !empty($_GET['id']) AND !empty($_GET['name'])) {
?>
						<h3 class="alert"><?php printf(__('Are you sure delete: %s ?'), htmlspecialchars($_GET['name'])); ?></h3>
						<a class="btn btn-warning" href="sections.php?action=delete&amp;id=<?php echo($_GET['id']); ?>"><?php _e('Confirm'); ?></a>　
						<a class="btn btn-success" href="sections.php"><?php _e('Cancel'); ?></a>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
		$delete_id = (int)$_GET['id'];
		DB::delete(DCRM_CON_PREFIX.'Sections', array('ID' => $delete_id));
		header("Location: sections.php");
		exit();
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_icon" AND !empty($_GET['id'])){
		if(isset($_GET['delete_now'])){
			$delete_id = (int)$_GET['id'];
			$n_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Sections` SET `Icon` = '' WHERE `ID` = {$delete_id}");
			header("Location: sections.php?action=edit&id={$_GET['id']}");
			exit();
		}
		if(!empty($_GET['name'])){
?>
						<h3 class="alert"><?php printf(__('Are you sure delete the section icon for %s ?'), htmlspecialchars($_GET['name'])); ?></h3>
						<a class="btn btn-warning" href="sections.php?action=delete_icon&amp;id=<?php echo($_GET['id']); ?>&amp;delete_now=true"><?php _e('Confirm'); ?></a>　
						<a class="btn btn-success" href="sections.php"><?php _e('Cancel'); ?></a>
<?php
		}
	}
	endlabel:
	if (isset($alert))
		echo '<h3 class="alert '.(isset($alert_tag) ? $alert_tag : 'alert-error').'">'.$alert.'<br /><a href="sections.php">'.__('Back').'</a></h3>';
?>
			</div>
		</div>
	</div>
	</div>
</body>
</html>
<?php
} else {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
?>