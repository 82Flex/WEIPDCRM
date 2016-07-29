<?php
/**
 * DCRM Installer Check
 * Copyright (c) 2015 Hintay <hintay@me.com>
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

if (!defined("DCRM")) {
	exit;
}
$__['php_os'] = __('OS');
$__['php_vers'] = __('PHP Ver');
$__['gd_vers'] = __('GD Ver');
$__['disk'] = __('Disk Space');
$__['writeable'] = __('Writeable');
$__['unwriteable'] = __('Unwriteable');
$__['supportted'] = __('Supported');
$__['unsupportted'] = __('Unsupported');
$__['error_php_vers'] = __('Unsupported this PHP Version');
$__['error_gd_vers'] = __('GD module version is too low');
$__['error_disk'] = __('Not enough disk space or cannot get disk space');
$__['error_./'] = __('No \'./\' directory read-write permissions');
$__['error_./system/config'] = __('No \'./system/config\' directory read-write permissions');
$__['error_mysql_connect( )'] = __('Mysql module not support');
$__['error_fsockopen( )'] = __('Does not support the fsockopen function or the function is disabled');
$__['error_fsockopen,curl( )'] = __('Does not support the fsockopen function and curl module or the function is disabled');
$__['error_file_get_contents( )'] = __('Does not support the file_get_contents function or the function is disabled');
$__['error_mhash( )'] = __('Does not support the mhash function or the function is disabled, maybe can not use GPG function.');
$__['error_bzcompress( )'] = __('Does not support the bzcompresss function or the function is disabled, maybe can not use list compress function.');
$__['error_zlib_encode( )'] = __('Does not support the zlib function or the function is disabled, maybe can not use list compress function.');
$__['error_mhash,hash_hmac( )'] = __('Does not support the mhash or hash_hmac function, maybe can not use list compress function.');
?>
	<div class="check">
	<h3><?php _e( 'Environments Check' );?></h3>
	<table>
		<colgroup>
			<col style="width:15%;" />
			<col style="width:20%;" />
			<col style="width:25%;" />
			<col style="width:32%;" />
			<col style="width:8%;" />
		</colgroup>
		<tr>
			<th class="l-b"><?php _e( 'Project' );?></th>
			<th><?php _e( 'Required' ); ?></th>
			<th><?php _e( 'Best' );?></th>
			<th><?php _e( 'Current Server' );?></th>
			<th class="r-b"><?php _e( 'Result' );?></th>
		</tr>
		<?php if ($env_vars):?>
		<?php foreach ($env_vars as $key => $item):?>
		<tr>
			<td><?php echo $__[$key];?></td>
			<td><?php echo $item['required'];?></td>
			<td><?php echo $item['best'];?></td>
			<td class="<?php echo $item['state'] ? $item['state'] : 'false';?>"><?php echo $item['curr'];?> </td>
			<td><span class="icon-<?php if ($item['state'] == true):?>true<?php else:?>false<?php endif;?>"></span></td>
		</tr>
<?php 
if ($item['state'] == false) {
	$env_error[$key] = $key;
}
?>
	<?php endforeach;?>
	<?php endif;?> 
	</table>
	<?php if (isset($env_error)):?>
	<div class="warn">
		<?php foreach ($env_error as $var):?>
		<p><?php echo $__['error_'.$var];?></p>
		<?php endforeach;?>
	</div>
	<?php endif;?>
	
	<h3><?php _e( 'Private Check' );?></h3>
	<table>
		<colgroup>
			<col style="width:60%;" />
			<col style="width:32%;" />
			<col style="width:8%;" />
		</colgroup>
		<tr>
			<th class="l-b"><?php _e( 'Directorys' );?></th>
			<th><?php _e( 'Status' );?></th>
			<th class="r-b"><?php _e( 'Result' );?></th>
		</tr>
		<?php if ($dir_file_vars):?>
		<?php foreach ($dir_file_vars as $key => $item):?>
			<tr>
				<td><?php echo $key;?>  </td>
				<td class="<?php echo $item['state'] ? $item['state'] : 'false';?>"><?php echo $__[$item['w']];?></td>
				<td><span class="icon-<?php if ($item['state'] == true):?>true<?php else:?>false<?php endif;?>"></span></td>
				</td>
			</tr>
<?php 
if ($item['state'] == false) {
	$dir_error[$key] = $key;
}
?>
		<?php endforeach;?>
		<?php endif;?>
	</table>
	<?php if (isset($dir_error)):?>
	<div class="warn">
		<?php foreach ($dir_error as $var):?>
		<p><?php echo $__['error_'.$var];?></p>
		<?php endforeach;?>
	</div>
	<?php endif;?>
	<h3><?php _e( 'Functions Depend Check' );?></h3>
		<table>
			<colgroup>
				<col style="width:60%;" />
				<col style="width:32%;" />
				<col style="width:8%;" />
			</colgroup>
		<tr>
			<th class="l-b"><?php _e( 'Function Name' );?></th>
			<th><?php _e( 'Status' );?></th>
			<th class="r-b"><?php _e ( 'Result' );?></th>
		</tr>
		<?php if ($func_vars):?>
		<?php foreach ($func_vars as $key => $item):?>
		<tr>
			<td><?php echo $key;?></td>
			<td class="<?php echo $item['state'] ? $item['state'] : 'false';?>"><?php echo $__[$item['s']];?></td>
			<td><span class="icon-<?php if ($item['state'] == true):?>true<?php else:?>false<?php endif;?>"></span></td>
		</tr>
<?php 
if ($item['state'] == false) {
	$fun_error[$key] = $key;
}
?>
		<?php endforeach;?>
		<?php endif;?>
	</table>
	<?php if (isset($fun_error)):?>
	<div class="warn">
		<?php foreach ($fun_error as $var):?>
		<p><?php echo $__['error_'.$var];?></p>
		<?php endforeach;?>
	</div>
	<?php endif;?>
	</div>