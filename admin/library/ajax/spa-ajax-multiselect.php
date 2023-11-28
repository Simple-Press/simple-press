<?php

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('Access denied - you cannot directly call this file');
}

spa_admin_ajax_support();

# subseqeent page loads,,,
if (isset($_GET['page_msbox'])) {
    if (!sp_nonce('multiselect'))
        die();
    $msbox = SP()->filters->str($_GET['msbox']);
    $uid = SP()->filters->integer($_GET['uid']);
    $name = esc_attr($_GET['name']);
    $from = esc_html($_GET['from']);
    $num = SP()->filters->integer($_GET['num']);
    $offset = SP()->filters->integer($_GET['offset']);
    $max = SP()->filters->integer($_GET['max']);
    $filter = urldecode($_GET['filter']);
    $ug = $_GET['ug'];

    if (sanitize_text_field($_GET['page_msbox']) == 'filter')
        $max = spa_get_query_max($msbox, $uid, $filter);
    echo spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter, $ug);
    die();
}

# handle include of file but not via ajax
if (isset($action)) {

    if (!isset($to)) {
        $to = '';
    }
    if (!isset($ug)) {
        $ug = '';
    }

    if (!isset($from)) {
        $from = '';
    }

    if ($action == 'addug') {
        spa_prepare_msbox_list('usergroup_add', $usergroup_id);
        echo spa_populate_msbox_list('usergroup_add', $usergroup_id, 'amid', $from, $to, $ug);
    }

    if ($action == 'delug') {
        spa_prepare_msbox_list('usergroup_del', $usergroup_id);
        echo spa_populate_msbox_list('usergroup_del', $usergroup_id, 'dmid', $from, $to, $ug);
    }

    if ($action == 'addru') {
        spa_prepare_msbox_list('rank_add', $rank_id);
        echo spa_populate_msbox_list('rank_add', $rank_id, 'amember_id', $from, $to, $ug);
    }

    if ($action == 'delru') {
        spa_prepare_msbox_list('rank_del', $rank_id);
        echo spa_populate_msbox_list('rank_del', $rank_id, 'dmember_id', $from, $to, $ug);
    }

    if ($action == 'addadmin') {
        spa_prepare_msbox_list('admin_add', '');
        echo spa_populate_msbox_list('admin_add', '', 'member_id', $from, $to, $ug, 20);
    }

    return;
}
die();

# --------------------------------------------------------------------------
# Create the temporary table to be used for all operations
# --------------------------------------------------------------------------

function spa_prepare_msbox_list($msbox, $uid) {
    # drop any existing temp table tp start afresh
    SP()->DB->execute('DROP TABLE IF EXISTS sftempmembers');

    switch ($msbox) {
        case 'usergroup_add':
            $records = SP()->DB->execute('CREATE TABLE sftempmembers AS
				SELECT DISTINCT ' . SPMEMBERS . '.user_id, ' . SPMEMBERS . '.display_name
				FROM ' . SPMEMBERSHIPS . '
				RIGHT JOIN ' . SPMEMBERS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
				WHERE (usergroup_id != ' . $uid . ' AND admin = 0) OR (' . SPMEMBERSHIPS . '.user_id IS NULL AND admin = 0)
				ORDER BY display_name'
            );

            # and then remove those in the current usergroup.
            # this can be necessary when users can be in more than one group and is the quickest method of doing it.
            SP()->DB->execute('DELETE FROM sftempmembers WHERE user_id IN
			(SELECT user_id FROM ' . SPMEMBERSHIPS . ' WHERE usergroup_id = ' . $uid . ')');

            break;

        case 'usergroup_del':
            $records = SP()->DB->execute('CREATE TABLE sftempmembers AS
				SELECT DISTINCT ' . SPMEMBERS . '.user_id, ' . SPMEMBERS . '.display_name
				FROM ' . SPMEMBERSHIPS . '
				JOIN ' . SPMEMBERS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id
				WHERE ' . SPMEMBERSHIPS . '.usergroup_id=' . $uid . '
				ORDER BY display_name'
            );
            break;

        case 'rank_add':
            $specialRank = SP()->meta->get('special_rank', false, $uid);
            $rank = $specialRank[0]['meta_key'];
            $records = SP()->DB->execute('CREATE TABLE sftempmembers AS
				SELECT DISTINCT ' . SPMEMBERS . '.user_id, ' . SPMEMBERS . '.display_name
				FROM ' . SPSPECIALRANKS . '
				RIGHT JOIN ' . SPMEMBERS . ' ON ' . SPMEMBERS . '.user_id = ' . SPSPECIALRANKS . '.user_id
				WHERE (special_rank != "' . $rank . '") OR (' . SPSPECIALRANKS . '.user_id IS NULL)
				ORDER BY display_name'
            );
            break;

        case 'rank_del':
            $specialRank = SP()->meta->get('special_rank', false, $uid);
            $rank = $specialRank[0]['meta_key'];
            $records = SP()->DB->execute('CREATE TABLE sftempmembers AS
				SELECT DISTINCT ' . SPMEMBERS . '.user_id, ' . SPMEMBERS . '.display_name
				FROM ' . SPSPECIALRANKS . '
				RIGHT JOIN ' . SPMEMBERS . ' ON ' . SPMEMBERS . '.user_id = ' . SPSPECIALRANKS . '.user_id
				WHERE (special_rank = "' . $rank . '")
				ORDER BY display_name'
            );
            break;

        case 'admin_add':
            $records = SP()->DB->execute('CREATE TABLE sftempmembers AS
				SELECT ' . SPMEMBERS . '.user_id, display_name
				FROM ' . SPMEMBERS . '
				WHERE admin=0
				ORDER BY display_name'
            );
            break;
    }
}

# --------------------------------------------------------------------------
# Initial list (i.e., psge 1)
# --------------------------------------------------------------------------

function spa_populate_msbox_list($msbox, $uid, $name, $from, $to, $ug, $num = 40) {
    $records = SP()->DB->select('SELECT * FROM sftempmembers LIMIT 0, ' . $num);
    $max = spa_get_query_max($msbox, $uid, '');
    return spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, 0, $max, '', $ug);
}

# --------------------------------------------------------------------------
# Subsequent page loads
# --------------------------------------------------------------------------

function spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter, $ug) {
    global $wpdb;

    $out = '';
    $like = '';
    if ($filter != '')
        $like = " WHERE display_name LIKE '%" . SP()->filters->esc_sql($wpdb->esc_like($filter)) . "%'";
    $records = SP()->DB->select('SELECT * FROM sftempmembers' . $like . ' LIMIT ' . $offset . ', ' . $num);

    $out .= spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter, $ug);
    return $out;
}

# --------------------------------------------------------------------------
# get the record counts
# --------------------------------------------------------------------------

function spa_get_query_max($msbox, $uid, $filter) {
    global $wpdb;

    $like = '';

    if ($filter != '')
        $like = " WHERE display_name LIKE '%" . SP()->filters->esc_sql($wpdb->esc_like($filter)) . "%'";
    $max = SP()->DB->select('SELECT COUNT(*) AS user_count FROM sftempmembers' . $like, 'var');

    if (!$max)
        $max = 0;
    return $max;
}

function spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter, $ug) {
    $empty = true;


    $out = '<div id="mslist-' . $name . $uid . '">';
        $out .= "<div style='display: flex;'>";
        $gif = SPCOMMONIMAGES . "working.gif";
        $site = wp_nonce_url(
            SPAJAXURL . "multiselect&amp;page_msbox=filter&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode(
                $from
            ) . "&amp;num=$num&amp;offset=0&amp;max=$max&amp;ug=$ug",
            'multiselect'
        );
        $out .= '       <p class="search-box-v2 sf-filter-auto">';
        $out .= '           <input type="search" placeholder="Search" id="list-filter' . $name . $uid . '" name="list-filter' . $name . $uid . '" value="' . $filter . '" class="sfacontrol" size="10">';
        $out .= '           <input type="button" id="filter' . $uid . '" class="spFilterList sf-hidden-important" value="' . SP(
            )->primitives->admin_text(
                'Filter'
            ) . '" data-url="' . $site . '" data-uid="' . $name . $uid . '" data-image="' . $gif . '">';
        $out .= '       </p>';
        // If user group is set
        if ($ug) {
            $out .= '<div style="flex-basis: 80%; text-align: right">';
                $out .= '<select name="usergroup_id" style="vertical-align: unset">';
                    $out .= '<option value="">' . SP()->primitives->admin_text('Select User Group') . '</option>';
                    foreach (spa_get_usergroups_all(null) as $usergroup) {
                        $out .= '<option value="' . $usergroup->usergroup_id . '"' . (!empty($_POST['usergroup_id']) && $usergroup->usergroup_id == $_POST['usergroup_id'] ? ' checked="checked"' : '') . '>' . SP()->displayFilters->title($usergroup->usergroup_name) . '</option>';
                    }
                $out .= '</select>';
                $out .= '<button class="sf-button-primary">' . SP()->primitives->admin_text('Move to group') . '</button>';
            $out .= '</div>';
        }
        $out .= "</div>";

        // Container for users or message that there are no users
        $out .= '<ul class="list-grid">';
            if ($records) {
                foreach ($records as $record) {
                    $empty = false;
                    $out .= "<li class='sf-grid-item'>";
                        $out .= "<input type='checkbox' name='{$name}[]' value='{$record->user_id}'>";
                        $out .= "<span class='sf-user-name' title='User ID: " . $record->user_id."'>" . SP()->displayFilters->name($record->display_name) . "</span>";
                    $out .= "</li>";
                }
            }
            if ($empty) {
                $out .= '<div class="sf-alert-block sf-info">' . SP()->primitives->admin_text('List is empty') . '</div>';
            }
        $out .= '</ul>';

        $out .= '<span id="filter-working"></span>';
        $out .= spa_msbox_pagination($msbox, $uid, $name, $from, $num, $offset, $max, $filter, $ug);
    $out .= '</div>';
    return $out;
}

function spa_msbox_pagination($msbox, $uid, $name, $from, $num, $offset, $max, $filter, $ug) {
    $out = '';
    $countPages = ceil($max / $num);
    $currentPageNum = ceil($offset / $num);
    $pagination = spa_pagination($countPages, $currentPageNum);


    if ($pagination) {
        $out .= '<div class="sf-pagination sf-mb-15">';
        $out .= '<div class="sf-pagination-links">';

        // First Page Link
        if ($currentPageNum > 0) {
            $out .= create_pagination_link(0, 'First', $msbox, $uid, $name, $from, $num, $max, $filter, $ug);
        }

        // Previous Page Link
        if ($currentPageNum >= 1) {
            $out .= create_pagination_link(($currentPageNum - 1) * $num, 'Previous', $msbox, $uid, $name, $from, $num, $max, $filter, $ug);
        }

        // Page Links
        foreach ($pagination as $n => $v) {
            $out .= create_pagination_link(($n - 1) * $num, $v, $msbox, $uid, $name, $from, $num, $max, $filter, $ug, $currentPageNum == $n - 1 ? ' sf-current-page' : '');
        }

        // Next Page Link
        if (($currentPageNum + 1)*$num < $max) {
            $out .= create_pagination_link(($currentPageNum + 1) * $num, 'Next', $msbox, $uid, $name, $from, $num, $max, $filter, $ug);
        }

        // Last Page Link
        if ($currentPageNum < $countPages - 1) {
            $out .= create_pagination_link(($countPages - 1) * $num, 'Last', $msbox, $uid, $name, $from, $num, $max, $filter, $ug);
        }

        $out .= '</div>';
        $out .= '</div>';
    }

    return $out;
}

function create_pagination_link($offset, $text, $msbox, $uid, $name, $from, $num, $max, $filter, $ug, $class = '') {
    return '<a class="sf-first-page spUpdateList' . $class . '" href="javascript:void(0)"'
        . ' data-uid="' . $name . $uid . '"'
        . ' data-url="' . wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=$offset&amp;max=$max&amp;filter=$filter&amp;ug=$ug", 'multiselect') . '"'
        . '>' . $text . '</a>';
}

