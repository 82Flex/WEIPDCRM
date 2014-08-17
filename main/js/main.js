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