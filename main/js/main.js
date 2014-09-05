function setCookie(c_name, value, expiredays) {
	var exdate = new Date()
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
}
function getCookie(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1) { 
			c_start = c_start + c_name.length + 1;
			c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1) {
				c_end = document.cookie.length;
			}
    	return unescape(document.cookie.substring(c_start, c_end));
		} 
	}
	return "";
}
//淡入效果(含淡入到指定透明度)
function fadeIn(elem, speed, opacity){
	/*
	 * 参数说明
	 * elem==>需要淡入的元素
	 * speed==>淡入速度,正整数(可选)
	 * opacity==>淡入到指定的透明度,0~100(可选)
	 */
    speed = speed || 20;
    opacity = opacity || 100;
	//显示元素,并将元素值为0透明度(不可见)
    elem.style.display = 'block';
    elem.style.opacity = 0;
	//初始化透明度变化值为0
    var val = 0;
	//循环将透明值以5递增,即淡入效果
    (function(){
        elem.style.opacity = val / 100;
        val += 5;
        if (val <= opacity) {
            setTimeout(arguments.callee, speed)
        }
    })();
}
//淡出效果(含淡出到指定透明度)
function fadeOut(elem, speed, opacity){
	/*
	 * 参数说明
	 * elem==>需要淡入的元素
	 * speed==>淡入速度,正整数(可选)
	 * opacity==>淡入到指定的透明度,0~100(可选)
	 */
    speed = speed || 20;
    opacity = opacity || 0;
    //初始化透明度变化值为0
    var val = 100;
	//循环将透明值以5递减,即淡出效果
    (function(){
        elem.style.opacity = val / 100;
        val -= 5;
        if (val >= opacity) {
            setTimeout(arguments.callee, speed);
        }else if (val < 0) {
			//元素透明度为0后隐藏元素
            elem.style.display = 'none';
        }
    })();
}
function hide() {
	if (document.getElementById("advertisement")) {
			fadeOut(document.getElementById("advertisement"));
			setCookie("hideadv", "yes", 1);
	}
}
function show() {
	if (getCookie("hideadv") == "yes" && document.getElementById("advertisement")) {
			document.getElementById("advertisement").style.display = "none";
	}
}
isCydia = navigator.userAgent.search(/Cydia/);
isHistory = window.location.href.search(/nohistory/);
isAdv = window.location.href.search(/advertisement/);
if (isCydia != -1) {
	document.body.classList.add("cydia");
} else {
	if (document.getElementById("cydialink")) {
		document.getElementById("cydialink").style.display = "";
	}
	if (document.getElementById("downloadlink")) {
		document.getElementById("downloadlink").style.display = "";
	}
}
if (isHistory != -1 || isCydia == -1) {
	if (document.getElementById("header")) {
		document.getElementById("header").style.display = "";
	}
	if (document.getElementById("contact")) {
		document.getElementById("contact").style.display = "";
	}
	if (document.getElementById("reportlink")) {
		document.getElementById("reportlink").style.display = "none";
	}
	if (document.getElementById("advertisement")) {
		document.getElementById("advertisement").style.display = "none";
	}
	if (document.getElementById("footer")) {
		document.getElementById("footer").style.display = "";
	}
}
if (isHistory != -1) {
	if (document.getElementById("reportlink")) {
		document.getElementById("reportlink").style.display = "none";
	}
	if (document.getElementById("historylink")) {
		document.getElementById("historylink").style.display = "none";
	}
}
if (isAdv != -1) {
	if (document.getElementById("advertisement")) {
		document.getElementById("advertisement").style.display = "none";
	}
}
if (document.getElementById("scroller")) {
	new iScroll(document.getElementById("scroller"));
}
show();