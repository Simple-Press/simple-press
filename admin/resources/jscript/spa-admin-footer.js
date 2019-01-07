/* ---------------------------------
 Simple:Press - Version 5.0
 Forum Admin Javascript loaded in footer after page loads

 $LastChangedDate: 2016-04-25 09:48:50 -0700 (Mon, 25 Apr 2016) $
 $Rev: 14157 $
 ------------------------------------ */

(function(spj, $, undefined) {
	// private properties

	// public properties

	// public methods
	$(document).ready(function() {
		activateAccordion();
		setupTooltips();
		highlightMenu();

		/* trigger event for items waiting on admin form loaded */
		$('#sfmaincontainer').trigger('adminformloaded');
	});

	// private methods
	function activateAccordion() {
		$("#sfadminmenu").accordion({
			heightStyle: 'content',
			collapsible: true,
			active: parseInt(sp_admin_footer_vars.panel)
		});
	}

	function setupTooltips() {
		if (sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
			$(document).tooltip({
				tooltipClass: "ttip",
				position: {
					my: "left+20 top",
					at: "left bottom+10"
				},
				track: false
			});
		}
	}

	function highlightMenu() {
		$('.wp-submenu li').removeClass('current');
		$('.wp-submenu li').find('a:contains(' + sp_admin_footer_vars.panel_name + ')').parent().addClass('current');
	}
}(window.spj = window.spj || {}, jQuery));
