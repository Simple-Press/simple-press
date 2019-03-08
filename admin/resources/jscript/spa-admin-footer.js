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
        activateAccordion();
        setupTooltips();
        highlightMenu();
        spl_license_activate();
        spl_license_deactivate();
        save_store_url();
        spPluginUpdateModel();
        spForceUpdateCheck();

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

    //activate plugin license
    function spl_license_activate(){

        $(document).on("click", 'input[name="SP_license_activate"]', function(e) {
            e.preventDefault();
            var s = $(this).parents("form").find('input[name="sp_sample_license_key"]').val(),
                n = $(this).parents("form").find(".sp_item_name").val(),
                a = $(this).parents("form").find('input[name="sp_itemn"]').val(),
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
                    sp_itemn_id: i,
                    sp_action: "activate_license"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html("Something Went Wrong Please Try Again!"), $("#sfmsgspot").fadeOut(2000))
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
            var s = $(this).parents("form").find('input[name="sp_sample_license_key"]').val(),
                n = $(this).parents("form").find(".sp_item_name").val(),
                a = $(this).parents("form").find('input[name="sp_itemn"]').val(),
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
                    sp_itemn_id: i,
                    sp_action: "deactivate_license"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html("Something Went Wrong Please Try Again!"), $("#sfmsgspot").fadeOut(2000))
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
            var s = $(this).parents("form").find('input[name="sp_sample_store_url"]').val();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    sp_sample_store_url: s,
                    sp_action: "save_store_url"
                },
                timeout: 5000,
                success: function(e) {
                    e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html("Something Went Wrong Please Try Again!"), $("#sfmsgspot").fadeOut(2000))
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
                    "timeout" === s && ($("#sfmsgspot").html("Something Went Wrong Please Try Again!"), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function () {
                    
                },
                complete: function () {}
            });
        });
    }

    //Force to chek Update
    function spForceUpdateCheck(){

        $(document).on("click", '#force_update_check', function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "license-check",
                    sp_action: "force_update_check"
                },
                timeout: 5000,
                success: function (e) { 
                   e.message && "" != e.message && ($("#sfmsgspot").fadeIn(), $("#sfmsgspot").html(e.message), $("#sfmsgspot").fadeOut(3000)), setTimeout(function() {
                        $("#acclicensing").click()
                    }, 4000)
                },
                error: function(e, s, n) {
                    "timeout" === s && ($("#sfmsgspot").html("Something Went Wrong Please Try Again!"), $("#sfmsgspot").fadeOut(2000))
                },
                beforeSend: function () {
                    $("#sfmsgspot").show(), $("#sfmsgspot").html(sp_platform_vars.pWait)
                },
                complete: function () {}
            });
        });
    }

}(window.spj = window.spj || {}, jQuery));