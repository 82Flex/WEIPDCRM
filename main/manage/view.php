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

/* DCRM Debian Simple View */

session_start();
define("DCRM",true);
$localetype = 'manage';
define('MANAGE_ROOT', dirname(__FILE__).'/');
define('ABSPATH', dirname(MANAGE_ROOT).'/');
require_once ABSPATH.'system/common.inc.php';
$activeid = 'view';
$f_Package = "";

if (!isset($_SESSION['connected']) || $_SESSION['connected'] != true) {
	header("Location: login.php");
	exit();
}
if (is_numeric($_GET['id'])) {
	$request_id = (int)$_GET['id'];
} else {
	httpinfo(405);
	exit();
}
$package_info = DB::fetch_first("SELECT * FROM `".DCRM_CON_PREFIX."Packages` WHERE `ID` = '" . $request_id . "'");

if (isset($_GET['action']) && $_GET['action'] == "image" && isset($_POST['image']) && strlen($_POST['image']) > 0) {
	if(strpos($_POST['image'], ', '))
		$images_array = explode(', ', $_POST['image']);
	else
		$images_array = array($_POST['image']);
	foreach($images_array as $image) {
		if(!empty($image))
			DB::insert(DCRM_CON_PREFIX.'ScreenShots', array('PID' => $request_id, 'Image' => DB::real_escape_string($image)));
	}
} elseif (isset($_GET['action']) && $_GET['action'] == "del" && is_numeric($_GET['image'])) {
	DB::delete(DCRM_CON_PREFIX.'ScreenShots', array('ID' => DB::real_escape_string($_GET['image'])));
}

require_once("header.php");
?>
			<input type="radio" name="package" value="<?php echo($request_id); ?>" style="display: none;" checked="checked" />
			<h2><?php _e('View Details'); ?></h2>
			<br />
<?php
if (!$package_info) {
	$alert = __('No specified item.');
} else {
	unset($package_info['Multi']);
	unset($package_info['System_Support']);
	foreach ($package_info as $m_key => $m_value) {
		if (!empty($m_value)) {
			$f_Package .= $m_key . ": " . trim(str_replace("\n","\n ",$m_value)) . "\n";
		}
	}
	$protection_status = check_commercial_tag($package_info['Tag']);
	$package = $package_info['Package'];
?>
			<div class="alert alert-info">
<?php echo(nl2br(htmlspecialchars($f_Package))); ?>
			</div>
<?php
}
$screenshots = DB::fetch_all("SELECT * FROM `".DCRM_CON_PREFIX."ScreenShots` WHERE `PID` = '".$request_id."'");
if (!$screenshots) {
?>
			<div class="alert" id="tips">
				<?php _e('This package no screenshot.'); ?><br />
			</div>
<?php
} else {
?>
			<div class="alert alert-success" id="tips">
				<?php printf(_n('This package have %d screenshot.', 'This package have %d screenshots.', count($screenshots)), count($screenshots)); ?><br />
<?php
	foreach($screenshots as $screenshot){
?>
				<li><a href="<?php echo($screenshot["Image"]); ?>"><?php if (strlen($screenshot["Image"]) > 72) echo(mb_substr($screenshot["Image"],0,72,"UTF-8").' ...'); else echo($screenshot["Image"]); ?></a>&emsp;<a href="javascript:delimage(<?php echo($screenshot["ID"]); ?>);">&times;</a></li>
<?php
	}
?>
			</div>
<?php
}
?>
			<form class="form-horizontal" method="POST" action="view.php?id=<?php echo($request_id); ?>&amp;action=image">
				<fieldset>
					<div class="group-control">
						<label class="control-label">* <?php _e('New Screenshot'); ?></label>
						<div class="controls">
							<input type="button" id="multiimage" value="<?php _e('Batch Upload'); ?>" />
							<input type="button" id="image1" value="<?php _e('Select Picture'); ?>" />
							<input type="text" id="url1" style="width: 400px;" required="required" name="image" /><button action="view.php?id=<?php echo($request_id); ?>&amp;action=image"><?php _e('Confirm'); ?></button>
						</div>
					</div>
				</fieldset>
			</form>
			</div>
		</div>
	</div>
	</div>
	<script type="text/javascript">
<?php if($protection_status): ?>
		sli = document.getElementById('sli');
		sli.innerHTML = '<a href="udid.php?package=<?php echo $package;?>"><?php _e('Binding UDID');?></a>';
<?php endif; ?>
		function delimage(pid) {
			if(confirm("<?php _e('Are you sure you want to delete this screenshot?'); ?>")){
			   window.location.href = "view.php?id=<?php echo($request_id); ?>&action=del&image=" + pid;
			}
		}
	</script>
	<link rel="stylesheet" href="./plugins/kindeditor/themes/default/default.css" />
	<script charset="utf-8" src="./plugins/kindeditor/kindeditor.min.js"></script>
	<script charset="utf-8" src="./plugins/kindeditor/lang/<?php echo $kdlang = check_languages(array($locale), 'kind');?>.js"></script>
	<script>
		KindEditor.ready(function(K) {
			var editor = K.editor({
				allowFileManager : true,
				langType : '<?php echo $kdlang; ?>',
				imageSizeLimit: '20MB'
			});
			K('#image1').click(function() {
				editor.loadPlugin('image', function() {
					editor.plugin.imageDialog({
						imageUrl : K('#url1').val(),
						clickFn : function(url, title, width, height, border, align) {
							K('#url1').val(url + ', ');
							editor.hideDialog();
						}
					});
				});
			});
			K('#multiimage').click(function() {
					editor.loadPlugin('multiimage', function() {
						editor.plugin.multiImageDialog({
							clickFn : function(urlList) {
								var div = K('#url1');
								K.each(urlList, function(i, data) {
									var image_url = K.formatUrl(data.url, 'absolute');
									div.val(div.val() + image_url + ', ');
								});
								editor.hideDialog();
							}
						});
					});
				});
		});
	</script>
</body>
</html>