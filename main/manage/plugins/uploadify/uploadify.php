<?php
/*
Uploadify 后台处理 Demo
Author:wind
Date:2013-1-4
uploadify 后台处理！
*/
header("Content-Type: text/html; charset=UTF-8");
//设置上传目录
$path = "../../../upload/";	

if (!empty($_FILES)) {

	//得到上传的临时文件流
	$tempFile = $_FILES['Filedata']['tmp_name'];
	
	//允许的文件后缀
	$fileTypes = array('jpg','jpeg','gif','png'); 
	
	//得到文件原名
	$fileName = $_FILES["Filedata"]["name"];
	$fileParts = pathinfo($fileName);
	if (file_exists($path . $fileName)) {
		echo $_FILES["Filedata"]["name"] . " 已经存在";
	} else {	
	//接受动态传值
	$files=$_POST['typeCode'];
	
	//最后保存服务器地址
	if(!is_dir($path))
	   mkdir($path);
	if (move_uploaded_file($tempFile, $path.$fileName)){
		echo $_FILES["Filedata"]["name"]."上传成功！";
	}else{
		echo $_FILES["Filedata"]["name"]."上传失败！";
	}
	
	}
}
?>