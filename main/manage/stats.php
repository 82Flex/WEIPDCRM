<?php
/**
 * DCRM Statistics
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
$activeid = 'stats';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
	require_once("header.php");

	if (!isset($_GET['action'])) {
				?>
				<h2><?php _e('Running Status'); ?></h2>
				<br />
				<div class="wrapper">
					<ul class="breadcrumb" onclick="wrapper('triangle_mysql','item_mysql'); return false;"><i class="icon" id="triangle_mysql">▼</i>&nbsp;<?php _e('Database Status'); ?></ul>
					<div class="item" style="display:block;" id="item_mysql">
						<?php echo nl2br(htmlspecialchars(str_replace("  ","\n\t\t\t\t\t\t",DB::stat())))."\n"; ?>
					</div>
				</div>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="wrapper('triangle_server','item_server'); return false;"><i class="icon" id="triangle_server">▼</i>&nbsp;<?php _e('Service Information'); ?></ul>
					<div class="item" style="display:block;" id="item_server">
<?php
		if(function_exists("gd_info")){                  
			$gd_info = gd_info();
			$gd_version = $gd_info['GD Version'];
		} else {
			$gd_version = "Unknown";
		}
		$max_upload = ini_get("file_uploads") ? ini_get("upload_max_filesize") : "Disabled";
		date_default_timezone_set("Etc/GMT-8");
		$system_time = date("Y-m-d H:i:s",time());
		$content = "\t\t\t\t\t\t".__('Server information: ') . $_SERVER["SERVER_SOFTWARE"] . '<br />'.__('Database version: ') . htmlspecialchars(DB::version()) . '<br />'.__('GD library version: ') . htmlspecialchars($gd_version) . '<br />'.__('Upload max filesize: ') . htmlspecialchars($max_upload) . '<br />'.__('Max execution time: ') . sprintf(_n('%d second', '%d seconds', $max_execution_time = ini_get("max_execution_time")), htmlspecialchars($max_execution_time) ).'<br />'.__('Server time: ') . htmlspecialchars($system_time) . "\n";
		echo $content;
?>
					</div>
				</div>
				<div class="wrapper">
					<ul class="breadcrumb" onclick="wrapper('triangle_manage','item_manage'); return false;"><i class="icon" id="triangle_manage">▼</i>&nbsp;<?php _e('Management Statistics'); ?></ul>
					<div class="item" style="display:block;" id="item_manage">
<?php
		$q_info = DB::query("SELECT sum(`DownloadTimes`) FROM `".DCRM_CON_PREFIX."Packages`");
		$info = DB::fetch_row($q_info);
		$totalDownloads = (int)$info[0];
		$q_info = DB::query("SELECT sum(`Size`) FROM `".DCRM_CON_PREFIX."Packages`");
		$info = DB::fetch_row($q_info);
		$poolSize = (int)$info[0];
		$poolSize_withext = sizeext($poolSize);
		$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages`");
		$info = DB::fetch_row($q_info);
		$num[0] = (int)$info[0];
		$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` != '-1'");
		$info = DB::fetch_row($q_info);
		$num[1] = (int)$info[0];
		$q_info = DB::query("SELECT count(*) FROM `".DCRM_CON_PREFIX."Sections`");
		$info = DB::fetch_row($q_info);
		$num[2] = (int)$info[0];
		$tmpSize = dirsize("../tmp");
		$tmpSize_withext = sizeext($tmpSize);
		$content = "\t\t\t\t\t\t".__('Total download times: ') . $totalDownloads . '<br />'.__('Number of packages: ') . $num[0] . '<br />'.__('Number of non-hidden packages: ') . $num[1] . '<br />'.__('Number of sections: ') . $num[2] . '<br />'.__('Download pool size: ') . $poolSize_withext . '<br /><span>'.__('Cache pool size: ') . $tmpSize_withext . '</span>　<span><a href="stats.php?action=clean">'.__('Clean cache')."</span></a>\n";
		echo $content;
?>
						<br />
						<!-- Statistics Start -->
<?php 
		if (defined("AUTOFILL_STATISTICS_INFO")) {
			echo "\t\t\t\t\t\t".AUTOFILL_STATISTICS_INFO."\n";
		}
?>
						<!-- Statistics End -->
					</div>
				</div>
<?php
	} elseif (!empty($_GET['action']) AND $_GET['action'] == "clean") {
		deldir("../tmp");
		mkdir("../tmp");
		echo '<h2>'.__('Clean Cache').'</h2><br />';
		echo '<h3 class="alert alert-success">'.__('Cache cleanup completed!').'<br />';
		echo '<a href="center.php">'.__('Back').'</a></h3>';
	} else {
		endlabel:
		echo $alert;
	}
?>
			</div>
		</div>
	</div>
	</div>
	<script src="javascript/backend/misc.js" type="text/javascript"></script>
</body>
</html>
<?php
} else {
	$_SESSION['referer'] = $_SERVER['REQUEST_URI'];
	header("Location: login.php");
	exit();
}
?>