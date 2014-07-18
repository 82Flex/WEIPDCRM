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
	
	if (!defined("DCRM")) {
		exit();
	}
	
	function randstr($len = 40) { 
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		mt_srand((double)microtime() * 1000000 * getmypid());
		$ranseed = '';
		while (strlen($ranseed) < $len) {
			$ranseed .= substr($chars,(mt_rand() % strlen($chars)),1);
		}
		return $ranseed;
	}
	
	function deldir($dir) {
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$fullpath = $dir . "/" . $file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				}
				else {
					deldir($fullpath);
				}
			}
		}
		closedir($dh);
		if (rmdir($dir)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	function br2nl($text) {
		return preg_replace("/<br\\s*?\/??>/i", "\n", $text);
	}
	
	function dirsize($dirName) {
		$dirsize = 0;
		$dir = opendir($dirName);
		while ($fileName = readdir($dir)) {
			$file = $dirName . "/" . $fileName;
			if ($fileName != "." && $fileName != "..") {
				if (is_dir($file)) {
					$dirsize += dirsize($file);
				}
				else {
					$dirsize += filesize($file);
				}
			}
		}
		closedir($dir);
		return $dirsize;
	}
	
	function sizeext($size) {
		return ($size < 1048576) ? round($size/1024,2).' KiB' : round($size/1048576,2).' MiB';
	}
	
	function execInBackground($cmd) {
		if (substr(php_uname(),0,7) == "Windows") {
			pclose(popen("start /B " . $cmd,"r"));
		}
		else {
			exec($cmd . " > /dev/null &");
		}
	}
	
	function downFile($fileName, $fancyName = '', $forceDownload = true, $speedLimit = DCRM_SPEED_LIMIT, $contentType = '') {
		if (!is_readable($fileName)) {
			header("HTTP/1.1 404 Not Found");
			return false;
		}
		$fileStat = stat($fileName);
		$lastModified = $fileStat['mtime'];
		$md5 = md5($fileStat['mtime'] .'='. $fileStat['ino'] .'='. $fileStat['size']);
		$etag = '"' . $md5 . '-' . crc32($md5) . '"';
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $lastModified) . ' GMT');
		header("ETag: $etag");
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified) {
			header("HTTP/1.1 304 Not Modified");
			return true;
		}  
		if (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $lastModified) {
			header("HTTP/1.1 304 Not Modified");
			return true;
		}
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&  $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
			header("HTTP/1.1 304 Not Modified");
			return true;
		}
		if ($fancyName == '') {
			$fancyName = basename($fileName);
		}
		if ($contentType == '') {  
			$contentType = 'application/octet-stream';
		}
		$fileSize = $fileStat['size'];
		$contentLength = $fileSize;
		$isPartial = false;
		if (isset($_SERVER['HTTP_RANGE'])) {  
			if (preg_match('/^bytes=(d*)-(d*)$/', $_SERVER['HTTP_RANGE'], $matches)) {
				$startPos = $matches[1];
				$endPos = $matches[2];
				if ($startPos == '' && $endPos == '') {
					return false;  
				}
				if ($startPos == '') {
					$startPos = $fileSize - $endPos;
					$endPos = $fileSize - 1;
				}
				elseif ($endPos == '') {
					$endPos = $fileSize - 1;
				}
				$startPos = $startPos < 0 ? 0 : $startPos;
				$endPos = $endPos > $fileSize - 1 ? $fileSize - 1 : $endPos;
				$length = $endPos - $startPos + 1;
				if ($length < 0) {
					return false;
				}
				$contentLength = $length;
				$isPartial = true;
			}
		}
		if ($isPartial) {
			header('HTTP/1.1 206 Partial Content');
			header("Content-Range: bytes $startPos-$endPos/$fileSize");
		}
		else {
			header("HTTP/1.1 200 OK");
			$startPos = 0;
			$endPos = $contentLength - 1;
		}
		header('Pragma: cache');
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
				list($usec, $sec) = explode(" ", microtime());
				$outputTimeStart = ((float)$usec + (float)$sec);
			}
			$readBufferSize = $contentLength - $bytesSent < $bufferSize ? $contentLength - $bytesSent : $bufferSize;
			$buffer = fread($fp, $readBufferSize);
			echo $buffer;
			ob_flush();
			flush();
			$bytesSent += $readBufferSize;
			if ($speedLimit != 0) {
				list($usec, $sec) = explode(" ", microtime());
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
	
	function httpinfo($info_type) {
		switch ($info_type) {
			case 400:
				header("HTTP/1.1 400 Bad Request");
				header("Status: 400 Bad Request");
			break;
			case 404:
				header('HTTP/1.1 404 Not Found'); 
				header('Status: 404 Not Found');
			break;
			case 4030:
				header('HTTP/1.1 403 Forbidden'); 
				header('Status: 403 Forbidden');
			break;
			case 4031:
				header('HTTP/1.1 402 Payment Required'); 
				header('Status: 402 Payment Required');
			break;
			case 500:
				header('HTTP/1.1 500 Internal Server Error');
				header('Status: 500 Internal Server Error');
			break;
			default:
				header('HTTP/1.1 501 Not Implemented');
				header('Status: 501 Not Implemented');
			break;
		}
		header("Content-Type: text/html; charset=UTF-8");
		exit();
	}
	
	function imageresize($source, $destination, $width = 0, $height = 0, $crop = false, $quality = 80) {
	    $quality = $quality ? $quality : 80;
	    $image = imagecreatefromstring($source);
	    if ($image) {
	        $w = imagesx($image);
	        $h = imagesy($image);
	        if (($width && $w > $width) || ($height && $h > $height)) {
	            $ratio = $w / $h;
	            if (($ratio >= 1 || $height == 0) && $width && !$crop) {
	                $new_height = $width / $ratio;
	                $new_width = $width;
	            } elseif ($crop && $ratio <= ($width / $height)) {
	                $new_height = $width / $ratio;
	                $new_width = $width;
	            } else {
	                $new_width = $height * $ratio;
	                $new_height = $height;
	            }
	        } else {
	            $new_width = $w;
	            $new_height = $h;
	        }
	        $x_mid = $new_width * .5;
	        $y_mid = $new_height * .5;
	        $new = imagecreatetruecolor(round($new_width), round($new_height));
	        imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
	        if ($crop) {
	            $crop = imagecreatetruecolor($width ? $width : $new_width, $height ? $height : $new_height);
	            imagecopyresampled($crop, $new, 0, 0, ($x_mid - ($width * .5)), 0, $width, $height, $width, $height);
	            //($y_mid - ($height * .5))
	        }
	        imageinterlace($crop ? $crop : $new, true);
	
	        $dext = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
	        if ($dext == '') {
	            $dext = $ext;
	            $destination .= '.' . $ext;
	        }
	        switch ($dext) {
	            case 'jpeg':
	            case 'jpg':
	                imagejpeg($crop ? $crop : $new, $destination, $quality);
	                break;
	            case 'png':
	                $pngQuality = ($quality - 100) / 11.111111;
	                $pngQuality = round(abs($pngQuality));
	                imagepng($crop ? $crop : $new, $destination, $pngQuality);
	                break;
	            case 'gif':
	                imagegif($crop ? $crop : $new, $destination);
	                break;
	        }
	        @imagedestroy($image);
	        @imagedestroy($new);
	        @imagedestroy($crop);
	    }
	}
?>