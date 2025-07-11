<?php
/*
  Simple:Press
  Admin User Groups Add Member Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

function spa_usergroups_add_members_form($usergroup_id) {
    $formId = sprintf('sfmembernew-%s-%d', uniqid(), esc_attr($usergroup_id));
    ?>
    <script>
        (function (spj, $, undefined) {
            $(document).ready(function () {
                $('#<?php echo esc_js($formId); ?>').ajaxForm({
                    target: '#sfmsgspot',
                });
            });
        }(window.spj = window.spj || {}, jQuery));
    </script>
    <?php
    spa_paint_options_init();

    $ajaxURL = wp_nonce_url(SPAJAXURL . 'usergroups-loader&amp;saveform=addmembers', 'usergroups-loader');
    $url = wp_nonce_url(SPAJAXURL . 'memberships&amp;targetaction=add', 'memberships');
    $target = 'sfmsgspot';
    $smessage = SP()->primitives->admin_text('Please Wait - Processing');
    $emessage = SP()->primitives->admin_text('Users added');
    ?>
    <form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="<?php echo esc_attr($formId); ?>" name="sfmembernew<?php echo esc_attr($usergroup_id) ?>"
          onsubmit="spj.addDelMembers(
                      '<?php echo esc_js($formId); ?>',
                      '<?php echo esc_js($url); ?>',
                      '<?php echo esc_js($target); ?>',
                      '<?php echo esc_js($smessage); ?>',
                      '<?php echo esc_js($emessage); ?>',
                      0, 50, '#amid<?php echo esc_js($usergroup_id); ?>', 'add');">
        <?php
        spa_paint_open_nohead_tab(true, '');
        echo '<input type="hidden" name="'.esc_attr('forum-adminform_membernew').'" value="'.esc_attr(wp_create_nonce('forum-adminform_membernew')).'" />';

        $sfmemberopts = SP()->options->get('sfmemberopts');

        if (!isset($sfmemberopts['sfsinglemembership'])) {
            $singleGrp = false;
        } else {
            $singleGrp = $sfmemberopts['sfsinglemembership'];
        }
        $singleOpt = ($singleGrp) ? SP()->primitives->admin_text('On') : SP()->primitives->admin_text('Off');
        $singleMsg = ($singleGrp) ? SP()->primitives->admin_text('Any members moved will be deleted from current user group memberships') : SP()->primitives->admin_text('Any members moved will be retained in current user group memberships');
        ?>
        <input type="hidden" name="usergroup_id" value="<?php echo esc_attr($usergroup_id); ?>" />
        <?php
        $from = esc_js(SP()->primitives->admin_text('No members selected'));
        $action = 'addug';
        require_once SP_PLUGIN_DIR . '/admin/library/ajax/spa-ajax-multiselect.php';
        ?>
        <div class="clearboth"></div>
        <?php
        do_action('sph_usergroup_add_member_panel');
        ?>
        <span class="sf-controls">
            <input type="submit" class="sf-button-primary" name="sfmembernew<?php echo esc_attr($usergroup_id); ?>" value="<?php echo esc_attr(SP()->primitives->admin_text('Add Members')); ?>" />
        </span>
        <span class="sf-button sf-hidden-important" id='onFinish'></span>
        <div class="pbar" id="progressbar"></div>
        <?php
        spa_paint_close_container();
        spa_paint_close_tab();
        ?>
    </form>
    <?php
}
