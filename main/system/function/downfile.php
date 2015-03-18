<?php
if(!defined('IN_DCRM')) exit();
function _downFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = DCRM_SPEED_LIMIT, $contentType = '') {
	if (!is_readable($fileName)) {
		httpinfo(404);
		return false;
	}
	$fileStat = stat($fileName);
	$lastModified = $fileStat['mtime'];
	$md5 = md5($fileStat['mtime'] .'='. $fileStat['ino'] .'='. $fileStat['size']);
	$etag = '"' . $md5 . '-' . crc32($md5) . '"';
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
	header("ETag: $etag");
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) {
		httpinfo(304);
		return true;
	}  
	if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified) {
		httpinfo(304);
		return true;
	}
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&  $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		httpinfo(304);
		return true;
	}
	if (empty($fancyName)) {
		$fancyName = basename($fileName);
	}
	if (empty($contentType)) {  
		$contentType = 'application/octet-stream';
	}
	$fileSize = $fileStat['size'];
	$contentLength = $fileSize;
	if (isset($_SERVER['HTTP_RANGE'])) {
		//if (preg_match('/^bytes=(d*)-(d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
			$match = str_replace('=','-',$_SERVER['HTTP_RANGE']);
			$matches = explode('-',$match);
			$startPos = trim($matches[1]);
			$endPos = trim($matches[2]);
			if (empty($startPos) && empty($endPos)) {
				return false;  
			}
			if (empty($startPos)) {
				$startPos = $fileSize - $endPos;
				$endPos = $fileSize - 1;
			} elseif (empty($endPos)) {
				$endPos = $fileSize - 1;
			}
			$startPos = $startPos < 0 ? 0 : $startPos;
			$endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;
			$length = $endPos - $startPos + 1;
			if ($length < 0) {
				return false;
			}
			$contentLength = $length;
			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes '.$startPos.'-'.$endPos.'/'.$fileSize);
		//}
	} else {
		header("HTTP/1.1 200 OK");
		$startPos = 0;
		$endPos = $contentLength - 1;
	}
	header('Pragma: public');
	header('Cache-Control: public, must-revalidate, max-age=0');
	header('Accept-Ranges: bytes');
	header('Content-type: ' . $contentType);
	header('Content-Length: ' . $contentLength);
	if ($forceDownload) {
		header('Content-Disposition: attachment; filename="' . rawurlencode($fancyName). '"');
	}
	header("Content-Transfer-Encoding: binary");
	$bufferSize = 2048;
	if ($speedLimit != 0) {
		$packetTime = floor($bufferSize * 1000000 / $speedLimit);
	}
	$bytesSent = 0;
	$fp = fopen($fileName, "rb");
	fseek($fp, $startPos);
	while ($bytesSent < $contentLength && !feof($fp) && connection_status() == 0 ) {
		if ($speedLimit != 0) {
			list($usec, $sec) = explode(' ', microtime());
			$outputTimeStart = ((float)$usec + (float)$sec);
		}
		$readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
		$buffer = fread($fp, $readBufferSize);
		echo $buffer;
		ob_flush();
		flush();
		$bytesSent += $readBufferSize;
		if ($speedLimit != 0) {
			list($usec, $sec) = explode(' ', microtime());
			$outputTimeEnd = ((float)$usec + (float)$sec);
			$useTime = ((float) $outputTimeEnd - (float) $outputTimeStart) * 1000000;
			$sleepTime = round($packetTime - $useTime);
			if ($sleepTime > 0) {
				usleep($sleepTime);
			}
		}
	}
	return true;
}
?>