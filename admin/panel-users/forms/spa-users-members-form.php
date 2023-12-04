<?php
/*
Simple:Press
Admin Users Members Form
$LastChangedDate: 2018-11-13 20:41:56 -0600 (Tue, 13 Nov 2018) $
$Rev: 15817 $
*/

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'Access denied - you cannot directly call this file' );
}

function spa_users_members_form() {
	global $adminhelpfile;
	require_once SP_PLUGIN_DIR . '/forum/content/sp-common-view-functions.php';
	add_screen_option( 'layout_columns', array( 'default' => 2 ) );

	spa_paint_options_init();

	spa_paint_open_tab(SP()->primitives->admin_text( 'Member Information' ), true );
	spa_paint_open_panel();
	spa_paint_open_fieldset( SP()->primitives->admin_text( 'Member Information' ), false, '', false );
	if ( ! class_exists( 'SP_List_Table' ) ) {
		require_once SP_PLUGIN_DIR . '/admin/library/sp-list-table.php';
	}

	class SP_Members_Table extends SP_List_Table {
		public $per_page = 25;
		public $current_page = 1;
		public $count_pages = 0;

		function __construct() {
			parent::__construct( array(
				'singular' => SP()->primitives->admin_text( 'member' ),
				'plural'   => SP()->primitives->admin_text( 'members' ),
				'ajax'     => false
			) );
		}

		function get_columns() {
			$columns = array(
				'user_id'         => SP()->primitives->admin_text( 'ID' ),
				'display_name'    => SP()->primitives->admin_text( 'User' ),
				'posts'           => SP()->primitives->admin_text( 'Posts' ),
				'memberships'     => SP()->primitives->admin_text( 'Memberships' ),
				'rank'            => SP()->primitives->admin_text( 'Forum Rank' ),
				'user_registered' => SP()->primitives->admin_text( 'Registered' ),
				'lastvisit'       => SP()->primitives->admin_text( 'Last Visit' ),
			);

			return $columns;
		}

		public function get_sortable_columns() {
			$sortable_columns = array(
				'user_id'         => array( 'user_id', true ),
				'user_login'      => array( 'user_login', false ),
				'display_name'    => array( 'display_name', false ),
				'user_registered' => array( 'user_registered', false ),
				'lastvisit'       => array( 'lastvisit', false ),
				'posts'           => array( 'posts', false ),
			);

			return $sortable_columns;
		}

		function get_bulk_actions() {
			$actions = array(
				// 'delete' => SP()->primitives->admin_text( 'Delete' )
			);

			return $actions;
		}

		function no_items() {
			SP()->primitives->admin_etext( 'No members found' );
		}

		function get_table_classes() {
			return array( 'widefat', 'striped', $this->_args['plural'], 'spMobileTable1280' );
		}

		function display_rows() {
			$records = $this->items;
			if ( ! empty( $records ) ) {
				list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

				foreach ( $records as $rec ) {
					echo '<tr>';

					foreach ( $columns as $column_name => $column_display_name ) {
						$classes    = "$column_name column-$column_name";
						$data       = 'data-label="' . wp_strip_all_tags( $column_display_name ) . '"';
						$attributes = "class='$classes' $data";

						switch ( $column_name ) {

							default;
								echo "<td $attributes>";
								switch ( $column_name ) {
									case 'user_id':
										echo $rec['user_id'];
										break;

									case 'display_name':
                                        echo '<div style="display: flex;">';
                                            echo '<div style="margin-right: 20px;">';
                                                echo sp_UserAvatar( "link=none&context=user&echo=0", $rec['user_id'] );
                                            echo '</div>';
                                            echo '<div>';
                                                $nonce       = wp_create_nonce( 'bulk-users' );
                                                $user_action = ( is_multisite() ) ? 'remove' : 'delete';


                                                $actions  = [
                                                    //'edit'    => '<a href="' . admin_url( 'user-edit.php?user_id=' . $rec['user_id'] ) . '&amp;wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php"><span class="sf-icon sf-blue sf-edit"></span></a>',
                                                    'delete'  => '<a href="' . admin_url( 'users.php?action=' . $user_action . '&amp;user=' . $rec['user_id'] . "&amp;_wpnonce=$nonce&amp;wp_http_referer=admin.php?page=" . SP_FOLDER_NAME . "/admin/panel-users/spa-users.php" ) . '"><span class="sf-icon sf-blue sf-delete"></span></a>',
                                                ];
                                                echo '<a href="' . admin_url( 'user-edit.php?user_id=' . $rec['user_id'] ) . '&amp;wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php">';
                                                    echo SP()->displayFilters->name( $rec['display_name'] ) . '<br>';
                                                echo '</a>';
                                                echo $rec['user_login'];
                                            echo '</div>';
                                            echo '<div>';
                                                echo $this->row_actions( $actions );
                                            echo '</div>';
                                        echo '</div>';
										break;

									case 'user_registered':
									case 'lastvisit':
										$newDate = new DateTime( $rec[ $column_name ] );
										echo $newDate->format( 'M j, Y' );
										break;

									case 'posts':
										echo max( $rec['posts'], 0 );
										break;
									default:
										if(isset($rec[ $column_name ]))
											echo $rec[ $column_name ];
								}
								echo '</td>';
						}
					}

					echo '</tr>';
				}
			}
		}

		function display_mobile_rows() {
			$records = $this->items;
			if ( ! empty( $records ) ) {
				list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

				foreach ( $records as $rec ) {
					echo '<div class="spMobileTableDataUsers">';

					$columns_sort = array(
						'cb'              => $columns['cb'],
						'user_id'         => $columns['user_id'],
						'avatar'          => $columns['avatar'],
						'display_name'    => $columns['display_name'],
						'user_login'      => $columns['user_login'],
						'posts'           => $columns['posts'],
						'memberships'     => $columns['memberships'],
						'rank'            => $columns['rank'],
						'user_registered' => $columns['user_registered'],
						'lastvisit'       => $columns['lastvisit'],
                    );

					foreach ( $columns_sort as $column_name => $column_display_name ) {
						$classes    = "$column_name column-$column_name";
						$data       = 'data-label="' . wp_strip_all_tags( $column_display_name ) . '"';
						$attributes = "class='$classes' $data";

						switch ( $column_name ) {
							case 'cb':
								echo '<div scope="row" class="check-column">' . sprintf( '<input type="checkbox" name="users[]" value="%s" />', $rec['user_id'] ) . '</div>';
								break;

							default;
								echo "<div $attributes>";
								switch ( $column_name ) {
									case 'user_id':

										$nonce       = wp_create_nonce( 'bulk-users' );
										$site        = wp_nonce_url( SPAJAXURL . 'profile&amp;targetaction=spa_popup&amp;user=' . $rec['user_id'], 'profile' );
										$title       = SP()->primitives->admin_text( 'Member Profile' );
										$user_action = ( is_multisite() ) ? 'remove' : 'delete';
										
										$site_groups        = wp_nonce_url( SPAJAXURL . 'membergroup&amp;user=' . $rec['user_id'], 'users-loader' );
										$title_group       = SP()->primitives->admin_text( 'Member Groups' );
										
										$actions     = array(
											'edit'    => '<a href="' . admin_url( 'user-edit.php?user_id=' . $rec['user_id'] ) . '&amp;wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php"><span class="sf-icon sf-blue sf-edit"></span></a>',
											'delete'  => '<a href="' . admin_url( 'users.php?action=' . $user_action . '&amp;user=' . $rec['user_id'] . "&amp;_wpnonce=$nonce&amp;wp_http_referer=admin.php?page=" . SP_FOLDER_NAME . "/admin/panel-users/spa-users.php" ) . '"><span class="sf-icon sf-blue sf-delete"></span></a>',
											'profile' => '<a id="memberprofile' . $rec['user_id'] . '" class="spOpenDialog" data-site="' . $site . '" data-label="' . $title . '" data-width="750" data-height="0" data-align="center"><span class="sf-icon sf-blue sf-profiles"></span></a>',
											'groups' => '<a id="membergroup' . $rec['user_id'] . '" class="spOpenDialog" data-site="' . $site_groups . '" data-label="' . $title_group . '" data-width="750" data-height="0" data-align="center"><span class="sf-icon sf-user-groups sf-blue"></span></a>'
										);

										echo $this->row_actions( $actions );
										break;

									case 'display_name':
										echo SP()->displayFilters->name( $rec['display_name'] );
										break;

									case 'user_registered':
									case 'lastvisit':
										$newDate = new DateTime( $rec[ $column_name ] );
										echo $newDate->format( 'M j, Y' );
										break;

									case 'posts':
										echo max( $rec['posts'], 0 );
										break;
									case 'avatar':
										$avatarClass = "sf-Avatar";
										$imgClass    = "sf-imgClass";
										$avatarSize  = 20;
										echo "<style>.sf-Avatar,.sf-imgClass{border-radius: 50%;}</style>";
										echo sp_UserAvatar( "tagClass=$avatarClass&size=$avatarSize&imgClass=$imgClass&link=none&context=user&echo=0", $rec['user_id'] );
										break;
									default:
										echo $rec[ $column_name ];
								}
								echo '</div>';
						}
					}
					echo '</div>';
				}
			}
		}

		function get_views() {
			$usergroup  = isset( $_REQUEST['usergroup'] ) ? (int) $_REQUEST['usergroup'] : '';
			$usergroups = spa_get_usergroups_all();
			$members    = SP()->DB->select( 'SELECT count(*) as count FROM ' . SPMEMBERS, 'var' );

			$class           = empty( $usergroup ) ? ' class="current"' : '';
			$ug_links        = array();
			$ug_links['all'] = "<a href='" . SPADMINUSER . "'$class>" . SP()->primitives->admin_text( 'All Members' ) . " ($members)</a>";
			foreach ( $usergroups as $ug ) {
				$class                           = ( $ug->usergroup_id == $usergroup ) ? ' class="current"' : '';
				$count                           = SP()->DB->count( SPMEMBERSHIPS, "usergroup_id = $ug->usergroup_id" );
				$name                            = $ug->usergroup_name . ' (' . $count . ')';
				$ug_links[ $ug->usergroup_name ] = "<a href='" . esc_url( add_query_arg( 'usergroup', $ug->usergroup_id, SPADMINUSER ) ) . "'$class>$name</a>";
			}

			$nomembership              = SP()->DB->select( 'SELECT count(*) as count
                            FROM ' . SPMEMBERS . '
                    		WHERE user_id NOT IN (SELECT user_id FROM ' . SPMEMBERSHIPS . ') AND admin=0', 'var' );
			$class                     = ( $usergroup === - 1 ) ? ' class="current"' : '';
			$ug_links['No Membership'] = "<a href='" . esc_url( add_query_arg( 'usergroup', - 1, SPADMINUSER ) ) . "'$class>" . SP()->primitives->admin_text( 'No Membership' ) . " ($nomembership)</a>";

			return $ug_links;
		}

		function prepare_items() {

			# init the class
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			# start the query
			$query             = new stdClass();
			$query->table      = SPMEMBERS;
			$query->found_rows = true;
			$query->fields     = SPMEMBERS . '.user_id, ' . SPMEMBERS . '.display_name, lastvisit, posts, admin, moderator, user_login, user_registered, avatar';
			$query->join       = array( SPUSERS . ' ON ' . SPMEMBERS . '.user_id = ' . SPUSERS . '.ID' );

			# handle specific usergroup
			$usergroup = isset( $_REQUEST['usergroup'] ) ? (int) $_REQUEST['usergroup'] : '';
			if ( $usergroup ) {
				if ( $usergroup == - 1 ) {
					$query->left_join = array( SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id' );
					$query->where     = SPMEMBERSHIPS . ".user_id IS NULL AND admin = 0";
				} else {
					$query->join[] = SPMEMBERSHIPS . ' ON ' . SPMEMBERS . '.user_id = ' . SPMEMBERSHIPS . '.user_id';
					$query->where  = SPMEMBERSHIPS . ".usergroup_id = $usergroup";
				}

				# need to fool wp on request uri since our admin urls are wrong
				$_SERVER['REQUEST_URI'] .= '&usergroup=' . $usergroup;
			}

			# handle sort ordering
			$orderby        = ( ! empty( $_GET['orderby'] ) ) ? SP()->filters->esc_sql( $_GET['orderby'] ) : 'ASC';
			$order          = ( ! empty( $_GET['order'] ) ) ? SP()->filters->esc_sql( $_GET['order'] ) : '';
			$query->orderby = ( ! empty( $orderby ) && ! empty( $order ) ) ? "$orderby $order" : '';

			# searching
			$search_term = isset( $_GET['s'] ) ? SP()->saveFilters->title( trim( $_GET['s'] ) ) : '';
			if ( $search_term ) {
				$searches = array();
				foreach ( array( 'user_login', SPMEMBERS . '.display_name' ) as $col ) {
					$searches[] = $col . " LIKE '%$search_term%'";
				}
				$where        = implode( ' OR ', $searches );
				$query->where = ( ! empty( $query->where ) ) ? " AND ($where)" : $where;

				# if no ordering, list matches that start with the search term first
				global $wpdb;
				if ( empty( $query->orderby ) ) {
					$query->orderby = 'IF (' . SPMEMBERS . ".display_name LIKE '" . SP()->filters->esc_sql( $wpdb->esc_like( $search_term ) ) . "%', 0, IF (" . SPMEMBERS . ".display_name LIKE '%" . SP()->filters->esc_sql( $wpdb->esc_like( $search_term ) ) . "%', 1, 2))";
				}

				# need to fool wp on request uri since our admin urls are wrong
				$_SERVER['REQUEST_URI'] .= '&s=' . $search_term;
			}

			# do our members query
			$query             = apply_filters( 'sph_admin_members_list_query', $query );
			$records           = SP()->DB->select( $query );
			$this->count_pages = count( $records );
			# pagination
			$per_page      = $this->per_page;
			$current_page  = $this->get_pagenum();
			$offset        = ( $current_page - 1 ) * $per_page;
			$query->limits = "$offset, $per_page";
			$query         = apply_filters( 'sph_admin_members_list_query', $query );
			$records       = SP()->DB->select( $query );
			# set up page links
			$total_items = SP()->DB->select( 'SELECT FOUND_ROWS()', 'var' );
			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page
			) );

			# fill the rest of the results with needed data
			$members = array();
			if ( $records ) {
				foreach ( $records as $idx => $data ) {
					# set up some data
					if ( $data->admin ) {
						$user_memberships = 'Admin';
						$status           = 'Admin';
						$start            = 0;
					} else if ( $data->moderator ) {
						$status = 'Moderator';
						$start  = 1;
					} else {
						$status = 'User';
						$start  = 1;
					}

					# get memberships for this member
					$memberships = SP()->DB->table( SPMEMBERSHIPS, "user_id=$data->user_id", '', '', '', ARRAY_A );
					if ( $memberships ) {
						foreach ( $memberships as $membership ) {
							$name = SP()->DB->table( SPUSERGROUPS, 'usergroup_id=' . $membership['usergroup_id'], 'usergroup_name' );
							if ( $start ) {
								$user_memberships = $name;
								$start            = 0;
							} else {
								$user_memberships .= ', ' . $name;
							}
						}
					} else if ( $start ) {
						$user_memberships = SP()->primitives->admin_text( 'No Memberships' );
					}

					# build the forum rank
					$rank = SP()->user->forum_rank( $status, $data->user_id, $data->posts );

					# now fill in the members array
					$members[ $idx ]['user_id']         = $data->user_id;
					$members[ $idx ]['user_login']      = $data->user_login;
					$members[ $idx ]['display_name']    = $data->display_name;
					$members[ $idx ]['user_registered'] = $data->user_registered;
					$members[ $idx ]['lastvisit']       = $data->lastvisit;
					$members[ $idx ]['posts']           = $data->posts;
					$members[ $idx ]['memberships']     = $user_memberships;
					$members[ $idx ]['rank']            = $rank[0]['name'];
					$members[ $idx ]['avatar']          = $data->avatar;
				}
			}

			# fill class items
			$this->items = $members;
		}

        function pagination($which)
        {
            return null;
        }

        function views()
        {
            $views = $this->get_views();
            /**
             * Filters the list of available list table views.
             *
             * The dynamic portion of the hook name, `$this->screen->id`, refers
             * to the ID of the current screen, usually a string.
             *
             * @since 3.5.0
             *
             * @param array $views An array of available list table views.
             */
            $views = apply_filters("views_{$this->screen->id}", $views);

            if (empty($views))
                return;

            $this->screen->render_screen_reader_content('heading_views');

            echo "<div class='spGroupList'>\n";
            foreach ($views as $class => $view) {
                echo $view;
            }
            echo "</div>";
        }
    }

	# build the class
	$membersTable = new SP_Members_Table();

	# going to display, lets prep items
	$membersTable->prepare_items();
	?>
    <form id="members-filter" method="get" action="<?php echo SPADMINUSER; ?>">
        <input type="hidden" name="page" value="<?php echo SP_FOLDER_NAME . '/admin/panel-users/spa-users.php'; ?>"/>
        <div class="">
            <div style="display: flex">
                <div style="width: 90%">
                    <?php
                    $views = $membersTable->get_views();
                    $views = apply_filters("views_{$membersTable->screen->id}", $views);
                    if (empty($views))
                        return;
                    $membersTable->screen->render_screen_reader_content('heading_views');

                    # display view links
                    $membersTable->views();
                    ?>
                </div>
                <div>
                    <input type="search" id="<?php echo esc_attr( 'search_id' ); ?>" value="<?php _admin_search_query(); ?>" form="plugin-filter"
                               placeholder="<?php echo SP()->primitives->admin_text( 'Search members' ) ?>"/>
                </div>
                <div>
                    <?php echo spa_paint_help( 'users-info', $adminhelpfile ); ?>
                </div>
            </divdisp>
			<input type="hidden" name="s" value="<?php _admin_search_query(); ?>" />
        </div>

        <div class="">
            <?php
            # display the members list table for desktop
            $membersTable->display();
            ?>
        </div>
    </form>

	<?php

	$countItems     = $membersTable->count_pages;
	$maxItemsOnPage = $membersTable->per_page;
	$countPages     = ceil( $countItems / $maxItemsOnPage );
	$pageNum        = $membersTable->get_pagenum();
	$pagination     = spa_pagination( $countPages, $pageNum);
    $userGroupId    = array_key_exists('usergroup', $_GET) ? $_GET['usergroup'] : '';

    ?>
	<?php if ( $pagination ): ?>
        <div class="sf-pagination">
            <span class="sf-pagination-links">
                <a class="sf-first-page spLoadAjax" href="javascript:void(0);"
                   data-target="#sfmaincontainer"
				   data-after_cb="after_users_listing" 
				   data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                   data-url="<?php echo wp_nonce_url( SPAJAXURL . "users-loader&amp;loadform=member-info&amp;usergroup=".$userGroupId."&amp;paged=1&amp;", 'users-loader' ) ?>"
                ></a>
                   <?php foreach ( $pagination as $n => $v ): ?>
                       <a class="spLoadAjax<?php echo $pageNum == $n ? ' sf-current-page' : '' ?>" href="javascript:void(0);"
                          data-target="#sfmaincontainer"
						  data-after_cb="after_users_listing" 
						  data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                          data-url="<?php echo wp_nonce_url( SPAJAXURL . "users-loader&amp;loadform=member-info&amp;usergroup=".$userGroupId."&amp;paged={$n}&amp;", 'users-loader' ) ?>"
                       ><?php echo $v ?></a>
                   <?php endforeach ?>
                <a class="sf-last-page spLoadAjax" href="javascript:void(0);"
                   data-target="#sfmaincontainer"
				   data-after_cb="after_users_listing" 
				   data-img="<?php echo SPADMINIMAGES . 'sp_WaitBox.gif' ?>"
                   data-url="<?php echo wp_nonce_url( SPAJAXURL . "users-loader&amp;loadform=member-info&amp;usergroup=".$userGroupId."&amp;paged={$countPages}&amp;", 'users-loader' ) ?>"
                ></a>
            </span>
        </div>
	<?php endif ?>

    <script>
		(function($) {
			$(function() {
				var location_url = $('#members-filter').attr('action') + '&' + $('#members-filter').find('[name=s]').serialize() + '&';
				
				// Update url of each column header with correct link
				$('#members-filter table thead th.sortable a').each( function() {

					var href = $(this).attr('href');
					var order_params = [];

					var url_params = href.split('?')[1].split('&');

					$.each( url_params, function() {	
					var name = this.split('=')[0];
						if( name == 'orderby' || name == 'order' ) {
							order_params.push(this);
						}
					});

					href = location_url + order_params.join('&');
					$(this).attr('href', href);
				});

                // Listen for key "enter" in search form
				$('#search_id').keyup( function(e) {
					if( e.which == 13 ) {
						$('input[type=hidden][name=s]').val($(this).val());
						$(this).closest('form').submit();
					}
				});
			});
}(jQuery))
		spj.after_users_listing();
    </script>
	<?php
	spa_paint_close_fieldset();
	spa_paint_close_panel();

	do_action( 'sph_users_members_panel' );
	spa_paint_close_container();
	spa_paint_close_tab();
}
