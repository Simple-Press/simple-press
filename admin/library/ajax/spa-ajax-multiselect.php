<?php

/*
  Simple:Press
  Common Ajax
  $LastChangedDate: 2018-11-02 13:02:17 -0500 (Fri, 02 Nov 2018) $
  $Rev: 15795 $
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
    die('Access denied - you cannot directly call this file');

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

    if (sanitize_text_field($_GET['page_msbox']) == 'filter')
        $max = spa_get_query_max($msbox, $uid, $filter);
    echo spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter);
    die();
}

# handle include of file but not via ajax
if (isset($action)) {

    if ($action == 'addug') {
        spa_prepare_msbox_list('usergroup_add', $usergroup_id);
        echo spa_populate_msbox_list('usergroup_add', $usergroup_id, 'amid', $from, $to, 100);
    }

    if ($action == 'delug') {
        spa_prepare_msbox_list('usergroup_del', $usergroup_id);
        echo spa_populate_msbox_list('usergroup_del', $usergroup_id, 'dmid', $from, $to, 100);
    }

    if ($action == 'addru') {
        spa_prepare_msbox_list('rank_add', $rank_id);
        echo spa_populate_msbox_list('rank_add', $rank_id, 'amember_id', $from, $to, 100);
    }

    if ($action == 'delru') {
        spa_prepare_msbox_list('rank_del', $rank_id);
        echo spa_populate_msbox_list('rank_del', $rank_id, 'dmember_id', $from, $to, 100);
    }

    if ($action == 'addadmin') {
        spa_prepare_msbox_list('admin_add', '');
        echo spa_populate_msbox_list('admin_add', '', 'member_id', $from, $to, 100);
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

function spa_populate_msbox_list($msbox, $uid, $name, $from, $to, $num) {
    $out = '';

    $records = SP()->DB->select('SELECT * FROM sftempmembers LIMIT 0, ' . $num);
    $max = spa_get_query_max($msbox, $uid, '');

    $out .= '<table class="sf-msbox-list">';
    $out .= '<tr class="sf-v-a-top">';
    $out .= '<td width="50%">';
    $out .= '<div id="mslist-' . $name . $uid . '">';
    $out .= spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, 0, $max, '');
    $out .= '</div>';
    $out .= '</td>';

    $out .= '<td width="50%">';
    $out .= '<div><strong>' . $to . ' <span id="selcount">0</span></strong></div>';
    $out .= '<select class="msAddControl" multiple="multiple" size="10" id="' . $name . $uid . '" name="' . $name . '[]" >';
    $out .= '<option disabled="disabled" value="-1">' . SP()->primitives->admin_text('List is empty') . '</option>';
    $out .= '</select>';
    $out .= '<p>' . SP()->primitives->admin_text('Max Selection - 400 Users') . '</p>';
    $out .= '<div class="sf-controls">';
    $out .= '<input type="button" id="add' . $uid . '" class="sf-button-secondary spStackBtnLong spTransferList sf-remove-from-list" value="' . SP()->primitives->admin_text('Remove From Selected List') . '" data-from="' . $name . $uid . '" data-to="temp-' . $name . $uid . '" data-msg="' . SP()->primitives->admin_text('List is Empty') . '" data-exceed="' . SP()->primitives->admin_text('Maximum of 400 Users would be exceeded - please reduce the selections') . '" data-recip="' . $name . $uid . '" />';
    $out .= '</div>';
    $out .= '</td>';
    $out .= '</tr>';
    $out .= '</table>';

    return $out;
}

# --------------------------------------------------------------------------
# Subsequent page loads
# --------------------------------------------------------------------------

function spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter) {
    global $wpdb;

    $out = '';
    $like = '';
    if ($filter != '')
        $like = " WHERE display_name LIKE '%" . SP()->filters->esc_sql($wpdb->esc_like($filter)) . "%'";
    $records = SP()->DB->select('SELECT * FROM sftempmembers' . $like . ' LIMIT ' . $offset . ', ' . $num);

    $out .= spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter);
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

function spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter) {
    $out = '';
    $empty = true;

    $out .= '<div><strong>' . $from . '</strong><br /></div>';
    $out .= '<select class="msAddControl" multiple="multiple" size="10" id="temp-' . $name . $uid . '" name="temp-' . $name . $uid . '[]">';
    if ($records) {
        foreach ($records as $record) {
            $empty = false;
            $out .= '<option value="' . $record->user_id . '">' . SP()->displayFilters->name($record->display_name) . '</option>' . "\n";
        }
    }
    if ($empty)
        $out .= '<option disabled="disabled" value="-1">' . SP()->primitives->admin_text('List is empty') . '</option>';
    $out .= '</select>';
    $out .= '<p>' . SP()->primitives->admin_text('Paging Controls') . '</p>';
    $out .= '<span id="filter-working"></span>';
    $out .= '<div class="sf-pagination">';

    $last = floor($max / $num) * $num;
    if ($last >= $max)
        $last = $last - $num;

    $disabled = '';
    if ($offset == 0)
        $disabled = ' disabled="disabled"';

    $site = wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=0&amp;max=$max&amp;filter=$filter", 'multiselect');
    $out .= '<input type="button"' . $disabled . ' id="firstpage' . $uid . '" class="sf-button-secondary spUpdateList" value="<<" data-url="' . $site . '" data-uid="' . $name . $uid . '" />';

    $site = wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=" . ($offset - $num) . "&amp;max=$max&amp;filter=$filter", 'multiselect');
    $out .= '<input type="button"' . $disabled . ' id="prevpage' . $uid . '" class="sf-button-secondary spUpdateList" value="<" data-url="' . $site . '" data-uid="' . $name . $uid . '" />';

    $disabled = '';
    if (($offset + $num) >= $max)
        $disabled = ' disabled="disabled"';

    $site = wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=" . ($offset + $num) . "&amp;max=$max&amp;filter=$filter", 'multiselect');
    $out .= '<input type="button"' . $disabled . ' id="nextpage' . $uid . '" class="sf-button-secondary spUpdateList" value=">" data-url="' . $site . '" data-uid="' . $name . $uid . '" />';

    $site = wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=$last&amp;max=$max&amp;filter=$filter", 'multiselect');
    $out .= '<input type="button"' . $disabled . ' id="lastpage' . $uid . '" class="sf-button-secondary spUpdateList" value=">>" data-url="' . $site . '" data-uid="' . $name . $uid . '" />';
    $out .= '</div>';
    $out .= '<div class="sf-controls">';
    $out .= '<input type="button" id="add' . $uid . '" class="sf-button-secondary spStackBtnLong spTransferList sf-move-to-list" value="' . SP()->primitives->admin_text('Move to Selected List') . '" data-from="temp-' . $name . $uid . '" data-to="' . $name . $uid . '" data-msg="' . SP()->primitives->admin_text('List is Empty') . '" data-exceed="' . SP()->primitives->admin_text('Maximum of 400 Users would be exceeded - please reduce the selections') . '" data-recip="' . $name . $uid . '" />';
    $out .= '<input type=text id="list-filter' . $name . $uid . '" name="list-filter' . $name . $uid . '" value="' . $filter . '" class="sfacontrol" size="10" />';
    $gif = SPCOMMONIMAGES . "working.gif";
    $site = wp_nonce_url(SPAJAXURL . "multiselect&amp;page_msbox=filter&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=" . urlencode($from) . "&amp;num=$num&amp;offset=0&amp;max=$max", 'multiselect');
    $out .= '<input type="button" id="filter' . $uid . '" class="sf-button-secondary spFilterList" value="' . SP()->primitives->admin_text('Filter') . '" data-url="' . $site . '" data-uid="' . $name . $uid . '" data-image="' . $gif . '" />';
    $out .= '</div>';
    return $out;
}
