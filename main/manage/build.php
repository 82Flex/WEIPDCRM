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
	
	/* DCRM Packages Output */
	
	session_start();
	ob_start();
	define("DCRM",true);
	require_once("include/config.inc.php");
	require_once("include/gnupg.inc.php");
	require_once('include/connect.inc.php');
	require_once('include/func.php');
	header("Content-Type: text/html; charset=UTF-8");
	$activeid = 'build';
	$alert = "";
	
	if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
		header("Location: login.php");
		exit();
	}
	$con = mysql_connect(DCRM_CON_SERVER, DCRM_CON_USERNAME, DCRM_CON_PASSWORD);
	if (!$con) {
		$alert = "数据库错误！" . mysql_error();
		goto endlabel;
	}
	mysql_query("SET NAMES utf8");
	$select  = mysql_select_db(DCRM_CON_DATABASE);
	if (!$select) {
		$alert = mysql_error();
		goto endlabel;
	}
	$parts = "`ID`, `Package`, `Source`, `Version`, `Priority`, `Section`, `Essential`, `Maintainer`, `Pre-Depends`, `Depends`, `Recommends`, `Suggests`, `Conflicts`, `Provides`, `Replaces`, `Enhances`, `Architecture`, `Filename`, `Size`, `Installed-Size`, `Description`, `Origin`, `Bugs`, `Name`, `Author`, `Sponsor`, `Homepage`, `Website`, `Depiction`, `Icon`, `Tag`";
	if (DCRM_CHECK_METHOD != 0) {
		$parts .= ", `MD5sum`";
	}
	if (DCRM_CHECK_METHOD == 2 || DCRM_CHECK_METHOD == 3) {
		$parts .= ", `SHA1`";
	}
	if (DCRM_CHECK_METHOD == 3) {
		$parts .= ", `SHA256`";
	}
	$m_query = mysql_query("SELECT ".$parts." FROM `".DCRM_CON_PREFIX."Packages` WHERE `Stat` = '1' GROUP BY `Package` ORDER BY `ID` DESC");
	if ($m_query == false) {
		$alert = "数据库错误！";
		goto endlabel;
	}
	$Packages = "";
	$i=0;
	while ($m_array = mysql_fetch_assoc($m_query)) {
		$i++;
		$f_Package = "";
		$m_array['Filename'] = "./debs/" . $m_array['ID'] . ".deb";
		unset($m_array['ID']);
		foreach ($m_array as $m_key => $m_value) {
			if (!empty($m_value) && !empty($m_key)) {
				$f_Package .= $m_key . ": " . trim(str_replace("\n", "\n ", $m_value)) . "\n";
			}
		}
		$Packages .= $f_Package . "\n";
	}
	
	if ($i > 0) {
		$alert .= "找到记录数：".$i;
	}
	else {
		$alert .= "数据表 `Packages` 中无记录！请先导入软件包并允许其显示。";
		goto norecord;
	}
	
	// Build Packages List & Release
	if (!file_exists("include/release.save")) {
		$alert .= "\n警告：release.save 文件不存在！请进行源信息设置！";
	}
	else {
		$alert .= "\nRelease 配置文件存在。";
		$verify_text = "MD5Sum:\n";
		if (file_exists("../Packages")) {
			unlink("../Packages");
		}
		if (DCRM_LISTS_METHOD == 1 OR DCRM_LISTS_METHOD == 3 OR DCRM_LISTS_METHOD == 5 OR DCRM_LISTS_METHOD == 7) {
			$handle = fopen("../Packages", "w");
			$size = fwrite($handle, $Packages);
			fclose($handle);
			$r_array['Packages'] = filesize("../Packages");
			$md5_array['Packages'] = md5_file("../Packages");
			$verify_text .= " " . $md5_array['Packages'] . " " . $r_array['Packages'] . " " . "Packages\n";
			$alert .= "\n写入 Packages 文件：".$r_array['Packages'];
		}
		if (file_exists("../Packages.gz")) {
			unlink("../Packages.gz");
		}
		if (DCRM_LISTS_METHOD == 2 OR DCRM_LISTS_METHOD == 3 OR DCRM_LISTS_METHOD == 6 OR DCRM_LISTS_METHOD == 7) {
			$g_handle = gzopen("../Packages.gz", "w");
			$size = gzwrite($g_handle, $Packages);
			gzclose($g_handle);
			$r_array['Packages.gz'] = filesize("../Packages.gz");
			$md5_array['Packages.gz'] = md5_file("../Packages.gz");
			$verify_text .= " " . $md5_array['Packages.gz'] . " " . $r_array['Packages.gz'] . " " . "Packages.gz\n";
			$alert .= "\n写入 Packages.gz 文件：".$r_array['Packages.gz'];
		}
		if (file_exists("../Packages.bz2")) {
			unlink("../Packages.bz2");
		}
		if (DCRM_LISTS_METHOD == 4 OR DCRM_LISTS_METHOD == 5 OR DCRM_LISTS_METHOD == 6 OR DCRM_LISTS_METHOD == 7) {
			$b_handle = bzopen("../Packages.bz2", "w");
			$size = bzwrite($b_handle, $Packages);
			bzclose($b_handle);
			$r_array['Packages.bz2'] = filesize("../Packages.bz2");
			$md5_array['Packages.bz2'] = md5_file("../Packages.bz2");
			$verify_text .= " " . $md5_array['Packages.bz2'] . " " . $r_array['Packages.bz2'] . " " . "Packages.bz2\n";
			$alert .= "\n写入 Packages.bz2 文件：".$r_array['Packages.bz2'];
		}
		if (DCRM_GNUPG_ENABLED == 1) {
			file_put_contents("../Release",file_get_contents("include/release.save") . $verify_text);
			$gpg_cmd = escapeshellcmd(DCRM_GNUPG_PATH . ' -abs --yes --passphrase "' . DCRM_GNUPG_PASS . '" -r "' . DCRM_GNUPG_NAME . '" -o "../Release.gpg" "../Release"');
			execInBackground($gpg_cmd);
		}
		else {
			file_put_contents("../Release",file_get_contents("include/release.save"));
		}
	}
	
	norecord:
	$alert .= "\n";
	
	endlabel:
	mysql_close($con);
	
	require_once("header.php");
?>
			<h2>刷新列表</h2>
			<br>
			<h4 class="alert alert-success">列表重建请求提交成功，建议您 <a href="stats.php?action=clean">清理缓存</a> 以释放磁盘空间。</h4>
				<div class="wrapper">
					<ul class="breadcrumb"><i class="icon" id="triangle_errors" onclick="wrapper('triangle_errors','item_errors'); return false;">▼</i>　错误</ul>
					<div class="item" style="display:block;" id="item_errors">
						<?php
							if (!empty($alert)) {echo nl2br($alert);}
						?>
					</div>
					<ul class="breadcrumb" onclick="return false;"><i class="icon" id="triangle_packages" onclick="wrapper('triangle_packages','item_packages'); return false;">▼</i>　Packages 文件预览</ul>
					<div class="item" style="display:block;" id="item_packages">
						<?php if (!empty($Packages)) {echo nl2br(htmlspecialchars(mb_substr($Packages,0,2048,"UTF-8")));}else{echo "文件不存在。";} ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	<script src="js/misc.js" type="text/javascript"></script>
</body>
</html>