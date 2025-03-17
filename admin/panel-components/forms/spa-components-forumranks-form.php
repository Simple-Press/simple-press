<?php
/*
  Simple:Press
  Admin Components Forum Ranks Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

require_once SP_PLUGIN_DIR . '/admin/panel-components/forms/spa-components-special-ranks-form.php';

function spa_components_forumranks_form() {
    $ajaxurl = wp_nonce_url(SPAJAXURL . 'uploader', 'uploader');
    ?>
    <script>
        (function (spj, $, undefined) {
            $(document).ready(function () {
                spj.loadAjaxForm('sfforumranksform', 'sfreloadfr');

                var button = $('#sf-upload-button'), interval;
                new AjaxUpload(button, {
                    action: '<?php echo json_encode(esc_url_raw($ajaxurl), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>',
                    name: 'uploadfile',
                    data: {
                        saveloc: '<?php echo esc_js(addslashes(SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/')); ?>'
                    },
                    onSubmit: function (file, ext) {
                        /* check for valid extension */
                        if (!(ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))) {
                            $('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
                            return false;
                        }
                        /* change button text, when user selects file */
                        /* If you want to allow uploading only 1 file at time, you can disable upload button */
                        this.disable();
                        /* Uploding -> Uploading. -> Uploading... */
                    },
                    onComplete: function (file, response) {
                        $('#sf-upload-status').html('');
                        //button.text('<?php echo esc_js(SP()->primitives->admin_text('Browse')); ?>');
                        window.clearInterval(interval);
                        /* re-enable upload button */
                        this.enable();
                        /* add file to the list */
                        if (response === "success") {
                            $('#sfreloadfr').click();
                        } else if (response === "invalid") {
                            $('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file has an invalid format!')); ?></p>');
                        } else if (response === "exists") {
                            $('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Sorry, the file already exists!')); ?></p>');
                        } else {
                            $('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Error uploading file!')); ?></p>');
                        }
                    }
                });
                $('.sf-Hide').css('display', 'none');
                $('.sf-edit-item').on('click', function (e) {
                    var item = $(this).parent().parent().parent().next();
                    if (item.css('display') === 'none') {
                        item.css('display', 'table-row');
                    } else {
                        item.css('display', 'none');
                    }
                });
                $('#new-range-cancel').on('click', function (e) {
                    $('#new-range-name').val('');
                    $('#new-range-post').val('');
                    $('#new-range-group').val('none');
                    $('#new-range-badge').find('select').val('all');
                    $('#new-range-badge').find('.selected-icon').find('i').remove();
                    $('#new-range-badge').find('.selected-icon').append('<i class="fip-icon-block"></i>');
                });
            });
        }(window.spj = window.spj || {}, jQuery));
    </script>

    <?php
    $rankings = spa_get_forumranks_data();
    $ajaxURL = wp_nonce_url(SPAJAXURL . 'components-loader&amp;saveform=forumranks', 'components-loader');
    ?>
        <div class='sf-panel-head'>
            <?php SP()->primitives->admin_etext('Forum Ranks'); ?>
        </div>
        <div class='sf-panel-body'>
            <div class="sf-full-form">

                <div class="sf-panel">
                    <form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sfforumranksform" name="sfforumranks" class="sf-opener">
                        <input type="hidden" name="forum-adminform_forumranks" value="<?php esc_attr(wp_create_nonce('forum-adminform_forumranks')) ;?>">
                    <fieldset class="sf-fieldset">
                        <div class="sf-panel-body-top">
                            <h4><?php echo esc_html(SP()->primitives->admin_text('Standard Forum Ranks')); ?></h4>
                            <span class="sf-icon-button sf-opener-button-open"><span class="sf-icon sf-add"></span></span>
                            <?php spa_paint_help('forum-ranks') ?>
                        </div>
                        <div class="sf-form-row">
                            <?php wp_kses_post(spa_paint_rankings_table($rankings)); ?>
                        </div>
                    </fieldset>
                    </form>
                </div>


                <div class="sf-panel">
                    <?php $special_rankings = spa_get_specialranks_data(); ?>
                    <?php wp_kses_post(spa_special_rankings_form($special_rankings)); ?>
                </div>

                <div class="sf-panel">
                    <fieldset class="sf-fieldset">
                        <div class="sf-panel-body-top">
                            <h4><?php echo esc_html(SP()->primitives->admin_text('Forum Rank Badges')); ?></h4>
                            <?php $loc = SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/'; ?>
                            <?php wp_kses_post(spa_paint_file(SP()->primitives->admin_text('Select rank badge to upload'), 'newrankfile', false, true, $loc)); ?>
                            <?php spa_paint_help('badges-upload') ?>
                        </div>
                        <div class="sf-form-row">
                            <?php spa_paint_rank_images(); ?>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    <?php do_action('sph_components_ranks_panel');
}

function spa_paint_rankings_table($rankings) {
    global $tab;

    $usergroups = spa_get_usergroups_all();

    # sort rankings from lowest to highest
    if ($rankings) {
        foreach ($rankings as $x => $info) {
            $ranks['id'][$x] = $info['meta_id'];
            $ranks['title'][$x] = $info['meta_key'];
            $ranks['posts'][$x] = $info['meta_value']['posts'];
            $ranks['usergroup'][$x] = $info['meta_value']['usergroup'];
            $ranks['badge'][$x] = (!empty($info['meta_value']['badge'])) ? $info['meta_value']['badge'] : '';
        }
        array_multisort($ranks['posts'], SORT_ASC, $ranks['title'], $ranks['usergroup'], $ranks['badge'], $ranks['id']);
    }
    ?>
    <table class="widefat sf-table-small sf-table-mobile">
        <thead>
            <tr>
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('Rank Name'); ?></th>
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('Posts'); ?></th>
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('Group'); ?></th>
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('Badge'); ?></th>
                <th class='sf-text-al-center'><?php //SP()->primitives->admin_etext('Remove');         ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $badges = spa_get_custom_icons(SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/', SP_STORE_URL . '/' . SP()->plugin->storage['ranks'] . '/');

            # display rankings info
            ?>
            <!--empty row for new rank-->
            <tr class="sf-opener-target">

                <td data-label='<?php SP()->primitives->admin_etext('Rank Name'); ?>'>
                    <input type='text' size="12"  class='wp-core-ui' tabindex='<?php echo esc_attr($tab); ?>' name='rankdesc[]' id="new-range-name" value='' placeholder="<?php esc_attr(SP()->primitives->admin_etext('Forum rank name')); ?>"/>
                    <input type='hidden' name='rankid[]' value='-1' />
                </td>
                <?php $tab++; ?>

                <td data-label='<?php SP()->primitives->admin_etext('NUMBER OF POSTS'); ?>'>
                    <input type='text' class='wp-core-ui' size='5' tabindex='<?php echo esc_attr($tab); ?>' name='rankpost[]' id="new-range-post" value='' placeholder="<?php esc_attr(SP()->primitives->admin_etext('# of posts for rank')); ?>"/>
                </td>
                <?php $tab++; ?>

                <td data-label='<?php esc_attr(SP()->primitives->admin_etext('MEMBERSHIP GROUP')); ?>'>
                    <div class='sf-select-wrap'>
                        <select class="wp-core-ui sp-input-40 sf-panel-comp-forum-rank-select" name="rankug[]" id="new-range-group" >
                            <option value="none"><?php echo esc_html(SP()->primitives->admin_text('Select Group')); ?></option>
                            <?php foreach ($usergroups as $usergroup) : ?>
                                <option value="<?php echo esc_attr($usergroup->usergroup_id); ?>">
                                    <?php echo esc_html(SP()->displayFilters->title($usergroup->usergroup_name)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </td>
                <?php $tab++; ?>

                <td data-label='<?php esc_attr(SP()->primitives->admin_etext('Badge')); ?>' id="new-range-badge" >
                    <?php spa_select_iconset_icon_picker('rankbadge[]', SP()->primitives->admin_text(('Select Badge'), array('Badges' => $badges), '', false)); ?>
                </td>
                <?php $tab++; ?>

                <td>
                    <div class="sf-item-controls">
                        <input type="submit" class="sf-button-primary" name="saveit" value="<?php esc_attr(SP()->primitives->admin_etext('Save')); ?>" />
                        <span id="new-range-cancel" class="sf-icon-button"><span class="sf-icon sf-cancel spDeleteRow"></span></span>	
                    </div>
                </td>
            </tr>
            <?php
            for ($x = 0; $x < count($rankings); $x++) {
                ?>
                <tr id="vrank<?php echo esc_attr($x); ?>">
                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('Rank Name')); ?>" class="sf-Left">
                        <span><?php echo esc_html($ranks['title'][$x]); ?></span>
                    </td>
                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('NUMBER OF POSTS')); ?>" class="sf-Left">
                        <span><?php echo esc_html($ranks['posts'][$x]); ?>&nbsp;posts</span>
                    </td>
                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('MEMBERSHIP GROUP')); ?>" class="sf-Left">
                        <?php
                        if ($ranks['usergroup'][$x] == 'none') {
                            echo "<span>" . esc_html(SP()->primitives->admin_text('None')) . "</span>";
                        } else {
                            foreach ($usergroups as $usergroup) {
                                if ($ranks['usergroup'][$x] == $usergroup->usergroup_id) {
                                    echo "<span>" . esc_html(SP()->displayFilters->title($usergroup->usergroup_name)) . "</span>";
                                }
                            }
                        }
                        ?>
                    </td>
                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('Badge')); ?>" class="sf-Left">
                        <?php echo wp_kses(
                                spa_get_saved_icon_html($ranks['badge'][$x], 'ranks')
                                , [
                                    'img' => [
                                        'class' => [],
                                        'src' => [],
                                        'alt' => [],
                                        'title' => [],
                                    ],
                                    'span' => [
                                        'class' => [],
                                        'style' => [],
                                    ],
                                ]
                        ); ?>
                    </td>
                    <td data-label="" class="sf-Left">
                        <span class="sf-item-controls">
                            <span class="sf-icon-button sf-small sf-little sf-edit-item">
                                <span class="sf-icon sf-edit"></span>
                            </span>
                            <?php $site = wp_nonce_url(SPAJAXURL . 'components&amp;targetaction=del_rank&amp;key=' . $ranks['id'][$x], 'components'); ?>
                            <span class="sf-icon-button sf-small sf-little spDeleteRowReload"
                                  title="<?php echo esc_attr(SP()->primitives->admin_text('Delete Rank')); ?>"
                                  data-url="<?php echo esc_url($site); ?>"
                                  data-target="<?php echo esc_attr('rank' . $x); ?>"
                                  data-reload="sfreloadfr">
                                <span class="sf-icon sf-delete"></span>
                            </span>
                        </span>
                    </td>
                </tr>
                <tr id="rank<?php echo esc_attr($x); ?>" class="sf-Hide">
                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('Rank Name')); ?>">
                        <input type="text" size="12" class="wp-core-ui" tabindex="<?php echo esc_attr($tab); ?>" name="rankdesc[]" value="<?php echo esc_attr($ranks['title'][$x]); ?>" />
                        <input type="hidden" name="rankid[]" value="<?php echo esc_attr($ranks['id'][$x]); ?>" />
                    </td>
                    <?php $tab++; ?>

                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('NUMBER OF POSTS')); ?>">
                        <input type="text" class="wp-core-ui" size="5" tabindex="<?php echo esc_attr($tab); ?>" name="rankpost[]" value="<?php echo esc_attr($ranks['posts'][$x]); ?>" />
                    </td>
                    <?php $tab++; ?>

                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('MEMBERSHIP GROUP')); ?>">
                        <select class="wp-core-ui sf-panel-comp-forum-rank-select" name="rankug[]">
                            <option value="none" <?php selected($ranks['usergroup'][$x], 'none'); ?>>
                                <?php echo esc_html(SP()->primitives->admin_text('None')); ?>
                            </option>
                            <?php foreach ($usergroups as $usergroup) : ?>
                                <option value="<?php echo esc_attr($usergroup->usergroup_id); ?>"
                                    <?php selected($ranks['usergroup'][$x], $usergroup->usergroup_id); ?>>
                                    <?php echo esc_html(SP()->displayFilters->title($usergroup->usergroup_name)); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </td>
                    <?php $tab++; ?>

                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('Badge')); ?>">
                        <?php spa_select_iconset_icon_picker('rankbadge[]', SP()->primitives->admin_text('Select Badge'), array('Badges' => $badges), esc_html($ranks['badge'][$x]), false); ?>
                    </td>
                    <?php $tab++; ?>

                    <td data-label="<?php echo esc_attr(SP()->primitives->admin_text('Remove')); ?>">
                        <div class="sf-item-controls">
                            <input type="submit" class="sf-button-primary" name="saveit" value="<?php echo esc_attr(SP()->primitives->admin_text('Save')); ?>" />
                            <span class="sf-icon-button"><span class="sf-icon sf-cancel spHideRow"></span></span>
                        </div>
                    </td>
                    <?php $tab++; ?>
                </tr>

                <?php
            }
            ?>

        </tbody>
    </table>
    <?php
}
