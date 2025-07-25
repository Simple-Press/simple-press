<?php
/*
  Simple:Press
  Admin User Groups Support Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
    die('Access denied - you cannot directly call this file');
}

function spa_get_mapping_data() {
    # get default usergroups
    $sfoptions = array();
    $value = SP()->meta->get('default usergroup', 'sfmembers');
    $sfoptions['sfdefgroup'] = $value[0]['meta_value'];
    $value = SP()->meta->get('default usergroup', 'sfguests');
    $sfoptions['sfguestsgroup'] = $value[0]['meta_value'];

    $sfmemberopts = SP()->options->get('sfmemberopts');
    $sfoptions['sfsinglemembership'] = $sfmemberopts['sfsinglemembership'];

    return $sfoptions;
}

function spa_members_not_belonging_to_any_usergroup($pageNum = 1, $filter = '', $maxItemsOnPage = 10) {
    global $wpdb;
    $alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $members = array();
    $pagination = array();
    $filter = urldecode($filter);
    if ($pageNum < 1) {
        $pageNum = 1;
    }

    $sql = 'SELECT COUNT(*)
        FROM ' . SPMEMBERS . '
        LEFT JOIN ' . SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
        WHERE ' . SPMEMBERSHIPS . '.usergroup_id IS NULL AND admin=0';


    $countTotal = SP()->DB->select($sql, 'var');

    $offset = $maxItemsOnPage * ($pageNum - 1);

    if ($offset < $countTotal) {

        $sql = 'SELECT COUNT(*)
            FROM ' . SPMEMBERS . '
            LEFT JOIN ' . SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
            WHERE ' . SPMEMBERSHIPS . ".usergroup_id IS NULL AND admin=0";
        if (mb_strlen($filter)) {
            $sql .= " AND display_name REGEXP '.*" . SP()->filters->esc_sql($wpdb->esc_like($filter)) . ".*'";
        }
        $countItems = SP()->DB->select($sql, 'var');

        $sql = 'SELECT ' . SPMEMBERS . '.user_id, display_name
            FROM ' . SPMEMBERS . '
            LEFT JOIN ' . SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
            WHERE ' . SPMEMBERSHIPS . ".usergroup_id IS NULL AND admin=0";
        if (mb_strlen($filter)) {
            $sql .= " AND display_name REGEXP '.*" . SP()->filters->esc_sql($wpdb->esc_like($filter)) . ".*'";
        }
        $sql .= " ORDER BY display_name LIMIT $offset, $maxItemsOnPage";

        $members = SP()->DB->select($sql);
        if ($members) {
            $countPages = ceil($countItems / $maxItemsOnPage);
            $pagination = spa_pagination($countPages, $pageNum);
        }
    }
    ?>
    <table class="widefat sf-table-small sf-table-mobile">
        <thead>
            <tr class="sf-v-a-middle sf-narrow">
                <th class="sf-narrow"><input type="checkbox" data-bind-cb="[name='amid[]']"></th>
                <th>
                    <div class="sf-alphabet">
                        <button class="sf-button<?php echo (!mb_strlen($filter)) ? ' sf-active' : '' ?>"><?php echo esc_html( SP()->primitives->admin_text('All') ) ?></button>
                        <button class="sf-button<?php echo ($filter == '[0-9]') ? ' sf-active' : '' ?>" value="[0-9]">0 - 9</button>
                        <?php foreach ($alphabet as $letter): ?>
                            <button class="sf-button<?php echo (strcasecmp($filter, $letter) == 0) ? ' sf-active' : '' ?>" value="<?php echo esc_attr($letter) ?>"><?php echo esc_html($letter) ?></button>
                        <?php endforeach ?>
                    </div>
                </th>
                <th>
                    <div class="sf-pull-right">
                        <?php echo sprintf('%s %d %s', esc_html( SP()->primitives->admin_text('Total') ), intval($countTotal), esc_html( SP()->primitives->admin_text('Members') ) ); ?>
                    </div>
                </th> 
            </tr>
        </thead>
        <tbody>
            <?php if (!$members): ?> 
                <tr class="sp-v-a-middle">
                    <td colspan="3">
                        <div class="sf-alert-block sf-info"><?php echo esc_html( SP()->primitives->admin_text('List is empty') ) ?></div> 
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $member) : ?>
                    <tr class="sp-v-a-middle">
                        <td class="sf-narrow"><input type="checkbox" name="amid[]" value="<?php echo esc_attr($member->user_id) ?>"></td>
                        <td colspan="2">
                            <div class="sf-avatar"><img src="<?php echo esc_url(get_avatar_url($member->user_id)) ?>" alt="avatar"></div>
                            <span class="sf-user-name"><?php echo esc_html($member->display_name) ?></span>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>
    <?php if ($pagination): ?>
        <div class="sf-pagination">
            <span class="sf-pagination-links">
                <a class="sf-first-page spLoadAjax" href="#"
                   data-target=".sf-not-belonging-to-any-usergroup"
                   data-url="<?php echo esc_url( wp_nonce_url(SPAJAXURL . "usergroups&amp;ug_no=1&amp;page=1&amp;filter=" . urlencode($filter), 'usergroups') ); ?>"
                   ></a>
                   <?php foreach ($pagination as $n => $v): ?>
                    <a class="spLoadAjax<?php echo ($pageNum == $n) ? ' sf-current-page' : '' ?>" href="#"
                       data-target=".sf-not-belonging-to-any-usergroup"
                       data-url="<?php echo esc_url( wp_nonce_url(SPAJAXURL . "usergroups&amp;ug_no=1&amp;page=" . intval($n) . "&amp;filter=" . urlencode($filter), 'usergroups') ); ?>"
                       ><?php echo esc_html($v) ?></a>
                   <?php endforeach ?>
                <a class="sf-last-page spLoadAjax" href="#"
                   data-target=".sf-not-belonging-to-any-usergroup"
                   data-url="<?php echo esc_url( wp_nonce_url(SPAJAXURL . "usergroups&amp;ug_no=1&amp;page=" . intval($countPages) . "&amp;filter=" . urlencode($filter), 'usergroups') ); ?>"
                   ></a>
            </span>
        </div>
    <?php endif;
}
