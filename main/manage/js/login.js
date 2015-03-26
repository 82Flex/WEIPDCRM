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
        var $form    = $('form[name=form-login]');

        // 点击提交按钮
        $form.on('click', 'button[type=submit]', function (e) {
            var $this = $(this);

            //运行parsley验证
            if ($form.parsley().validate()) {
                // 禁用提交按钮
                $this.prop('disabled', true);

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
				$('#loginlogo').fadeOut(0);
				animation();
            }
            // prevent default
            e.preventDefault();
        });
    });

}));
function animation(){
	// 切换动画
	$('form[name=form-login]')
		.removeClass('animation animating shake')
		.addClass('animation animating shake')
		.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
			$(this).removeClass('animation animating shake');
		});
}