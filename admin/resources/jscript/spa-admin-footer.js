/* ---------------------------------
 Simple:Press - Version 5.0
 Forum Admin Javascript loaded in footer after page loads

 $LastChangedDate: 2019-02-22 09:48:50 -0700 (Fri, 22 Feb 2019) $
 $Rev: 14157 $
 ------------------------------------ */

(function(spj, $, undefined) {
    // private properties

    // public properties

    // public methods
    $(document).ready(function() {
        initAdminMenu();
        setupTooltips();
        spl_license_activate();
        spl_license_deactivate();
		spLicenseRemove();
        save_store_url();
        spPluginUpdateModel();
        spForceUpdateCheck();

        /* trigger event for items waiting on admin form loaded */
        $('#sfmaincontainer').trigger('adminformloaded');
    });

    // private methods
    function initAdminMenu() {
        var dirName = typeof( sp_platform_vars.dirName ) !== 'undefined' ? sp_platform_vars.dirName : 'simple-press';
        var wp_submenu = $('li#toplevel_page_'+dirName+'-admin-panel-forums-spa-forums ul.wp-submenu li.current a').html();
        var num = $('li#toplevel_page_'+dirName+'-admin-panel-forums-spa-forums ul.wp-submenu li.current').index() - 1;
        
        $('#sfadminmenu .sfsidebutton').each( function(k, v) {
                if( $(this).find('a').html() == wp_submenu ) {
                        num = k;
                }
        });
        
        $('#sfadminmenu .sfsidebutton:eq('+num+')+.sfmenublock .sfsideitem:eq(0)').addClass('sf-active');
        
        $("#sfadminmenu").accordion({
            heightStyle: 'content',
            collapsible: true,
            active: num
        });
        
        $(document).on('click', '#sfsidepanel .sf-toggle-admin-menu', function() {
            $('#sfsidepanel').toggleClass('sf-admin-menu-mobile-show');
        });
    
        $(document).on('click', '.ui-accordion .ui-accordion-content .sfsideitem', function(e) {
			e.preventDefault();
            $('.ui-accordion .ui-accordion-content .sfsideitem').removeClass('sf-active');
            $(this).addClass('sf-active');
        });
    }

    function setupTooltips() {
        if (sp_platform_vars.device == 'desktop' && sp_platform_vars.tooltips == true) {
            $(document).tooltip({
                tooltipClass: "ttip",
                position: {
                    my: "center",
                    at: "center top-30"
                },
                track: false
            });
        }
    }

    //activate plugin license
    function spl_license_activate(){

        $(document).on("click", 'input[name="SP_license_activate"]', function(e) {
            e.preventDefault();
            var s = $(this).parents("form").find('input[name="sp_addon_license_key"]').val(),
                n = $(this).parents("form").find('input[name="sp_item_name"]').val(),
                a = $(this).parents("form").find('input[name="sp_item"]').val(),
				em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val(),
                i = $(this).parents("form").find('input[name="sp_item_id"]').val();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    licence_key: s,
                    item_name: n,
                    sp_item: a,
                    sp_item_id: i,
                    sp_action: "activate_license"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function() {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function() {}
            });
        });
    }

    //deactivate plugin license
    function spl_license_deactivate(){

        $(document).on("click", 'input[name="SP_license_deactivate"]', function(e) {
            e.preventDefault();
            var s = $(this).parents("form").find('input[name="sp_addon_license_key"]').val(),
                n = $(this).parents("form").find('input[name="sp_item_name"]').val(),
                a = $(this).parents("form").find('input[name="sp_item"]').val(),
				em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val(),
                i = $(this).parents("form").find('input[name="sp_item_id"]').val();

            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    licence_key: s,
                    item_name: n,
                    sp_item: a,
                    sp_item_id: i,
                    sp_action: "deactivate_license"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function() {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function() {}
            });
        });
    }

    //save store url
    function save_store_url(){

        $(document).on("click", 'input[name="save_store_url"]', function(e) {
            e.preventDefault();
            var s = $(this).parents("form").find('input[name="sp_licensing_server_url"]').val(),
				em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    sp_licensing_server_url: s,
                    sp_action: "sp_licensing_server_url"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function() {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function() {}
            });
        });
    }

    //Open pop-up with update information
    function spPluginUpdateModel(){

        $(document).on("click", '.spPluginUpdate', function (e) {
            e.preventDefault();
            var s = $(this).attr('data-site'),
                ti = $(this).attr('data-href'),
                tt = $(this).attr('data-label'),
				em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val(),
                th = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    changelog_link: ti,
                    sp_action: "changelog_link"
                },
                timeout: 5000,
                success: function (e) { 
                   spj.dialogOpen(th, '', tt, '1000', 'auto', 'center', '', '', e.message);
                },
                error: function (e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function () {
                    
                },
                complete: function () {}
            });
        });
    }

    //Force to check Update
    function spForceUpdateCheck(){

        $(document).on("click", '#force_update_check', function (e) {
            e.preventDefault();
			var em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    sp_action: "force_update_check"
                },
                timeout: 50000,
                success: function (e) { 
                   e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function () {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function () {}
            });
        });
    }
	
	//spLicenseRemove
	function spLicenseRemove(){

        $(document).on("click", 'input[name="SP_license_remove"]', function(e) {
            e.preventDefault();
            var s = $(this).parents("form").find('input[name="sp_addon_license_key"]').val(),
                n = $(this).parents("form").find('input[name="sp_item_name"]').val(),
                a = $(this).parents("form").find('input[name="sp_item"]').val(),
				em = $('.sp-licensing-instructions-tab').find('input[name="ajax_error_message"]').val(),
                i = $(this).parents("form").find('input[name="sp_item_id"]').val();

            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    licence_key: s,
                    item_name: n,
                    sp_item: a,
                    sp_item_id: i,
                    sp_action: "license_remove"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html(em), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function() {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function() {}
            });
        });
    }
    
    
    
        spj.resizeThemeItems = function(){
                
                
                var rows = new Array();
                
                
                var row = new Array();
                $('.spThemeContainer .spTheme').each(function() {
                    if($(this).prev().length > 0) {
                        if($(this).position().top != $(this).prev().position().top) {
                                rows.push(row);
                                row = new Array();
                        }
                        row.push($(this));
                    }
                    else {
                          row.push($(this));
                    }
                });
                
                if( row.length > 0 ) {
                        rows.push(row);
                }
                
                
                
                
                
                var maxHeight = 0;
                $.each( rows, function() {
                        maxHeight = 0;
                        $.each( this, function() {
                                
                                var _height = $(this).find('.spThemeInner').height();
                                
                                if ( _height > maxHeight) { 
                                        maxHeight = _height; 
                                }
                             
                        });
                        
                        $.each( this, function() {
                                $(this).height(maxHeight);
                        });
                        
                });

        }

        

}(window.spj = window.spj || {}, jQuery));