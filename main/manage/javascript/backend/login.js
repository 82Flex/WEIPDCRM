/* ========================================================================
 * login.js
 * 页面: login.php
 * 插件使用: parsley
 * ======================================================================== */

'use strict';

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define([
			'parsley'
		], factory);
	} else {
		factory();
	}
}(function () {

	$(function () {
		// 登录表功能
		// ================================
		var $form = $('form[name=form-login]');

		// 点击提交按钮
		$form.on('click', 'button[type=submit]', function (e) {
			var $this = $(this);

			//运行parsley验证
			if ($form.parsley().validate()) {
				// 禁用提交按钮(Safari支持有问题？)
				//$this.prop('disabled', true);

				// start nprogress bar
				NProgress.start();

				// 你可以在此放入ajax
				setTimeout(function () {
					// done nprogress bar
					NProgress.done();

					// 提交表单
					 $('form-login').submit();
				}, 500);
			} else {
				animation();
			}
			// prevent default
			e.preventDefault();
		});
	});

}));
function animation(){
	// 切换动画
	$('.panel')
		.removeClass('animation animating shake')
		.addClass('animation animating shake')
		.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
			$(this).removeClass('animation animating shake');
		});
};
// 选择语言后POST
function set_language(){
	var selectedValue = $("select[name=language]").val();
	selectedValue = typeof selectedValue == "object" ? selectedValue[0] : selectedValue;
	$.post("login.php", {language: selectedValue}, function(){
		window.location.href = "login.php";
	});
};
// 粒子动画
var particles_config = {
	"particles": {
		"number": {"value": 20, "density": {"enable": 1, "value_area": 1E3}}, 
		"color": {"value": "#e1e1e1"}, 
		"shape": {"type": "circle"}, 
		"opacity": {"value": 0.5, "random": 0, "anim": {"enable": 1,"speed": 0.5,"opacity_min": 0}}, 
		"size": {"value": 15, "random": 1, "anim": {"enable": 0}}, 
		"line_linked": {"enable": 1, "distance": 650, "color": "#cfcfcf", "opacity": 0.25, "width": 1}, 
		"move": {"enable": 1, "speed": 2, "random": 1, "direction": "none", "straight": 0, "out_mode": "out", "bounce": 0, "attract": {"enable": 0}}
	}, 
	"interactivity": {"detect_on": "canvas", "events": {"onhover": {"enable": 0}, "onclick": {"enable": 0}, "resize": 1},}, 
	"retina_detect": 1
};
((0, window.$)("\x3cdiv\x3e", {id: "particles"}).appendTo("body"), window.particlesJS('particles', particles_config));