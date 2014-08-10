function wrapper(triangleitem, item) {
	var triangle = document.getElementById(triangleitem);
	var elementitem = document.getElementById(item);
	if (elementitem.style.display == "none") {
		triangle.style.mozTransform = "rotate(0deg)";
		triangle.style.webkitTransform = "rotate(0deg)";
		triangle.style.oTransform = "rotate(0deg)";
		triangle.style.transform = "rotate(0deg)";
		elementitem.style.display = "block";
	} else {
		elementitem.style.display = "none";
		triangle.style.mozTransform = "rotate(-90deg)";
		triangle.style.webkitTransform = "rotate(-90deg)";
		triangle.style.oTransform = "rotate(-90deg)";
		triangle.style.transform = "rotate(-90deg)";
	}
}