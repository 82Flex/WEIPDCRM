function wrapper(triangleitem, item) {
	var triangle = document.getElementById(triangleitem);
	var elementitem = $("#"+item);
	if (elementitem.css("display")=="none") {
		triangle.style.mozTransform = "rotate(0deg)";
		triangle.style.webkitTransform = "rotate(0deg)";
		triangle.style.oTransform = "rotate(0deg)";
		triangle.style.transform = "rotate(0deg)";
		elementitem.slideDown(300);
	} else {
		elementitem.slideUp(300);
		triangle.style.mozTransform = "rotate(-90deg)";
		triangle.style.webkitTransform = "rotate(-90deg)";
		triangle.style.oTransform = "rotate(-90deg)";
		triangle.style.transform = "rotate(-90deg)";
	}
}