<?php
/*
  Simple:Press
  Admin Components Forum Ranks Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

require_once SP_PLUGIN_DIR . '/admin/panel-components/forms/spa-components-special-ranks-form.php';

function spa_components_forumranks_form() {
    $ajaxurl = htmlspecialchars_decode(wp_nonce_url(SPAJAXURL . 'uploader', 'uploader'));
    ?>
    <script>
        (function (spj, $, undefined) {
            $(document).ready(function () {
                spj.loadAjaxForm('sfforumranksform', 'sfreloadfr');

                var button = $('#sf-upload-button'), interval;
                new AjaxUpload(button, {
                    action: '<?php echo $ajaxurl; ?>',
                    name: 'uploadfile',
                    data: {
                        saveloc: '<?php echo addslashes(SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/'); ?>'
                    },
                    onSubmit: function (file, ext) {
                        /* check for valid extension */
                        if (!(ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))) {
                            $('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(SP()->primitives->admin_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
                            return false;
                        }
                        /* change button text, when user selects file */
                        //utext = '<?php echo esc_js(SP()->primitives->admin_text('Uploading')); ?>';
                        //button.text(utext);
                        /* If you want to allow uploading only 1 file at time, you can disable upload button */
                        this.disable();
                        /* Uploding -> Uploading. -> Uploading... */
                        //interval = window.setInterval(function(){
                        //	var text = button.text();
                        //	if (text.length < 13){
                        //		button.text(text + '.');
                        //	} else {
                        //		button.text(utext);
                        //	}
                        //}, 200);
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
                            //var site = "<?php echo SPAJAXURL; ?>components&amp;_wpnonce=<?php echo wp_create_nonce('components'); ?>&amp;targetaction=delbadge&amp;file=" + file;
                            //var count = document.getElementById('rank-count');
                            //var rcount = parseInt(count.value) + 1;
                            //$('#sf-rank-badges').append('<tr id="rankbadge' + rcount + '" class="spMobileTableData"><td data-label="<?php SP()->primitives->admin_etext('Filename'); ?>">' + file + '</td><td data-label="<?php SP()->primitives->admin_etext('Badge'); ?>"><img class="sfrankbadge" src="<?php echo SPRANKS; ?>/' + file + '" alt="" /></td><td data-label="<?php SP()->primitives->admin_etext('Remove'); ?>"><span class="sf-item-controls"><span class="sf-icon sf-delete spDeleteRow" title="<?php echo esc_js(SP()->primitives->admin_text('Delete Rank Badge')); ?>" data-url="' + site + '" data-target="rankbadge' + rcount + '"></span></span></td></tr>');
                            //$('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(SP()->primitives->admin_text('Forum badge uploaded!')); ?></p>');
                            //$('.ui-tooltip').hide();
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
    <form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumranksform" name="sfforumranks" class="sf-opener">
        <?php echo sp_create_nonce('forum-adminform_forumranks'); ?>
        <?php
        spa_paint_options_init();

#== FORUM RANKS Tab ============================================================

        spa_paint_open_tab(/* SP()->primitives->admin_text('Components').' - '. */SP()->primitives->admin_text('Forum Ranks'), true);
        ?>
        <div class="sf-panel-body-top">
            <div class="sf-panel-body-top-left">
                <h4><?php echo SP()->primitives->admin_text('Standard Forum Ranks') ?></h4>
                <span><?php echo SP()->primitives->admin_text('Design is not just what it looks like and feels like. Design is how it works.') ?></span>
            </div>
            <div class="sf-panel-body-top-right sf-mobile-btns">
                <?php echo spa_paint_help('forum-ranks') ?>
                <span class="sf-icon-button sf-opener-button-open"><span class="sf-icon sf-add"></span></span>
            </div>
        </div>
        <?php
        //spa_paint_open_panel();
        //	spa_paint_open_fieldset(SP()->primitives->admin_text('Forum Ranks'), true, 'forum-ranks');
        spa_paint_rankings_table($rankings);
        //	spa_paint_close_fieldset();
        //spa_paint_close_panel();

        spa_paint_close_container();
        ?>
        <!--<div class="sf-form-submit-bar">
        <input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Forum Ranks Components'); ?>" />
        </div>-->
        <?php
        spa_paint_close_tab();
        ?>
    </form>
    <div class="sfform-panel-spacer"></div>
    <?php
    $special_rankings = spa_get_specialranks_data();
    spa_special_rankings_form($special_rankings);

    //spa_paint_open_tab(SP()->primitives->admin_text('Components').' - '.SP()->primitives->admin_text('Forum Rank Badges'), true);
    spa_paint_open_nohead_tab(true);
    ?>
    <div class="sf-panel-body-top">
        <div class="sf-panel-body-top-left">
            <h4><?php echo SP()->primitives->admin_text('Forum Rank Badges') ?></h4>
            <span><?php echo SP()->primitives->admin_text('Design is not just what it looks like and feels like. Design is how it works') ?>.</span>
        </div>
        <div class="sf-panel-body-top-right sf-mobile-btns">
            <?php echo spa_paint_help('badges-upload') ?>
            <?php
            $loc = SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/';
            spa_paint_file(SP()->primitives->admin_text('Select rank badge to upload'), 'newrankfile', false, true, $loc);
            ?>
        </div>
    </div>
    <?php
    //spa_paint_open_panel();
    //	spa_paint_open_fieldset(SP()->primitives->admin_text('Custom rank badge upload'), true, 'badges-upload');
    //		$loc = SP_STORE_DIR.'/'.SP()->plugin->storage['ranks'].'/';
    //		spa_paint_file(SP()->primitives->admin_text('Select rank badge to upload'), 'newrankfile', false, true, $loc);
    //	spa_paint_close_fieldset();
    //spa_paint_close_panel();
    //spa_paint_open_panel();
    //	spa_paint_open_fieldset(SP()->primitives->admin_text('Custom Rank Badges'), true, 'rank-badges');
    spa_paint_rank_images();
    //	spa_paint_close_fieldset();
    //spa_paint_close_panel();

    spa_paint_close_container();

    do_action('sph_components_ranks_panel');
    spa_paint_close_tab();
    //echo '<div class="sfform-panel-spacer"></div>';
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
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('NUMBER OF POSTS'); ?></th>
                <th class='sf-text-al-center'><?php SP()->primitives->admin_etext('MEMBERSHIP GROUP'); ?></th>
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
                    <input type='text' size="12"  class='wp-core-ui' tabindex='<?php echo $tab; ?>' name='rankdesc[]' id="new-range-name" value='' placeholder="<?= SP()->primitives->admin_etext('Forum rank name') ?>"/>
                    <input type='hidden' name='rankid[]' value='-1' />
                </td>
                <?php $tab++; ?>

                <td data-label='<?php SP()->primitives->admin_etext('NUMBER OF POSTS'); ?>'>
                    <input type='text' class='wp-core-ui' size='5' tabindex='<?php echo $tab; ?>' name='rankpost[]' id="new-range-post" value='' placeholder="<?= SP()->primitives->admin_etext('# of posts for rank') ?>"/>
                </td>
                <?php $tab++; ?>

                <td data-label='<?php SP()->primitives->admin_etext('MEMBERSHIP GROUP'); ?>'>
                    <div class='sf-select-wrap'>
                        <select class="wp-core-ui sp-input-40 sf-panel-comp-forum-rank-select" name="rankug[]" id="new-range-group" >
                            <?php
                            $out = '<option value="none">' . SP()->primitives->admin_text('Select Group') . '</option>';
                            foreach ($usergroups as $usergroup) {
                                $out .= '<option value="' . $usergroup->usergroup_id . '">' . SP()->displayFilters->title($usergroup->usergroup_name) . '</option>';
                            }
                            echo $out;
                            ?>
                        </select>
                    </div>
                </td>
                <?php $tab++; ?>

                <td data-label='<?php SP()->primitives->admin_etext('Badge'); ?>' id="new-range-badge" >
                    <?php spa_select_iconset_icon_picker('rankbadge[]', __('Select Badge'), array('Badges' => $badges), '', false); ?>
                </td>
                <?php $tab++; ?>

                <td>
                    <div class="sf-item-controls">
                        <input type="submit" class="sf-button-primary" name="saveit" value="<?php SP()->primitives->admin_etext('Save'); ?>" />
                        <span id="new-range-cancel" class="sf-icon-button"><span class="sf-icon sf-cancel spDeleteRow"></span></span>	
                    </div>
                </td>
            </tr>
            <?php
            for ($x = 0; $x < count($rankings); $x++) {
                ?>
                <tr id="vrank<?php echo($x); ?>">
                    <td data-label='<?php SP()->primitives->admin_etext('Rank Name'); ?>' class="sf-Left">
                        <span><?php echo esc_attr($ranks['title'][$x]); ?></span>
                    </td>
                    <td data-label='<?php SP()->primitives->admin_etext('NUMBER OF POSTS'); ?>' class="sf-Left">
                        <span><?php echo $ranks['posts'][$x]; ?>&nbsp;posts</span>
                    </td>
                    <td data-label='<?php SP()->primitives->admin_etext('MEMBERSHIP GROUP'); ?>' class="sf-Left">
                        <?php
                        if ($ranks['usergroup'][$x] == 'none') {
                            echo "<span>" . SP()->primitives->admin_text('None') . "</span>";
                        } else {
                            foreach ($usergroups as $usergroup) {
                                if ($ranks['usergroup'][$x] == $usergroup->usergroup_id) {
                                    echo "<span>" . SP()->displayFilters->title($usergroup->usergroup_name) . "</span>";
                                }
                            }
                        }
                        ?>
                    </td>
                    <td data-label='<?php SP()->primitives->admin_etext('Badge'); ?>' class="sf-Left">
                        <?php echo spa_get_saved_icon_html($ranks['badge'][$x]) ?>
                    </td>
                    <td data-label='<?php SP()->primitives->admin_etext('Remove'); ?>' class="sf-Left">
                        <span class="sf-item-controls">
                            <span class="sf-icon-button sf-small sf-little sf-edit-item"><span class="sf-icon sf-edit"></span></span>
                            <?php $site = wp_nonce_url(SPAJAXURL . 'components&amp;targetaction=del_rank&amp;key=' . $ranks['id'][$x], 'components'); ?>
                            <span class="sf-icon-button sf-small sf-little spDeleteRowReload"
                                  title="<?php SP()->primitives->admin_etext('Delete Rank'); ?>"
                                  data-url="<?php echo $site; ?>"
                                  data-target="rank<?php echo $x; ?>"
                                  data-reload="sfreloadfr"
                                  ><span class="sf-icon sf-delete"></span>
                            </span>
                        </span>
                    </td>
                </tr>
                <tr id="rank<?php echo($x); ?>" class="sf-Hide">

                    <td data-label='<?php SP()->primitives->admin_etext('Rank Name'); ?>'>
                        <input type='text' size="12" class='wp-core-ui' tabindex='<?php echo $tab; ?>' name='rankdesc[]' value='<?php echo esc_attr($ranks['title'][$x]); ?>' />
                        <input type='hidden' name='rankid[]' value='<?php echo esc_attr($ranks['id'][$x]); ?>' />
                    </td>
                    <?php $tab++; ?>

                    <td data-label='<?php SP()->primitives->admin_etext('NUMBER OF POSTS'); ?>'>
                        <input type='text' class='wp-core-ui' size='5' tabindex='<?php echo $tab; ?>' name='rankpost[]' value='<?php echo $ranks['posts'][$x]; ?>' />
                        <?php //echo ' '.SP()->primitives->admin_text('Posts'); ?>
                    </td>
                    <?php $tab++; ?>

                    <td data-label='<?php SP()->primitives->admin_etext('MEMBERSHIP GROUP'); ?>'>
                        <select class="wp-core-ui sf-panel-comp-forum-rank-select" name="rankug[]">
                            <?php
                            if ($ranks['usergroup'][$x] == 'none') {
                                $out = '<option value="none" selected="selected">' . SP()->primitives->admin_text('None') . '</option>';
                            } else {
                                $out = '<option value="none">' . SP()->primitives->admin_text('None') . '</option>';
                            }
                            foreach ($usergroups as $usergroup) {
                                if ($ranks['usergroup'][$x] == $usergroup->usergroup_id) {
                                    $selected = ' SELECTED';
                                } else {
                                    $selected = '';
                                }
                                $out .= '<option value="' . $usergroup->usergroup_id . '"' . $selected . '>' . SP()->displayFilters->title($usergroup->usergroup_name) . '</option>';
                            }
                            echo $out;
                            ?>
                        </select>
                    </td>
                    <?php $tab++; ?>

                    <td data-label='<?php SP()->primitives->admin_etext('Badge'); ?>'>
                        <?php spa_select_iconset_icon_picker('rankbadge[]', __('Select Badge'), array('Badges' => $badges), $ranks['badge'][$x], false); ?>

                    </td>
                    <?php $tab++; ?>

                    <td data-label='<?php SP()->primitives->admin_etext('Remove'); ?>'>
                        <div class="sf-item-controls">
                            <input type="submit" class="sf-button-primary" name="saveit" value="<?php SP()->primitives->admin_etext('Save'); ?>" />
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
