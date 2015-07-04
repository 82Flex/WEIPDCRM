var isCydia = navigator.userAgent.search(/Cydia/);
var isHistory = window.location.href.search(/nohistory/);
var isAdv = window.location.href.search(/advertisement/);
(function($){$.extend({Request:function(m){var sValue=location.search.match(new RegExp("[\?\&]"+m+"=([^\&]*)(\&?)","i"));return sValue?sValue[1]:sValue;},UrlUpdateParams:function(url,name,value){var r=url;if(r!=null&&r!='undefined'&&r!=""){value=encodeURIComponent(value);var reg=new RegExp("(^|)"+name+"=([^&]*)(|$)");var tmp=name+"="+value;if(url.match(reg)!=null){r=url.replace(eval(reg),tmp);}else{if(url.match("[\?]")){r=url+"&"+tmp;}else{r=url+"?"+tmp;}}}return r;}});})(jQuery);
function loadPackages() {
	var offset = 0;
	offset = document.getElementById("section").children.length;
	$.ajax({
		type: 'GET',
		url: '/index.php?pid=' + $.Request('pid') + '&method=packages' + '&offset=' + offset,
		dataType: 'html',
		cache: true,
		success: function (data) {
			if (data.length == 0) {
				$('#loadmore').fadeOut();
			} else {
				$('#section').append(data);
			}
		}
	});
}
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
function hide() {
	if ($("#advertisement")[0]) {
		$("#advertisement").fadeOut();
		setCookie("hideadv", "yes", 1);
	}
}
function show() {
	if (getCookie("hideadv") == "yes" && $("#advertisement")[0]) {
		document.getElementById("advertisement").style.display = "none";
	}
}
if (isCydia != -1) {
	document.body.classList.add("cydia");
} else {
	if ($("#cydialink")[0]) {
		document.getElementById("cydialink").style.display = "";
	}
	if ($("#downloadlink")[0]) {
		document.getElementById("downloadlink").style.display = "";
	}
}
if (isHistory != -1 || isCydia == -1) {
	if ($("#header")[0]) {
		document.getElementById("header").style.display = "";
	}
	if ($("#contact")[0]) {
		document.getElementById("contact").style.display = "";
	}
	if ($("#reportlink")[0]) {
		document.getElementById("reportlink").style.display = "none";
	}
	if ($("#advertisement")[0]) {
		document.getElementById("advertisement").style.display = "none";
	}
	if ($("#footer")[0]) {
		document.getElementById("footer").style.display = "";
	}
}
if (isHistory != -1) {
	if ($("#reportlink")[0]) {
		document.getElementById("reportlink").style.display = "none";
	}
	if ($("#historylink")[0]) {
		document.getElementById("historylink").style.display = "none";
	}
}
if (isAdv != -1) {
	if ($("#advertisement")[0]) {
		document.getElementById("advertisement").style.display = "none";
	}
}
if ($("#scroller")[0]) {
	new iScroll(document.getElementById("scroller"));
}
if ($("#section")[0]) {
	loadPackages();
}
show();