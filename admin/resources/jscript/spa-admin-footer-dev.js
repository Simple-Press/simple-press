/* ---------------------------------
Simple:Press - Version 5.0
Forum Admin Javascript loaded in footer after page loads

$LastChangedDate: 2016-04-25 09:48:50 -0700 (Mon, 25 Apr 2016) $
$Rev: 14157 $
------------------------------------ */

jQuery(document).ready(function() {
        /* activate the SP accordion menu */
		jQuery("#sfadminmenu").accordion({
            heightStyle: 'content',
            collapsible: true,
			active: parseInt(sp_admin_footer_vars.panel)
		});

        /* start the tooltips javascript functionality */
		if (sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
			jQuery(document).tooltip( {
				tooltipClass: "ttip",
				position: {
					my: "left+20 top",
					at: "left bottom+10"
				},
				track: false
			});
		}

        /* hightlight proper wp menu item with current class */
        jQuery('.wp-submenu li').removeClass('current');
        jQuery('.wp-submenu li').find('a:contains(' + sp_admin_footer_vars.panel_name + ')').parent().addClass('current');

        /* trigger event for items waiting on admin form loaded */
        jQuery('#sfmaincontainer').trigger('adminformloaded');
});