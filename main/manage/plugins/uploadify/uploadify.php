<?php
/**
 * Uploadify Backend API
 * Copyright (c) 2015 Hintay <hintay@me.com>
 * Copyright (c) wind
 *
 * Update Date: December 5, 2015
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

error_reporting(E_ALL ^ E_NOTICE);

if(isset($_POST['SESSION_ID']) && ($session_id = $_POST['SESSION_ID']) != session_id())
	session_id($session_id);
session_start();
if(!isset($_SESSION['connected']) || $_SESSION['connected'] != true)
	echo_json(403, 'NULL', 'Forbidden', 'You don\'t have permission to access.');

$path = "../../../upload/";
$allowExtension = 'deb';

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$fileName = $_FILES["Filedata"]["name"];

	$fileExtension = pathinfo($_FILES["Filedata"]["name"], PATHINFO_EXTENSION);
	if(strtolower($fileExtension) != $allowExtension)
		echo_json(300, 'NULL', 'extnotallow', 'Extension are not allowed.');

	$path = realpath($path) . '/';
	if(!empty($fileExtension))
		$fileName = str_replace('.'.$fileExtension, '', $fileName);
	$fileName = $fileName . '_' . date("Ymd") . '.' . $fileExtension;

	if (file_exists($path.$fileName)) {
		echo_json(300, $fileName, 'exist', 'Already Exists.');
	} else {
		if(!is_dir($path))
			mkdir($path);
		if (move_uploaded_file($tempFile, $path.$fileName))
			echo_json(200, $fileName, 'success', 'Upload Success.');
		else
			echo_json(300, $fileName, 'failed', 'Upload Failed.');
	}
} else {
	echo_json(403, 'NULL', 'Forbidden', 'No data submitted.');
}

function echo_json($code = 400, $fileName = 'NULL', $status = 'error', $message = '') {
	header('Content-type: text/html; charset=UTF-8');
	echo(json_encode(array('code' => $code, 'filename' => $fileName, 'status' => $status, 'message' => $message)));
	exit();
}
?>