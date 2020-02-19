<?php

	sp_SectionStart('tagClass=spProfileShowSection spCenter', 'profileShow');

		# output section for basic user info
		sp_SectionStart('tagClass=spProfileShowBasicSection', 'profileBasic');

			# show avatar and rank
			sp_SectionStart('tagClass=spProfileShowInfoSection spLeft', 'profileAvatarRank');

				sp_SectionStart('tagClass=spPlainSection spCenter avatarSection', '');

					sp_UserAvatar('context=user&link=', SP()->user->profileUser);
					sp_UserForumRank('', SP()->user->profileUser->rank);
					sp_UserSpecialRank('', SP()->user->profileUser->special_rank);
                    if (function_exists('sp_UserReputationLevel')) sp_UserReputationLevel('', SP()->user->profileUser);

				sp_SectionEnd();
				
				
				
				sp_InsertBreak();

			sp_SectionEnd('', 'profileAvatarRank');

		# show user stats
			sp_SectionStart('tagClass=spProfileShowInfoSection spRight', 'profileStats');
			
			
			if( 0 === count( $groups ) ) {
				printf( '<div class="spUsergroupEmpty">%s</div>', sprintf( '%s is not a member of a group', SP()->user->profileUser->display_name ) );
			} else {
				
				printf( '<div class="spUsergroupListMsg">%s</div>', sprintf( '%s belongs to following groups', SP()->user->profileUser->display_name ) );
				
				echo '<ul class="spUsergroupsList">';
				foreach ( $groups as $group ) {
					echo '<li>' . $group->usergroup_name . '</li>';
				}
				echo '</ul>';
			}
			
			

				

			sp_SectionEnd('', 'profileStats');

		sp_SectionEnd('tagClass=spClear', 'profileBasic');

			

		

	sp_SectionEnd('tagClass=spClear', 'profileShow');
