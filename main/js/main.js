if (document.getElementById("scroller")) {
	new iScroll(document.getElementById("scroller"));
}
if (navigator.userAgent.search(/Cydia/) != -1) {
	document.body.classList.add("cydia");
} else {
	if (document.getElementById("reportlink")) {
		document.getElementById("reportlink").style.display = "none";
	}
	if (document.getElementById("cydialink")) {
		document.getElementById("cydialink").style.display = "";
	}
}
if (window.location.href.search(/nohistory/) != -1) {
	if (document.getElementById("reportlink")) {
		document.getElementById("reportlink").style.display = "none";
	}
	if (document.getElementById("historylink")) {
		document.getElementById("historylink").style.display = "none";
	}
}
if (window.location.href.search(/advertisement/) != -1) {
	if (document.getElementById("advertisement")) {
		document.getElementById("advertisement").style.display = "none";
	}
}