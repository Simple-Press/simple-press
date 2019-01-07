<?php
/*
Simple:Press
Admin Users Members Form
$LastChangedDate: 2017-11-11 15:57:00 -0600 (Sat, 11 Nov 2017) $
$Rev: 15578 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_users_members_form() {

	add_screen_option('layout_columns', array('default' => 2));

	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Users').' - '.spa_text('Member Information'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Member Information'), 'true', 'users-info');
                if (!class_exists('WP_List_Table')) require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');

                class SP_Members_Table extends WP_List_Table {
                    function __construct() {
                        parent::__construct( array(
                            'singular'=> spa_text('member'),
                            'plural' => spa_text('members'),
                            'ajax'   => false
                        ));
                    }

                    function get_columns(){
                        $columns = array(
                            'cb'                => '<input type="checkbox" />',
                            'user_id'           => spa_text('ID'),
                            'user_login'        => spa_text('User Login'),
                            'display_name'      => spa_text('Display Name'),
                            'user_registered'   => spa_text('Registered'),
                            'lastvisit'         => spa_text('Last Visit'),
                            'posts'             => spa_text('Posts'),
                            'memberships'       => spa_text('Memberships'),
                            'rank'              => spa_text('Forum Rank'),
                        );
                        return $columns;
                    }

                    public function get_sortable_columns() {
                        $sortable_columns = array(
                            'user_id'           => array('user_id', true),
                            'user_login'        => array('user_login', false),
                            'display_name'      => array('display_name', false),
                            'user_registered'   => array('user_registered', false),
                            'lastvisit'         => array('lastvisit', false),
                            'posts'             => array('posts', false),
                        );
                        return $sortable_columns;
                    }

                    function get_bulk_actions() {
                        $actions = array(
                            'delete'    => spa_text('Delete')
                        );
                        return $actions;
                    }

                    function no_items() {
                        spa_etext('No members found');
                    }

                	function get_table_classes() {
                		return array('widefat', 'fixed', 'striped', $this->_args['plural'], 'spMobileTable1280');
                	}

                    function display_rows() {
                        $records = $this->items;
                        if (!empty($records)) {
                    		list($columns, $hidden, $sortable, $primary) = $this->get_column_info();
                            foreach ($records as $rec) {
                                echo '<tr class="spMobileTableData">';

                                foreach ($columns as $column_name => $column_display_name) {
                        			$classes = "$column_name column-$column_name";
                        			$data = 'data-label="'.wp_strip_all_tags($column_display_name).'"';
                        			$attributes = "class='$classes' $data";

                                    switch ($column_name) {
                                        case 'cb':
                            				echo '<th scope="row" class="check-column">'.sprintf('<input style="left:0; position:relative" type="checkbox" name="users[]" value="%s" />', $rec['user_id']).'</th>';
                                            break;

                                        default;
                                            echo "<td $attributes>";
                                            switch ($column_name) {
                                                case 'user_id':
                                                    echo $rec['user_id'];

                               						$nonce = wp_create_nonce('bulk-users');
                            						$site = wp_nonce_url(SPAJAXURL.'profile&amp;targetaction=popup&amp;user='.$rec['user_id'], 'profile');
                            						$title = spa_text('Member Profile');
                            						$user_action = (is_multisite()) ? 'remove' : 'delete';
													$actions = array(
                                                        'edit'      => '<a href="'.admin_url('user-edit.php?user_id='.$rec['user_id']).'&amp;wp_http_referer=admin.php?page=simple-press/admin/panel-users/spa-users.php">'.spa_text('Edit').'</a>',
                                                        'delete'   => '<a href="'.admin_url('users.php?action='.$user_action.'&amp;user='.$rec['user_id']."&amp;_wpnonce=$nonce&amp;wp_http_referer=admin.php?page=simple-press/admin/panel-users/spa-users.php").'">'.spa_text('Delete').'</a>',
                                                        'profile'    => '<a id="memberprofile'.$rec['user_id'].'" class="spOpenDialog" data-site="'.$site.'" data-label="'.$title.'" data-width="750" data-height="0" data-align="center">'.spa_text('Profile').'</a>',
                                                    );

                                                    echo $this->row_actions($actions);
                                                    break;

                                                case 'display_name':
                                                    echo sp_filter_name_display($rec['display_name']);
                                                    break;

                                                case 'user_registered':
                                                case 'lastvisit':
                                                    echo sp_date('d', $rec[$column_name]).'<br />'.sp_date('t', $rec[$column_name]);
                                                    break;

                                                case 'posts':
                                                    echo max($rec['posts'], 0);
                                                    break;

                                                default:
                                                    echo $rec[$column_name];
                                                }
                                            echo '</td>';
                                    }
                                }

                                echo '</tr>';
                            }
                        }
                    }

                	function get_views() {
                		$usergroup = isset($_REQUEST['usergroup']) ? (int) $_REQUEST['usergroup'] : '';
                		$usergroups = spa_get_usergroups_all();
                        $members = spdb_select('var', 'SELECT count(*) as count FROM '.SFMEMBERS);

                		$class = empty($usergroup) ? ' class="current"' : '';
                		$ug_links = array();
                		$ug_links['all'] = "<a href='".SFADMINUSER."'$class>".spa_text('All')." <span class='count'>($members)</span></a>";
                		foreach ($usergroups as $ug) {
                			$class = ($ug->usergroup_id == $usergroup) ? ' class="current"' : '';
                            $count = spdb_count(SFMEMBERSHIPS, "usergroup_id = $ug->usergroup_id");
                			$name = $ug->usergroup_name.' <span class="count">('.$count.')</span>';
                			$ug_links[$ug->usergroup_name] = "<a style='margin-left:-12px' href='".esc_url(add_query_arg('usergroup', $ug->usergroup_id, SFADMINUSER))."'$class>$name</a>";
                		}

                    	$nomembership = spdb_select('var', '
                    		SELECT count(*) as count
                            FROM '.SFMEMBERS.'
                    		WHERE user_id NOT IN (SELECT user_id FROM '.SFMEMBERSHIPS.') AND admin=0'
                    	);
               			$class = ($usergroup === -1) ? ' class="current"' : '';
                		$ug_links['No Membership'] = "<a style='margin-left:-12px' href='".esc_url(add_query_arg('usergroup', -1, SFADMINUSER))."'$class>".spa_text('No Membership')." <span class='count'>($nomembership)</span></a>";

                		return $ug_links;
                	}

                    function prepare_items() {
                        # init the class
                        $columns = $this->get_columns();
                        $hidden = array();
                        $sortable = $this->get_sortable_columns();
                        $this->_column_headers = array($columns, $hidden, $sortable);

                        # start the query
                       	$spdb = new spdbComplex;
                        $spdb->table        = SFMEMBERS;
                        $spdb->found_rows   = true;
                        $spdb->fields       = SFMEMBERS.'.user_id, '.SFMEMBERS.'.display_name, lastvisit, posts, admin, moderator, user_login, user_registered';
        				$spdb->join         = array(SFUSERS.' ON '.SFMEMBERS.'.user_id = '.SFUSERS.'.ID');

                        # handle specific usergroup
                		$usergroup = isset($_REQUEST['usergroup']) ? (int) $_REQUEST['usergroup'] : '';
                        if ($usergroup) {
                            if ($usergroup == -1) {
                				$spdb->left_join = array(SFMEMBERSHIPS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id');
                                $spdb->where = SFMEMBERSHIPS.".user_id IS NULL AND admin = 0";
                            } else {
                				$spdb->join[] = SFMEMBERSHIPS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id';
                                $spdb->where = SFMEMBERSHIPS.".usergroup_id = $usergroup";
                            }

                            # need to fool wp on request uri since our admin urls are wrong
                            $_SERVER['REQUEST_URI'].= '&usergroup='.$usergroup;
                        }

                        # handle sort ordering
                        $orderby = (!empty($_GET['orderby'])) ? sp_esc_sql($_GET['orderby']) : 'ASC';
                        $order = (!empty($_GET['order'])) ? sp_esc_sql($_GET['order']) : '';
                        $spdb->orderby = (!empty($orderby) && !empty($order)) ? "$orderby $order" : '';

                        # pagination
                        $per_page = 50;
                        $current_page = $this->get_pagenum();
                        $offset = ($current_page - 1) * $per_page;
            			$spdb->limits = "$offset, $per_page";

                        # searching
                		$search_term = isset($_GET['s']) ? sp_filter_title_save(trim($_GET['s'])) : '';
                        if ($search_term) {
                			$searches = array();
                			foreach (array('user_login', SFMEMBERS.'.display_name') as $col) {
                				$searches[] = $col." LIKE '%$search_term%'";
                            }
                			$where = implode(' OR ', $searches);
                            $spdb->where = (!empty($spdb->where)) ? " AND ($where)" : $where;

                            # if no ordering, list matches that start with the search term first
                            global $wpdb;
                            if (empty($spdb->orderby)) $spdb->orderby = 'IF ('.SFMEMBERS.".display_name LIKE '".sp_esc_sql($wpdb->esc_like($search_term))."%', 0, IF (".SFMEMBERS.".display_name LIKE '%".sp_esc_sql($wpdb->esc_like($search_term))."%', 1, 2))";

                            # need to fool wp on request uri since our admin urls are wrong
                            $_SERVER['REQUEST_URI'].= '&s='.$search_term;
                        }

                        # do our members query
                        $spdb = apply_filters('sph_admin_members_list_query', $spdb);

                        $records = $spdb->select();

                        # set up page links
                        $total_items = spdb_select('var', 'SELECT FOUND_ROWS()');
                        $this->set_pagination_args(array(
                            'total_items' => $total_items,
                            'per_page'    => $per_page
                        ));

                        # fill the rest of the results with needed data
                        $members = array();
                        if ($records) {
                            foreach ($records as $idx => $data) {
                                # set up some data
                            	if ($data->admin) {
                            		$user_memberships = 'Admin';
                            		$status = 'Admin';
                            		$start = 0;
                            	} else if ($data->moderator) {
                            		$status = 'Moderator';
                            		$start = 1;
                            	} else {
                            		$status = 'User';
                            		$start = 1;
                            	}

                                # get memberships for this member
                            	$memberships = spdb_table(SFMEMBERSHIPS, "user_id=$data->user_id", '', '', '', ARRAY_A);
                            	if ($memberships) {
                            		foreach ($memberships as $membership) {
                            			$name = spdb_table(SFUSERGROUPS, 'usergroup_id='.$membership['usergroup_id'], 'usergroup_name');
                            			if ($start) {
                            				$user_memberships = $name;
                            				$start = 0;
                            			} else {
                            				$user_memberships.= ', '.$name;
                            			}
                            		}
                            	} else if ($start) {
                            		$user_memberships = spa_text('No Memberships');
                            	}

                                # build the forum rank
                            	$rank = sp_get_user_forum_rank($status, $data->user_id, $data->posts);

                                # now fill in the members array
            					$members[$idx]['user_id']          = $data->user_id;
            					$members[$idx]['user_login']       = $data->user_login;
            					$members[$idx]['display_name']     = $data->display_name;
            					$members[$idx]['user_registered']  = $data->user_registered;
            					$members[$idx]['lastvisit']        = $data->lastvisit;
            					$members[$idx]['posts']            = $data->posts;
            					$members[$idx]['memberships']      = $user_memberships;
            					$members[$idx]['rank']             = $rank[0]['name'];
                            }
                        }

                        # fill class items
                        $this->items = $members;
                    }
                }

                # build the class
                $membersTable = new SP_Members_Table();

                # any actions to process?
                switch ($membersTable->current_action()) {
                    case 'delete':
                       	$userids = array_map('intval', (array) $_REQUEST['users']);
                        $url = self_admin_url('users.php?action=delete&users[]='.implode('&users[]=', $userids).'&wp_http_referer=admin.php?page=simple-press/admin/panel-users/spa-users.php');
                        $url = str_replace('&amp;', '&', wp_nonce_url($url, 'bulk-users'));
                        sp_redirect($url);
                        exit();
                }

                # going to display, lets prep items
                $membersTable->prepare_items();

                # display view links
                $membersTable->views();
?>
                <form id="members-filter" method="get" action="<?php echo SFADMINUSER; ?>">
                    <input type="hidden" name="page" value="<?php echo 'simple-press/admin/panel-users/spa-users.php'; ?>" />
<?php
                    # dispaly the search box
                    $membersTable->search_box(spa_text('Search Members'), 'search_id');

                    # display the members list table
                    $membersTable->display();
?>
                </form>
<?php
			spa_paint_close_fieldset();
           	echo '<div class="sfform-panel-spacer"></div>';
		spa_paint_close_panel();

		do_action('sph_users_members_panel');
		spa_paint_close_container();
	spa_paint_close_tab();
}
?>