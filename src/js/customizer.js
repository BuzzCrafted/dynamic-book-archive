/**
 * Theme Customizer preview: live-update site title and tagline (postMessage).
 */
(function ($) {
	wp.customize('blogname', function (setting) {
		setting.bind(function (value) {
			$('.site-title a').text(value);
		});
	});

	wp.customize('blogdescription', function (setting) {
		setting.bind(function (value) {
			$('.site-description').text(value);
		});
	});
})(jQuery);
