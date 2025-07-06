<?php
/*
Simple:Press
Admin Users Members Form
*/

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'Access denied - you cannot directly call this file' );
}

/**
 * Outputs a simple paginated table of members from SPMEMBERS table,
 * with a navigation menu above - filter by user group.
 * With a "Delete" link for each user (protected by nonce).
 *
 * @return string HTML output.
 */
function sp_list_members() {
    global $wpdb;

    //Pagination
    $per_page = 25;
    $paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
    $offset   = ( $paged - 1 ) * $per_page;

    //Get current usergroup from URL.
    $usergroup_filter = isset( $_GET['usergroup'] ) ? intval( $_GET['usergroup'] ) : 0;

    $base_url = admin_url( 'admin.php?page=simplepress/admin/panel-users/spa-users.php' );
    $current_url = remove_query_arg( array( 'usergroup', 'paged' ), $base_url );

    //Build usergroup links
    $ug_links = array();
    //"All Members" link.
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    $all_total = $wpdb->get_var( "SELECT COUNT(*) FROM " . SPMEMBERS );
    $class = empty( $usergroup_filter ) ? ' class="current"' : '';
    $ug_links['all'] = "<a href='" . esc_url( add_query_arg( 'usergroup', '', $current_url ) ) . "'{$class}>All Members ({$all_total})</a>";

    //Get all user groups (SP function).
    $usergroups = spa_get_usergroups_all();
    if ( ! empty( $usergroups ) ) {
        foreach ( $usergroups as $ug ) {
            $class = ( $ug->usergroup_id == $usergroup_filter ) ? ' class="current"' : '';
            //Count members in this group.
            $count = SP()->DB->count( SPMEMBERSHIPS, "usergroup_id = " . intval( $ug->usergroup_id ) );
            $ug_links[ $ug->usergroup_name ] = "<a href='" . esc_url( add_query_arg( 'usergroup', $ug->usergroup_id, $current_url ) ) . "'{$class}>{$ug->usergroup_name} ({$count})</a>";
        }
    }

    //"No Membership" link.
    $no_membership = $wpdb->get_var(
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        "SELECT COUNT(*) FROM " . SPMEMBERS . " WHERE user_id NOT IN (SELECT user_id FROM " . SPMEMBERSHIPS . ") 
         AND admin = 0"
    );
    $class = ( $usergroup_filter === -1 ) ? ' class="current"' : '';
    $ug_links['No Membership'] = "<a href='" . esc_url( add_query_arg( 'usergroup', -1, $current_url ) ) . "'{$class}>No Membership ({$no_membership})</a>";

    //Build navigation HTML.
    $nav_output = '<div class="sp-usergroup-nav" style="margin-bottom:20px;">' . implode(' &nbsp; ', $ug_links) . '</div>';

    //Prepare member query based on filter.
    if ( $usergroup_filter ) {
        if ( $usergroup_filter === -1 ) {
            //Only members with no membership.
            $total = $wpdb->get_var(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                "SELECT COUNT(*) FROM " . SPMEMBERS . " WHERE user_id NOT IN (SELECT user_id FROM " . SPMEMBERSHIPS . ") 
                 AND admin = 0"
            );
            $members = SP()->DB->select( $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                "SELECT * FROM " . SPMEMBERS . " WHERE user_id NOT IN (SELECT user_id FROM " . SPMEMBERSHIPS . ") 
                    AND admin = 0 
                    LIMIT %d, %d",
                $offset, $per_page ) );
        } else {
            //Filter by specific user group.
            $total = $wpdb->get_var( $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                "SELECT COUNT(DISTINCT m.user_id) FROM " . SPMEMBERS . " m INNER JOIN " . SPMEMBERSHIPS . " ms ON m.user_id = ms.user_id
                 WHERE ms.usergroup_id = %d",
                $usergroup_filter
            ) );
            $members = SP()->DB->select( $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                "SELECT DISTINCT m.* FROM " . SPMEMBERS . " m INNER JOIN " . SPMEMBERSHIPS . " ms ON m.user_id = ms.user_id
                      WHERE ms.usergroup_id = %d
                      LIMIT %d, %d",
                $usergroup_filter, $offset, $per_page 
            ) );
        }
    } else {
        //No filter: list all members.
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $total = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM " . SPMEMBERS ));
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $members = SP()->DB->select( $wpdb->prepare( "SELECT * FROM " . SPMEMBERS . " LIMIT %d, %d", $offset, $per_page ) );
    }

    $total_pages = ceil( $total / $per_page );

    ob_start();
    echo wp_kses(
        $nav_output,
        [
            'div' => [
                'class' => true,
                'style' => true
            ],
            'a' => [
                'class' => true,
                'href' => true
            ]
        ]
    );
    ?>
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'User ID', 'simplepress' ); ?></th>
                <th><?php esc_html_e( 'Display Name', 'simplepress' ); ?></th>
                <th><?php esc_html_e( 'Memberships', 'simplepress' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'simplepress' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $members ) ) : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e( 'No members found', 'simplepress' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $members as $member ) : ?>
                    <tr>
                        <td><?php echo esc_html( $member->user_id ); ?></td>
                        <td><?php echo esc_html( $member->display_name ); ?></td>
                        <td>
                            <?php
                            if ( $usergroup_filter ) {
                                if ( $usergroup_filter === -1 ) {
                                    echo esc_html__( 'No Membership', 'simplepress' );
                                } else {
                                    //Show only the filtered group name.
                                    $group_name = $wpdb->get_var( $wpdb->prepare(
                                        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                                        "SELECT usergroup_name FROM " . SPUSERGROUPS . " 
                                         WHERE usergroup_id = %d",
                                        $usergroup_filter
                                    ) );
                                    echo esc_html( $group_name );
                                }
                            } else {
                                //List all memberships for member.
                                $groups = $wpdb->get_col( $wpdb->prepare(
                                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                                    "SELECT ug.usergroup_name FROM " . SPMEMBERSHIPS . " ms LEFT JOIN " . SPUSERGROUPS . " ug ON ms.usergroup_id = ug.usergroup_id WHERE ms.user_id = %d",
                                    $member->user_id
                                ) );
                                if ( empty( $groups ) ) {
                                    $groups = array( __( 'No Memberships', 'simplepress' ) );
                                }
                                echo esc_html( implode( ', ', $groups ) );
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            //Delete link with nonce - calls users.php?action=delete&user=xxx (WP built-in user deletion)
                            //'bulk-users' action is recognized by WP core. Change for custom check.
							$delete_base = admin_url( 'users.php' );
                            $delete_args = array(
                                'action'         => 'delete',
                                'user'           => $member->user_id,
                                'wp_http_referer'=> urlencode( 'admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php' ),
                            );
                            //Build the link
                            $delete_url = add_query_arg( $delete_args, $delete_base );
                            //Wrap with nonce for 'bulk-users'
                            $delete_url = wp_nonce_url( $delete_url, 'bulk-users' );
                            ?>
                            <a href="<?php echo esc_url( $delete_url ); ?>" class="sf-icon sf-blue sf-delete">
                                <?php esc_html_e( 'Delete', 'simplepress' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php

    //Build pagination base URL from scratch.
    $base_url = admin_url( 'admin.php' );
    $query_args = array(
        'page' => SP_FOLDER_NAME . '/admin/panel-users/spa-users.php',
    );
    if ( ! empty( $usergroup_filter ) ) {
        $query_args['usergroup'] = $usergroup_filter;
    }
    $base_url = add_query_arg( $query_args, $base_url );
    //Remove existing 'paged' value.
    $base_url = remove_query_arg( 'paged', $base_url );
    $pagination_base = add_query_arg( 'paged', '%#%', $base_url );

    $pagination = paginate_links( array(
        'base'      => $pagination_base,
        'format'    => '',
        'current'   => $paged,
        'total'     => $total_pages,
        'prev_text' => __( 'Previous', 'simplepress' ),
        'next_text' => __( 'Next', 'simplepress' ),
        'type'      => 'array'
    ) );
    
    if ( ! empty( $pagination ) ) {
        $processed_pagination = array_map( function( $link ) use ( $paged, $base_url ) {
            if ( strpos( $link, 'current' ) !== false ) {
                return '<a class="page-numbers current sf-current-page" href="' . esc_url( add_query_arg( 'paged', $paged, $base_url ) ) . '">' . esc_html($paged) . '</a>';
            }
            return $link;
        }, $pagination );
    
        echo '<div class="sf-pagination sf-mt-15"><span class="sf-pagination-links">' . esc_html(implode( '', $processed_pagination )) . '</span></div>';
    }

    return ob_get_clean();
}

/**
 * outputting the Simple:Press Admin Users Members Form.
 */
function spa_users_members_form() {
    require_once SP_PLUGIN_DIR . '/forum/content/sp-common-view-functions.php';
    spa_paint_options_init();
    spa_paint_open_tab( SP()->primitives->admin_text( 'Member Information' ), true );
    spa_paint_open_panel();
    spa_paint_open_fieldset( SP()->primitives->admin_text( 'Member Information' ), false, '', false );

    ?>
    <form id="members-filter" method="get" action="<?php echo esc_attr(SPADMINUSER); ?>">
        <?php echo wp_kses(
            sp_list_members(),
            SP_CORE_ALLOWED_TAGS
        ); ?>
    </form>

    <script>
        if (typeof spj !== 'undefined' && typeof spj.after_users_listing === 'function') {
            spj.after_users_listing();
        }
    </script>
    <?php

    spa_paint_close_fieldset();
    spa_paint_close_panel();
    do_action( 'sph_users_members_panel' );
    spa_paint_close_container();
    spa_paint_close_tab();
}
