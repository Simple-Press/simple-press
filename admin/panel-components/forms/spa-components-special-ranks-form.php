<?php
/*
  Simple:Press
  Admin Components Special Ranks Form
  $LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
  $Rev: 15601 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

function spa_special_rankings_form($rankings) {
    global $tab;
    ?>
    <script>
        spj.loadAjaxForm('sfaddspecialrank', 'sfreloadfr');
    </script>
    <?php
    spa_paint_open_nohead_tab(true);
    $ajaxURL = wp_nonce_url(SPAJAXURL . 'components-loader&amp;saveform=specialranks&amp;targetaction=newrank', 'components-loader');
    ?>
    <form action="<?php echo $ajaxURL; ?>" method="post" name="sfaddspecialrank" id="sfaddspecialrank" class="sf-opener">
        <?php echo sp_create_nonce('special-rank-new'); ?>
        <div class="sf-panel">
            <fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                    <h4><?php echo SP()->primitives->admin_text('Special Forum Ranks') ?></h4>
                    <span class="sf-icon-button sf-opener-button-open"><span class="sf-icon sf-add"></span></span>
                    <?php echo spa_paint_help('special-ranks') ?>
                </div>
                <div class="sf-opener-target">
                    <div class="sf-form-row">
                        <label><?php echo SP()->primitives->admin_text('New Special Rank Name'); ?></label>
                        <?php spa_paint_single_input('specialrank', '', false, true); ?>
                        <input type="submit" class="sf-button-primary" id="addspecialrank" name="addspecialrank" value="<?php echo SP()->primitives->admin_text('Add Special Rank') ?>" />
                    </div>
                </div>
                <div class="sf-form-row">
                    <?php if ($rankings) : ?>
                        <table class="widefat sf-table-small sf-table-mobile">
                            <thead>
                            <tr>
                                <th style="width: 30%"><?php SP()->primitives->admin_etext('Rank Name') ?></th>
                                <th style="width: 20%"><?php SP()->primitives->admin_etext('Badge') ?></thn>
                                <th style="width: 40%"><?php SP()->primitives->admin_etext('Manage') ?></th>
                                <th style="width: 10%"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $badges = spa_get_custom_icons(SP_STORE_DIR . '/' . SP()->plugin->storage['ranks'] . '/', SP_STORE_URL . '/' . SP()->plugin->storage['ranks'] . '/');

                            foreach ($rankings as $rank) {
                                $ajaxURL = wp_nonce_url(SPAJAXURL . 'components-loader&amp;saveform=specialranks&amp;targetaction=updaterank&amp;id=' . $rank['meta_id'], 'components-loader');
                                $delsite = wp_nonce_url(SPAJAXURL . 'components&amp;targetaction=del_specialrank&amp;key=' . $rank['meta_id'], 'components');

                                $base = wp_nonce_url(SPAJAXURL . 'components-loader', 'components-loader');
                                $target = 'members-' . $rank['meta_id'];
                                $image = SPADMINIMAGES;
                                ?>
                                <tr id="srank<?php echo $rank['meta_id']; ?>">
                                    <td colspan="4" class="sf-padding-none sf-border-none">
                                        <form action="<?php echo $ajaxURL; ?>" method="post" id="sfspecialrankupdate<?php echo $rank['meta_id']; ?>" name="sfspecialrankupdate<?php echo $rank['meta_id']; ?>">
                                            <?php
                                            echo sp_create_nonce('special-rank-update');
                                            ?>
                                            <table class='widefat sf-table-small sf-table-mobile sf-border-none'>
                                                <tr>
                                                    <td style="width: 30%">
                                                        <?php echo $rank['meta_key'] ?>
                                                        </tdi>
                                                    <td style="width: 20%; text-align:center">
                                                        <?php echo spa_get_saved_icon_html($rank['meta_value']['badge'], 'ranks') ?>
                                                    </td>
                                                    <td style="width: 40%">
                                                        <?php $loc = '#sfrankshow-' . $rank['meta_id']; ?>
                                                        <input type="button" id="show<?php echo $rank['meta_id']; ?>" class="sf-button-secondary spSpecialRankShow" value="<?php echo esc_js(SP()->primitives->admin_text('Show')) ?>" data-loc="<?php echo $loc; ?>" data-site="<?php echo wp_nonce_url(SPAJAXURL . 'components&amp;targetaction=show&amp;key=' . $rank['meta_id'], 'components') ?>" data-img="<?php echo SPCOMMONIMAGES . 'working.gif' ?>" data-id="<?php echo $rank['meta_id']; ?>" />

                                                        <input type="button" id="remove<?php echo $rank['meta_id']; ?>" class="sf-button-secondary spSpecialRankForm" value="<?php SP()->primitives->admin_etext('Remove'); ?>" data-loc="<?php echo $loc; ?>" data-form="delmembers" data-base="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $rank['meta_id']; ?>" />

                                                        <input type="button" id="add<?php echo $rank['meta_id']; ?>" class="sf-button-secondary spSpecialRankForm" value="<?php SP()->primitives->admin_etext('Add'); ?>" data-loc="<?php echo $loc; ?>" data-form="addmembers" data-base="<?php echo $base; ?>" data-target="<?php echo $target; ?>" data-img="<?php echo $image; ?>" data-id="<?php echo $rank['meta_id']; ?>" />
                                                    </td>
                                                    <td style="width: 10%">
                                                <span class="sf-item-controls">
                                                    <span class="sf-icon-button sf-small sf-little sf-edit-item"><span class="sf-icon sf-edit"></span></span>
                                                    <span class="sf-icon-button sf-small sf-little spDeleteRowReload"
                                                          title="<?php SP()->primitives->admin_etext('Delete Special Rank'); ?>"
                                                          data-url="<?php echo $delsite; ?>"
                                                          data-target="srank<?php echo $rank['meta_id']; ?>"
                                                          data-reload="sfreloadfr"
                                                    ><span class="sf-icon sf-delete"></span>
                                                    </span>
                                                </span>
                                                    </td>
                                                </tr>
                                                <tr class="sf-Hide">
                                                    <td>
                                                        <input type="hidden" name="<?php echo('currentname[' . $rank['meta_id'] . ']'); ?>" value="<?php echo $rank['meta_key']; ?>" />
                                                        <input type="text" size="13" tabindex="<?php echo $tab; ?>" name="<?php echo('specialrankdesc[' . $rank['meta_id'] . ']'); ?>" value="<?php echo $rank['meta_key']; ?>" />
                                                    </td>
                                                    <td width="20%">
                                                        <?php
                                                        spa_select_iconset_icon_picker('specialrankbadge[' . $rank['meta_id'] . ']', SP()->primitives->admin_text('Select Badge'), array('Badges' => $badges), $rank['meta_value']['badge'], false);
                                                        ?>
                                                    </td>
                                                    <td colspan="2">
                                                <span class="sf-item-controls">
                                                    <input type="submit" class="sf-button-primary" id="updatespecialrank<?php echo $rank['meta_id']; ?>" name="updatespecialrank<?php echo $rank['meta_id']; ?>" value="<?php SP()->primitives->admin_etext('Save'); ?>" />
                                                    <span class="sf-icon-button"><span class="sf-icon sf-cancel spHideRow"></span></span>
                                                </span>
                                                    </td>
                                                </tr>
                                                <tr id="sfrankshow-<?php echo $rank['meta_id']; ?>" class="sfinline-form sf-border-none">
                                                    <td colspan="4" class="sf-padding-none">
                                                        <div id="members-<?php echo $rank['meta_id']; ?>"></div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                    </td>
                                </tr>
                                <script>
                                    spj.loadAjaxForm('sfspecialrankupdate<?php echo $rank['meta_id']; ?>', 'sfreloadfr');
                                </script>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </fieldset>
        </div>

        <?php do_action('sph_components_add_rank_panel'); ?>
    </form>
    </div>
    <?php
}
