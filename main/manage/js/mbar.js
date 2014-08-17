function getRadioBoxValue(radioName) {
	var obj = document.getElementsByName(radioName);
	for (i = 0; i < obj.length; i++) {
		if (obj[i].checked) {
			return obj[i].value;
		}
	}
	return "undefined"; 
}  
function opt(r) {
	pid = getRadioBoxValue("package");
	if (pid != "undefined") {
		if (r == 1) {
			window.location.href = "view.php?id="+pid;
		} else if (r == 2) {
			window.location.href = "edit.php?id="+pid;
		} else if (r == 3) {
			window.location.href = "edit.php?action=advance&id="+pid;
		} else if (r == 4) {
			if(confirm("您确定要隐藏该软件包？")){
			   window.location.href = "center.php?action=submit&id="+pid;
			}
		} else if (r == 5) {
			if(confirm("您确定要显示该软件包？\n该软件包的其它版本都将被隐藏。")){
			   window.location.href = "center.php?action=submit&id="+pid;
			}
		}
	}
}