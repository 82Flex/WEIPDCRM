<?php
/**
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

/* DCRM About Page */

session_start();
define("DCRM",true);
$activeid = 'about';

if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {		
	require_once("header.php");

	if (!isset($_GET['action'])) {
	preg_match("#^([0-9]\.[0-9])\.([0-9.]*)#", $_version, $versions);
?>
					<h2><?php _ex('About', 'About Page'); ?></h2>
					<br /><div class="alert alert-info"><h3>
					Darwin Cydia Repository Manager (DCRM)<br />
					<?php _e('Version: '); ?><?php echo $versions[1];?> Pro <small>(<?php echo $_version;?>)</small><br />
					<?php _e('Open source Repository Manager for Cydia™/APT.'); ?></h3><br />
					<h4><?php _e('Developer'); ?></h4>
					<?php _e('Main Program: '); ?><?php _e('WeiPhone Test Group'); ?> <a href="http://weibo.com/82flex">@i_82</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/hintay">@Hintay</a><br />
					<!-- <?php _e('Contributors: '); ?><a href="http://weibo.com/405901422">JK</a><br /> -->
					<?php _e('UI Design:'); ?><a href="http://weibo.com/hintay">@Hintay</a><br /><br />
					<h4><?php _e('Credits'); ?></h4>
					<?php _e('Touch Sprite Team'); ?> @Z<?php _ex(', ' , 'Punctuation'); ?>@F<?php _ex(', ' , 'Punctuation'); ?>@K<?php _ex(', ' , 'Punctuation'); ?><?php _e('WeiPhone'); ?> <a href="http://weibo.cn/375584554">@飄Sir</a> <?php _ex('support' , 'Credits'); ?><br />
					<?php _e('E-pal'); ?> <a href="http://weibo.com/u/1766730601">@zsm1703</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/u/2175594103">@Naville</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/u/1931192555">@Q某某某某</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/u/3254325910">@摇滚米饭_</a><?php _ex(', ' , 'Punctuation'); ?>
					<?php _e('WeiPhone Test Group'); ?> <a href="http://weibo.com/u/1675423275">@Sunbelife</a><?php _ex(', ' , 'Punctuation'); ?><?php _e('WeiPhone Technology Group'); ?> <a href="http://weibo.cn/nivalxer">@NivalXer</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/u/1417725530">@ioshack</a> <?php _e('provide viewpoints'); ?><br />
					<?php _e('WeiPhone Technology Group'); ?> <a href="http://weibo.com/u/2004244347">@autopear</a><?php _e('\'s articles: '); ?><a href="http://bbs.weiphone.com/read-htm-tid-669283.html">从零开始搭建 Cydia™ 软件源，制作 Debian 安装包</a><br />
					<?php _e('Also, the father of the Cydia™'); ?> <a href="http://www.saurik.com">Saurik</a><?php _e('\'s articles: '); ?><a href="http://www.saurik.com/id/7">How to host a Cydia™ Repository</a><br/>
					<?php _e('Mobile Home Page Style From Saurik IT.'); ?><br /><br />
					<h4><?php _e('Translator'); ?></h4>
					<p>You can help us to translate DCRM into your familiar language!</p>
					English: <a href="http://weibo.com/hintay">@Hintay</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/82flex">@i_82</a><?php _ex(', ' , 'Punctuation'); ?>globus<br />
					简体中文 (Chinese Simplified): <a href="http://weibo.com/82flex">@i_82</a><?php _ex(', ' , 'Punctuation'); ?><a href="http://weibo.com/hintay">@Hintay</a><br />
					(Uyghurche) ئۇيغۇرچه: <a href="http://weibo.com/u/2767344637">Shiraq</a>
					<br /><br />
					<h4><?php _e('Copyright'); ?>&copy; 2013–<?php echo date('Y'); ?> i_82 &amp; Hintay<br />
					<?php _e('This program is free software, you can redistribute it and/or modify it under the terms of the <a href="http://www.gnu.org/licenses">GNU Affero General Public License</a> as published by the Free Software Foundation either version 3 of the License.'); ?></h4></div>
<?php
	} else {
		endlabel:
		echo $alert;
	}
?>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
<?php
} else {
	header("Location: login.php");
	exit();
}
?>