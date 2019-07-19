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

	spa_paint_open_tab(/*SP()->primitives->admin_text('Users').' - '.*/ SP()->primitives->admin_text( 'Member Information' ), true );
	spa_paint_open_panel();
	spa_paint_open_fieldset( SP()->primitives->admin_text( 'Member Information' ), false, '', false );
	if ( ! class_exists( 'SP_List_Table' ) ) {
		require_once SP_PLUGIN_DIR . '/admin/library/sp-list-table.php';
	}

	class SP_Members_Table extends SP_List_Table {
		public $per_page = 2;
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
				'cb'              => '<input type="checkbox" />',
				'avatar'          => SP()->primitives->admin_text( 'Avatar' ),
				'user_id'         => SP()->primitives->admin_text( 'ID' ),
				'display_name'    => SP()->primitives->admin_text( 'Display Name' ),
				'user_login'      => SP()->primitives->admin_text( 'Login' ),
				'posts'           => SP()->primitives->admin_text( 'Posts' ),
				'memberships'     => SP()->primitives->admin_text( 'Memberships' ),
				'rank'            => SP()->primitives->admin_text( 'Forum Rank' ),
				'user_registered' => SP()->primitives->admin_text( 'Registered On' ),
				'lastvisit'       => SP()->primitives->admin_text( 'Last Visit' ),
				'more'            => SP()->primitives->admin_text( '' ),
			);

			return $columns;
		}

		public function get_sortable_columns() {
			$sortable_columns = array(
				'user_id'         => array( 'user_id', true ),
				'avatar'          => array( 'avatar', false ),
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
				'delete' => SP()->primitives->admin_text( 'Delete' )
			);

			return $actions;
		}

		function no_items() {
			SP()->primitives->admin_etext( 'No members found' );
		}

		function get_table_classes() {
			return array( 'widefat', 'fixed', 'striped', $this->_args['plural'], 'spMobileTable1280' );
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
							case 'cb':
								echo '<td scope="row" class="check-column">' . sprintf( '<input style="left:0; position:relative" type="checkbox" name="users[]" value="%s" />', $rec['user_id'] ) . '</td>';
								break;

							default;
								echo "<td $attributes>";
								switch ( $column_name ) {
									case 'user_id':
										echo $rec['user_id'];

										$nonce       = wp_create_nonce( 'bulk-users' );
										$site        = wp_nonce_url( SPAJAXURL . 'profile&amp;targetaction=popup&amp;user=' . $rec['user_id'], 'profile' );
										$title       = SP()->primitives->admin_text( 'Member Profile' );
										$user_action = ( is_multisite() ) ? 'remove' : 'delete';
										$actions     = array(
											'edit'    => '<a href="' . admin_url( 'user-edit.php?user_id=' . $rec['user_id'] ) . '&amp;wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php"><span class="sf-icon sf-blue sf-edit"></span></a>',
											'delete'  => '<a href="' . admin_url( 'users.php?action=' . $user_action . '&amp;user=' . $rec['user_id'] . "&amp;_wpnonce=$nonce&amp;wp_http_referer=admin.php?page=" . SP_FOLDER_NAME . "/admin/panel-users/spa-users.php" ) . '"><span class="sf-icon sf-blue sf-delete"></span></a>',
											'profile' => '<a id="memberprofile' . $rec['user_id'] . '" class="spOpenDialog" data-site="' . $site . '" data-label="' . $title . '" data-width="750" data-height="0" data-align="center"><span class="sf-icon sf-blue sf-profiles"></span></a>',
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
										//echo "<div style='max-width:16px;'>".get_avatar($rec['user_id'],  16)."</div>";
										$avatarClass = "sf-Avatar";
										$imgClass    = "sf-imgClass";
										$avatarSize  = 20;
										echo "<style>.sf-Avatar,.sf-imgClass{border-radius: 50%;}</style>";
										echo sp_UserAvatar( "tagClass=$avatarClass&size=$avatarSize&imgClass=$imgClass&link=none&context=user&echo=0", $rec['user_id'] );
										break;
									default:
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
										$site        = wp_nonce_url( SPAJAXURL . 'profile&amp;targetaction=popup&amp;user=' . $rec['user_id'], 'profile' );
										$title       = SP()->primitives->admin_text( 'Member Profile' );
										$user_action = ( is_multisite() ) ? 'remove' : 'delete';
										$actions     = array(
											'edit'    => '<a href="' . admin_url( 'user-edit.php?user_id=' . $rec['user_id'] ) . '&amp;wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php"><span class="sf-icon sf-blue sf-edit"></span></a>',
											'delete'  => '<a href="' . admin_url( 'users.php?action=' . $user_action . '&amp;user=' . $rec['user_id'] . "&amp;_wpnonce=$nonce&amp;wp_http_referer=admin.php?page=" . SP_FOLDER_NAME . "/admin/panel-users/spa-users.php" ) . '"><span class="sf-icon sf-blue sf-delete"></span></a>',
											'profile' => '<a id="memberprofile' . $rec['user_id'] . '" class="spOpenDialog" data-site="' . $site . '" data-label="' . $title . '" data-width="750" data-height="0" data-align="center"><span class="sf-icon sf-blue sf-profiles"></span></a>',
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
			$ug_links['all'] = "<a href='" . SPADMINUSER . "'$class>" . SP()->primitives->admin_text( 'All Members' ) . " <span class='count'>($members)</span></a>";
			foreach ( $usergroups as $ug ) {
				$class                           = ( $ug->usergroup_id == $usergroup ) ? ' class="current"' : '';
				$count                           = SP()->DB->count( SPMEMBERSHIPS, "usergroup_id = $ug->usergroup_id" );
				$name                            = $ug->usergroup_name . ' <span class="count">(' . $count . ')</span>';
				$ug_links[ $ug->usergroup_name ] = "<a href='" . esc_url( add_query_arg( 'usergroup', $ug->usergroup_id, SPADMINUSER ) ) . "'$class>$name</a>";
			}

			$nomembership              = SP()->DB->select( 'SELECT count(*) as count
                            FROM ' . SPMEMBERS . '
                    		WHERE user_id NOT IN (SELECT user_id FROM ' . SPMEMBERSHIPS . ') AND admin=0', 'var' );
			$class                     = ( $usergroup === - 1 ) ? ' class="current"' : '';
			$ug_links['No Membership'] = "<a href='" . esc_url( add_query_arg( 'usergroup', - 1, SPADMINUSER ) ) . "'$class>" . SP()->primitives->admin_text( 'No Membership' ) . " <span class='count'>($nomembership)</span></a>";

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
	}

	# build the class
	$membersTable = new SP_Members_Table();

	# any actions to process?
	switch ( $membersTable->current_action() ) {
		case 'delete':
			$userids = array_map( 'intval', (array) $_REQUEST['users'] );
			$url     = self_admin_url( 'users.php?action=delete&users[]=' . implode( '&users[]=', $userids ) . '&wp_http_referer=admin.php?page=' . SP_FOLDER_NAME . '/admin/panel-users/spa-users.php' );
			$url     = str_replace( '&amp;', '&', wp_nonce_url( $url, 'bulk-users' ) );
			SP()->primitives->redirect( $url );
			exit();
	}

	# going to display, lets prep items
	$membersTable->prepare_items();
	?>
    <form id="members-filter" method="get" action="<?php echo SPADMINUSER; ?>">
        <input type="hidden" name="page" value="<?php echo SP_FOLDER_NAME . '/admin/panel-users/spa-users.php'; ?>"/>
        <div class="sf-panel-body-top">

			<?php
            $views = $membersTable->get_views();
			$views = apply_filters("views_{$membersTable->screen->id}", $views);
			if (empty($views))
				return;
			$membersTable->screen->render_screen_reader_content('heading_views');

			echo '<div class="sf-plugin-hide">';
                echo "<ul class='subsubsub-desk'>\n";

                foreach ($views as $class => $view) {
                    $view = str_replace('All Members','All', $view);
                    $views[$class] = "\t<li class='$class'>$view";
                }
                echo implode(" </li>\n", $views) . "</li>\n";

			    echo "</ul>";
			echo '</div>';

			# display view links
			$membersTable->views();
			?>
            <div class="sf-panel-body-top-right sf-plugin-hide">
                <p class="search-box">
                    <input type="search" id="<?php echo esc_attr( 'search_id' ); ?>" name="s" value="<?php _admin_search_query(); ?>" form="plugin-filter"
                           placeholder="<?php echo SP()->primitives->admin_text( 'Search members' ) ?>"/>
                </p>
				<?php echo spa_paint_help( 'users-info', $adminhelpfile ); ?>
            </div>

            <div class="sf-panel-body-top-right sf-showm sf-width-100-per">
                <p class="search-box">
                    <input type="search" class="" id="<?php echo esc_attr( 'search_id' ); ?>" name="s" value="<?php _admin_search_query(); ?>" form="plugin-filter"
                           placeholder="<?php echo SP()->primitives->admin_text( 'Search members' ) ?>"/>
                </p>
                <div class="sf-pt-15">
					<? echo spa_paint_help( 'users-info', $adminhelpfile ); ?>
                </div>
            </div>
        </div>
        <div class="sf-plugin-hide">
            <?php
            # display the members list table for desktop
            $membersTable->display();
            ?>
        </div>

        <div class="sf-showm">
            <input type="checkbox" name="cbhead" id="cbhead_mob"/>
            <label class="wp-core-ui sf-label-select-all" for='cbhead_mob'>SELECT ALL</label>
                <?php
                # display the members list table for mobile
                $membersTable->display_mobile_rows();
                ?>
        </div>
    </form>
	<?php
	$countItems     = $membersTable->count_pages;
	$maxItemsOnPage = $membersTable->per_page;
	$countPages     = ceil( $countItems / $maxItemsOnPage );
	$pageNum        = $membersTable->get_pagenum();
	$pagination     = spa_pagination( $countPages, $pageNum, 8, 2 ); ?>
	<?php if ( $pagination ): ?>
        <div class="sf-pagination">
            <span class="sf-pagination-links">
                <a class="sf-first-page spLoadAjax" href="javascript:void(0);"
                   data-target=".sf-full-form"
                   data-url="<?php echo wp_nonce_url( SPAJAXURL . "users&amp;ug_no=1&amp;page=1&amp;filter={$filter}", 'users' ) ?>"
                ></a>
                   <?php foreach ( $pagination as $n => $v ): ?>
                       <a class="spLoadAjax<?php echo $pageNum == $n ? ' sf-current-page' : '' ?>" href="javascript:void(0);"
                          data-target=".sf-full-form"
                          data-url="<?php echo wp_nonce_url( SPAJAXURL . "users&amp;ug_no=1&amp;page={$n}&amp;filter={$filter}", 'users' ) ?>"
                       ><?php echo $v ?></a>
                   <?php endforeach ?>
                <a class="sf-last-page spLoadAjax" href="javascript:void(0);"
                   data-target=".sf-full-form"
                   data-url="<?php echo wp_nonce_url( SPAJAXURL . "users&amp;ug_no=1&amp;page={$countPages}&amp;filter={$filter}", 'users' ) ?>"
                ></a>
            </span>
        </div>
	<?php endif ?>

    <script>

        ///////////////////////////////////////////////////////////////////////////////
        // More Column
        if (jQuery(window).width() < 768) putDiv();
        var $action = jQuery('.row-actions');
        jQuery('.row-actions').remove();
        jQuery('.spMobileTableDataUsers .column-more').each(function (index) {
            jQuery(this).addClass('sf-hide-mobile');
            jQuery(this).append($action[index]);
            jQuery(this).append("<div class=\"drop-down\"><span class=\"sf-icon sf-gray sf-more\"></div>");
        });
        jQuery('.spMobileTableDataUsers .column-user_id').each(function (index) {
            jQuery(this).append($action[index]);
            jQuery(this).find('.row-actions').addClass('sf-hide-full');
        });

        jQuery('.column-more .row-actions').toggleClass('hide');

        jQuery('.drop-down').on('click', function () {
            jQuery(this).parent().find('.row-actions').toggleClass('hide');
        });
        var paggs = '<div class="pagginator"><ul></ul></div>';
        ////////////////////////////////////////////////////////////////////////////////
        // DropdownMenu
        jQuery('#members-filter > table').insertAfter("#members-filter > .sf-panel-body-top");



        jQuery('<div class="sf-showm sf-actions sf-width-100-per sf-dropdown"></div>').insertBefore('#members-filter .sf-panel-body-top .subsubsub');
        jQuery('#members-filter .sf-panel-body-top .subsubsub').appendTo('#members-filter .sf-panel-body-top .sf-dropdown');

        jQuery('<div class="sf-dropdown-cur">'+ jQuery('#members-filter .subsubsub .current').text()  +'</div>').insertBefore('#members-filter .sf-panel-body-top .sf-dropdown .subsubsub');




        jQuery('#members-filter .sf-panel-body-top .sf-dropdown .sf-dropdown-cur').on('click',function(){
            jQuery('#members-filter .sf-panel-body-top .subsubsub').toggleClass('sf-hide-full');
        });

        if (jQuery(window).width() < 768) {
            jQuery('.sf-dropdown .subsubsub').addClass('sf-hide-full');
        } else {
            jQuery('.sf-dropdown .subsubsub').removeClass('sf-hide-full');
        }
        ;
        jQuery('#cb-select-all-1').on('change', function (event) {
            if (jQuery(this)[0]["checked"]) {
                jQuery('.spMobileTableDataUsers .check-column input').each(function (index) {
                    jQuery(this)[0]["checked"] = true;
                })
            } else {
                jQuery('.spMobileTableDataUsers .check-column input').each(function (index) {
                    jQuery(this)[0]["checked"] = false;
                })
            }
            ;
        });
        jQuery(window).on('resize', function () {
            if (jQuery(window).width() < 768) {
                putDiv();
            } else {
                delDiv()
            }
            ;
        });

        function delDiv() {
            if (jQuery(".avatar-text-lable").length != 0) {
                jQuery(".avatar-text-lable").remove();
            }
            ;
            if (jQuery(".login-text-lable").length != 0) {
                jQuery(".login-text-lable").remove();
            }
            ;
            if (jQuery(".name-text-lable").length != 0) {
                jQuery(".name-text-lable").remove();
            }
            ;
            if (jQuery(".reg-text-lable").length != 0) {
                jQuery(".reg-text-lable").remove();
            }
            ;
            if (jQuery(".visit-text-lable").length != 0) {
                jQuery(".visit-text-lable").remove();
            }
            ;
            if (jQuery(".posts-text-lable").length != 0) {
                jQuery(".posts-text-lable").remove();
            }
            ;
            if (jQuery(".member-text-lable").length != 0) {
                jQuery(".member-text-lable").remove();
            }
            ;
            if (jQuery(".rank-text-lable").length != 0) {
                jQuery(".rank-text-lable").remove();
            }
            ;

        }

        function putDiv() {
            if (jQuery(".avatar-text-lable").length == 0) {
                jQuery("<div class=\"avatar-text-lable\">Avatar</div>").insertBefore(".spMobileTableDataUsers [data-label=\"Avatar\"]");
            }
            ;
            if (jQuery(".name-text-lable").length == 0) {
                jQuery("<div class=\"name-text-lable\">Display Name</div>").insertAfter(".spMobileTableDataUsers .avatar-text-lable");
            }
            ;
            if (jQuery(".login-text-lable").length == 0) {
                jQuery("<div class=\"login-text-lable\">User Login</div>").insertBefore(".spMobileTableDataUsers [data-label=\"Login\"]");
            }
            ;
            if (jQuery(".posts-text-lable").length == 0) {
                jQuery("<div class=\"posts-text-lable\">Posts</div>").insertAfter(".spMobileTableDataUsers .login-text-lable");
            }
            ;
            if (jQuery(".member-text-lable").length == 0) {
                jQuery("<div class=\"member-text-lable\">Memberships</div>").insertBefore(".spMobileTableDataUsers [data-label=\"Memberships\"]");
            }
            ;
            if (jQuery(".rank-text-lable").length == 0) {
                jQuery("<div class=\"rank-text-lable\">Forum Rank</div>").insertAfter(".spMobileTableDataUsers .member-text-lable");
            }
            ;
            if (jQuery(".reg-text-lable").length == 0) {
                jQuery("<div class=\"reg-text-lable\">Registered</div>").insertBefore(".spMobileTableDataUsers [data-label=\"Registered On\"]");
            }
            ;
            if (jQuery(".visit-text-lable").length == 0) {
                jQuery("<div class=\"visit-text-lable\">Last Visit</div>").insertAfter(".spMobileTableDataUsers .reg-text-lable");
            }
            ;
        };
    </script>
	<?php
	spa_paint_close_fieldset();
	echo '<div class="sfform-panel-spacer"></div>';
	spa_paint_close_panel();

	do_action( 'sph_users_members_panel' );
	spa_paint_close_container();
	spa_paint_close_tab();
}
