<?php
/*
  Simple:Press
  Admin Permissions Add Permission Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}
function spa_permissions_add_permission_form() {
    ?>
    <script>
        spj.loadAjaxForm('sfrolenew', 'sfreloadpb');
    </script>
    <?php
    # Get correct tooltips file
    $lang = spa_get_language_code();
    if (empty($lang))
        $lang = 'en';
    $ttpath = SPHELP . 'admin/tooltips/admin-permissions-tips-' . $lang . '.php';
    if (file_exists($ttpath) == false)
        $ttpath = SPHELP . 'admin/tooltips/admin-permissions-tips-en.php';
    if (file_exists($ttpath))
        require_once $ttpath;

    spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL . 'permissions-loader&amp;saveform=addperm', 'permissions-loader');
    ?>
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfrolenew" name="sfrolenew">
        <?php
        echo sp_create_nonce('forum-adminform_rolenew');
        spa_paint_open_tab(SP()->primitives->admin_text('Add New Permission Set'));


        spa_paint_open_fieldset(SP()->primitives->admin_text('Permission Set Details'), 'true', 'create-new-permission-set');
        spa_paint_input(SP()->primitives->admin_text('Set Name'), "role_name", '', false, true);
        spa_paint_wide_textarea('Set Description', 'role_desc', '', '', 4);
        spa_paint_select_start(SP()->primitives->admin_text('Clone Existing'), 'role', 'role');
        spa_display_permission_select('', false);
        spa_paint_select_end('<span class="sf-sublabel sf-sublabel-small">' . SP()->primitives->admin_text('Select an existing Permission Set to Clone') . '</span>');
        spa_paint_close_fieldset();
        ?>
        <div class="_sf-form-submit-bar sf-mb-15">
            <input type="submit" class="sf-button-primary" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" />
        </div>

        <?php
        spa_paint_open_fieldset(SP()->primitives->admin_text('Permission Set Actions'), 'true', 'create-new-permission-set');
        ?>
            <div class="sf-alert-block sf-info">
                <p><span class="sf-icon sf-ignore-guest sf-red sf-small"></span>
                    <?php echo SP()->primitives->admin_text('Ignored for Guest Users') ?></p>
                <p><span class="sf-icon sf-requires-enable sf-green sf-small"></span>
                    <?php echo SP()->primitives->admin_text('Require enabling to use') ?></p>
                <p><span class="sf-icon sf-warning sf-yellow sf-small"></span>
                    <?php echo SP()->primitives->admin_text('Use with great care') ?></p>
            </div>
        <?php
        sp_build_site_auths_cache();

        $sql = 'SELECT auth_id, auth_name, auth_cat, authcat_name, warning FROM ' . SPAUTHS . '
							JOIN ' . SPAUTHCATS . ' ON ' . SPAUTHS . '.auth_cat = ' . SPAUTHCATS . '.authcat_id
							WHERE active = 1
							ORDER BY auth_cat, auth_id';
        $authlist = SP()->DB->select($sql);

        $tmp = array();
        foreach ($authlist as $a) {
            if (!isset($tmp[$a->authcat_name])) {
                $tmp[$a->authcat_name] = array();
            }
            array_push($tmp[$a->authcat_name], $a);
        }
        ?>
        <div class="sf-form-row">
        <ul>
            <?php foreach ($tmp as $name => $arr): ?>
                <li class="">
                    <h4 class="sf-mt-15 sf-mb-15">
                        <?php SP()->primitives->admin_etext($name) ?></span>
                    </h4>
                    <div class="sp-permission-body">
                        <?php foreach ($arr as $a): ?>
                            <?php
                            $auth_id = $a->auth_id;
                            $auth_name = $a->auth_name;
                            $authWarn = (empty($a->warning)) ? false : true;
                            $warn = ($authWarn) ? " permwarning" : '';
                            $tip = ($authWarn) ? " class='permwarning' title='" . esc_js(SP()->primitives->admin_text($a->warning)) . "'" : '';

                            $button = 'b-' . $auth_id;
                            if (SP()->core->forumData['auths'][$auth_id]->ignored || SP()->core->forumData['auths'][$auth_id]->enabling || $authWarn) {
                                $span = '';
                            } else {
                                $span = ' colspan="2" ';
                            }
                            ?>
                            <div<?php //echo $tip;             ?> class="sp-permission-item">
                                <div class="permentry<?php echo $warn; ?>" >
                                    <input type="checkbox" name="<?php echo $button; ?>" id="sf<?php echo $button; ?>"  />
                                    <label for="sf<?php echo $button; ?>" class="sflabel">
                                        <?php SP()->primitives->admin_etext(SP()->core->forumData['auths'][$auth_id]->auth_desc); ?>
                                    </label>
                                    <div class="sf-icons">
                                        <?php if ($span == ''): ?>
                                            <?php if (SP()->core->forumData['auths'][$auth_id]->enabling): ?>
                                                <span class="sf-icon sf-requires-enable sf-green sf-small" title="<?php echo SP()->primitives->admin_text('Requires Enabling') ?>"></span>
                                            <?php endif ?>
                                            <?php if (SP()->core->forumData['auths'][$auth_id]->ignored): ?>
                                                <span class="sf-icon sf-ignore-guest sf-red sf-small" title="<?php echo SP()->primitives->admin_text('Ignored for Guests') ?>"></span>
                                            <?php endif ?>
                                            <?php if ($authWarn): ?>
                                                <span class="sf-icon sf-warning sf-yellow sf-small" title="<?php echo SP()->primitives->admin_text('Use with Caution') ?>"></span>
                                            <?php endif ?>
                                            <span class="sf-icon sf-about sf-blue sf-small" title="<?php echo $tooltips[$auth_name]; ?>"></span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
        </div>
        <?php
        spa_paint_close_fieldset();
        spa_paint_close_panel();
        do_action('sph_perm_add_perm_panel');
        ?>
        <div class="sf-form-submit-bar sf-mobile-show">
            <input type="submit" class="sf-button-primary" name="saveit" value="<?php SP()->primitives->admin_etext('Create New Permission'); ?>" />
        </div>
        <?php
        spa_paint_close_tab();
        ?>
    </form>
    <?php
}
