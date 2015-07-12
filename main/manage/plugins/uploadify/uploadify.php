<?php
/**
* Uploadify 后台处理
* Author: Hintay, wind
* Date: 2015/7/11
**/

header("Content-Type: text/html; charset=UTF-8");
// 设置上传目录
$path = "../../../upload/";

if (!empty($_FILES)) {
	// 得到上传的临时文件流
	$tempFile = $_FILES['Filedata']['tmp_name'];

	// 得到文件原名
	$fileName = $_FILES["Filedata"]["name"];
	if (file_exists($path . $fileName)) {
		echo(json_encode(array('code'=>300,'filename'=>$fileName,'status'=>'exist')));
	} else {
		// 接受动态传值
		$files=$_POST['typeCode'];

		// 最后保存服务器地址
		if(!is_dir($path))
			mkdir($path);
		if (move_uploaded_file($tempFile, $path.$fileName))
			echo(json_encode(array('code'=>200,'filename'=>$fileName,'status'=>'success')));
		else
			echo(json_encode(array('code'=>300,'filename'=>$fileName,'status'=>'failed')));
	}
}
?>