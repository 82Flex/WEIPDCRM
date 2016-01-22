<?php
/**
 * DCRM Debian List
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
define("DCRM",true);
$activeid = 'center';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");
	class_loader('CorePage');

	if (!isset($_GET['action'])) {
		if (isset($_GET['search'])) {
?>
				<h2><?php _e('Manage Packages'); ?></h2>
				<br />
				<h3 class="navbar"><span><a href="center.php"><?php _e('All Packages'); ?></a></span>　<span><?php _e('Search Packages'); ?></span></h3>
					<br />
					<form class="form-horizontal" method="GET" action="center.php" >
					<div class="group-control">
						<label class="control-label"><?php _e('Search Content'); ?></label>
						<div class="controls">
							<input type="hidden" name="action" value="search" />
							<input class="input-xlarge" name="contents" required="required" />
						</div>
					</div>
					<br />
					<div class="group-control">
						<label class="control-label"><?php _e('Search Type'); ?></label>
						<div class="controls">
							<select name="type" >
							<option value="1" selected="selected"><?php _e('Identifier'); ?></option>
							<option value="2"><?php _e('Name'); ?></option>
							<option value="3"><?php _e('Author'); ?></option>
							<option value="4"><?php _e('Description'); ?></option>
							<option value="5"><?php _e('Maintainer'); ?></option>
							<option value="6"><?php _e('Sponsor'); ?></option>
							<option value="7"><?php _e('Section'); ?></option>
							<option value="8"><?php _e('Tag'); ?></option>
							</select>
						</div>
					</div>
					<br />
					<div class="form-actions">
						<div class="controls">
							<button type="submit" class="btn btn-success"><?php _e('Search'); ?></button>
						</div>
					</div>
					</form>
				<br />
<?php
		} else {
?>
				<h2><?php _e('Manage Packages'); ?></h2>
				<br />
				<h3 class="navbar"><span><?php _e('All Packages'); ?></span>　<span><a href="center.php?search=yes"><?php _e('Search Packages'); ?></a></span></h3>
<?php
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
			} elseif (isset($_SESSION['page'])) {
				$page = $_SESSION['page'];
			} else {
				$page = 1;
			}
			if ($page <= 0 OR $page >= 100) {
				$page = 1;
			}
			unset($_SESSION['contents']);
			unset($_SESSION['type']);
			$_SESSION['page'] = $page;
			$row_start = $page * 10 - 10;
			$lists = DB::fetch_all("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat`, `Size`, `Section` FROM `".DCRM_CON_PREFIX."Packages` ORDER BY `Stat` DESC, `ID` DESC, `Version` DESC, `Name` DESC LIMIT " . (string)$row_start. ",10");
?>
								<table class="table"><thead><tr>
									<th style="width:13px;"></th>
									<th style="width:13px;"><ul class="ctl">ID</ul></th>
									<th><ul class="ctl"><?php _e('Name'); ?></ul></th>
									<th style="width:20%;"><ul class="ctl"><?php _e('Version'); ?></ul></th>
									<th style="width:20%;"><ul class="ctl"><?php _e('Size'); ?></ul></th>
									<th style="width:10%;"><ul class="ctl"><?php _e('Downloads'); ?></ul></th>
									<th style="width:5%;"><ul class="ctl"><?php _e('Delete'); ?></ul></th>
									<th style="width:5%;"><ul class="ctl"><?php _e('History'); ?></ul></th>
								</tr></thead><tbody>
<?php
			foreach ($lists as $list) {
?>
								<tr>
									<td height="20"><input type="radio" name="package" value="<?php echo($list['ID']); ?>" onclick="javascript:show(<?php echo $list['Stat']; ?>);" /></td>
									<td height="20"><ul class="ctl"><?php echo($list['ID']); ?></ul></td>
<?php
				if (empty($list['Name']))
					$list['Name'] = AUTOFILL_NONAME;
				$color = array(-1 => 'gray', 1 => '#08C', 2 => 'green', 3 => 'yellow');
?>
									<td><a href = "view.php?id=<?php echo($list['ID']); ?>"><ul class="ctl" style="color: <?php echo($color[$list['Stat']]); ?>;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
									<td><ul class="ctl"><?php echo(htmlspecialchars($list['Version'])); ?></ul></td>
									<td><ul class="ctl"><?php echo(sizeext($list['Size'])); ?></ul></td>
									<td><ul class="ctl"><?php echo($list['DownloadTimes']); ?></ul></td>
									<td><a href="center.php?action=delete_confirm&name=<?php echo($list['Package']); ?>&id=<?php echo $list['ID']; ?>" class="close">&times;</a></td>
									<td><a href="center.php?action=search&contents=<?php echo($list['Package']); ?>&type=1" class="close">&raquo;</a></td>
								</tr>
<?php
			}
?>
								</tbody></table>
<?php
			$packages_count = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages`");
			$params = array('total_rows' => (int)$packages_count, 'method' => 'html', 'parameter' => 'center.php?page=%page', 'now_page'  => $page, 'list_rows' => 10);
			$page = new Core_Lib_Page($params);
			echo('<div class="page">' . $page->show(2) . '</div>');
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "search" AND !empty($_GET['contents']) AND !empty($_GET['type'])) {
		unset($_SESSION['page']);
		$_SESSION['contents'] = $_GET['contents'];
		$_SESSION['type'] = $_GET['type'];
		if (isset($_GET['page'])) {
			$page = (int)$_GET['page'];
		} else {
			$page = 1;
		}
		if ($page <= 0 OR $page >= 100) {
			$page = 1;
		}
		$row_start = $page * 10 - 10;
?>
				<h2><?php _e('Manage Packages'); ?></h2>
				<br />
				<h3 class="navbar"><?php $contents = isset($_GET['udid']) ? __('Protection Packages') : $_GET['contents']; printf(__('Search Packages: %s'), $contents); ?></h3>
<?php
		$search_type = (int)$_GET['type'];
		$query_type = array('', 'Package', 'Name', 'Author', 'Description', 'Maintainer', 'Sponsor', 'Section', 'Tag');

		if(isset($query_type[$search_type])){
			$r_value = DB::real_escape_string(str_replace('*', '%', str_replace('?', '_', $_GET['contents'])));
			$lists = DB::fetch_all("SELECT `ID`, `Package`, `Name`, `Version`, `DownloadTimes`, `Stat`, `Size` FROM `".DCRM_CON_PREFIX."Packages` WHERE `" . $query_type[$search_type] . "` LIKE '%" . $r_value . "%' ORDER BY `Stat` DESC, `ID` DESC LIMIT ".(string)$row_start.",10");
?>
								<table class="table"><thead><tr>
									<th style="width:13px;"></th>
									<th><ul class="ctl"><?php _e('Name'); ?></ul></th>
									<th style="width:20%;"><ul class="ctl"><?php _e('Version'); ?></ul></th>
									<th style="width:20%;"><ul class="ctl"><?php _e('Size'); ?></ul></th>
									<th style="width:10%;"><ul class="ctl"><?php _e('Downloads'); ?></ul></th>
									<th style="width:5%;"><ul class="ctl"><?php _e('Delete'); ?></ul></th>
									<th style="width:5%;"><ul class="ctl"><?php _e('History'); ?></ul></th>
<?php 		if(isset($_GET['udid'])) { ?><th style="width:5%;"><ul class="ctl"><?php _e('Binding'); ?></ul></th><?php } ?>
								</tr></thead><tbody>
<?php
			if(isset($_GET['udid'])) {
				$udid_query = DB::result_first("SELECT `Packages` FROM `".DCRM_CON_PREFIX."UDID` WHERE `UDID` = '" . $_GET['udid'] . "'");
				$packages_udid = TrimArray(explode(',', $udid_query));
			}
			foreach ($lists as $list) {
?>
								<tr>
									<td height="20"><input type="radio" name="package" value="<?php echo($list['ID']); ?>" onclick="javascript:show(<?php echo $list['Stat']; ?>);" /></td>
<?php
				if (empty($list['Name']))
					$list['Name'] = AUTOFILL_NONAME;
				$color = array(-1 => 'gray', 1 => '#08C', 2 => 'green', 3 => 'yellow');
?>
									<td><a href = "view.php?id=<?php echo($list['ID']); ?>"><ul class="ctl" style="color: <?php echo($color[$list['Stat']]); ?>;"><?php echo htmlspecialchars($list['Name']); ?></ul></a></td>
									<td><ul class="ctl"><?php echo(htmlspecialchars($list['Version'])); ?></ul></td>
									<td><ul class="ctl"><?php echo(sizeext($list['Size'])); ?></ul></td>
									<td><ul class="ctl"><?php echo($list['DownloadTimes']); ?></ul></td>
									<td><a href="center.php?action=delete_confirm&name=<?php echo($list['Package']); ?>&id=<?php echo $list['ID']; ?>" class="close">&times;</a></td>
									<td><a href="center.php?action=search&contents=<?php echo($list['Package']); ?>&type=1" class="close">&raquo;</a></td>
<?php
				if(isset($_GET['udid'])) {
					if(in_array($list['Package'], $packages_udid, true)) {
?>
									<td><a href="udid.php?action=binding&contents=<?php echo($list['Package']); ?>&amp;udid=<?php echo $_GET['udid']; ?>&amp;delete=true" class="close" title="<?php _e('Delete'); ?>">&times;</a></td>
<?php				} else { ?>
									<td><a href="udid.php?action=binding&contents=<?php echo($list['Package']); ?>&amp;udid=<?php echo $_GET['udid']; ?>" class="close" title="<?php _e('Binding'); ?>">※</a></td>
<?php
					}
				}
?>
								</tr>
<?php
			}
?>
								</tbody></table>
<?php
			$packages_count = DB::result_first("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `" . $query_type[$search_type] . "` LIKE '%" . $r_value . "%'");
			$params = array('total_rows' => (int)$packages_count, 'method' => 'html', 'parameter' => 'center.php?action=search&contents='.$_GET['contents'].'&type='.$_GET['type'].'&page=%page', 'now_page'  => $page, 'list_rows' => 10);
			$page = new Core_Lib_Page($params);
			echo('<div class="page">' . $page->show(2) . '</div>');
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete_confirm" AND !empty($_GET['name']) AND !empty($_GET['id'])) {
?>
						<h3 class="alert"><?php printf(__('Are you sure you want to delete: %s?'), htmlspecialchars($_GET['name']));?><br /><?php _e('This operation is irreversible, all related data will be delete!'); ?></h3>
						<a class="btn btn-danger" href="center.php?action=delete&id=<?php echo($_GET['id']); ?>"><?php _e('Delete'); ?></a>　
						<a class="btn btn-warning" href="center.php?action=submit&id=<?php echo($_GET['id']); ?>"><?php _e('Hide'); ?></a>　
<?php
		echo('<a class="btn btn-success" href="center.php?');
		if (!empty($_SESSION['page'])) {
			echo("page=" . $_SESSION['page']);
		} elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
			echo("action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
		}
		echo('">'.__('Cancel').'</a>');
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "delete" AND !empty($_GET['id'])) {
		$delete_id = (int)$_GET['id'];
		$f_filename = DB::result_first("SELECT `Filename` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $delete_id . "'");

		unlink($f_filename);
		DB::delete(DCRM_CON_PREFIX.'Packages', array('ID' => $delete_id));
		DB::delete(DCRM_CON_PREFIX.'Reports', array('PID' => $delete_id));
		if (!empty($_SESSION['page'])) {
			header("Location: center.php?page=" . $_SESSION['page']);
			exit();
		} elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
			header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
			exit();
		} else {
			header("Location: center.php");
			exit();
		}
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "submit" AND !empty($_GET['id'])) {
		$submit_id = (int)$_GET['id'];
		$s_info = DB::fetch_first("SELECT `Package`, `Stat` FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $submit_id . "'");
		if ((int)$s_info['Stat'] != 1) {
			//$s_query = DB::query("UPDATE `".DCRM_CON_PREFIX."Packages` SET `Stat` = '-1' WHERE `Package` = '" . $s_info['Package'] . "'");
			DB::update(DCRM_CON_PREFIX.'Packages', array('Stat' => '1'), array('ID' => $submit_id));
		} else {
			DB::update(DCRM_CON_PREFIX.'Packages', array('Stat' => '-1'), array('ID' => $submit_id));
		}
		if (!empty($_SESSION['page'])) {
			header("Location: center.php?page=" . $_SESSION['page']);
			exit();
		} elseif (!empty($_SESSION['contents']) AND !empty($_SESSION['type'])) {
			header("Location: center.php?action=search&contents=" . $_SESSION['contents'] . "&type=" . $_SESSION['type']);
			exit();
		} else {
			header("Location: center.php");
			exit();
		}
	}
?>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
	function show(stat) {
		sli = document.getElementById('sli');
		
		if (stat == 1) {
			sli.innerHTML = '<a href="javascript:opt(4)"><?php _e('Hide Package'); ?></a>';
		} else {
			sli.innerHTML = '<a href="javascript:opt(5)"><?php _e('Display Package'); ?></a>';
		}
		document.getElementById('mbar').style.display = "";
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