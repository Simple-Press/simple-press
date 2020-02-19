<?php
# --------------------------------------------------------------------------------------
#
#	Simple:Press Template
#	Theme		:	Barebones
#	Template	:	profile popup show
#	Author		:	Simple:Press
#
#	The 'profile-show' template is used to display a user profile in a popup
#
# --------------------------------------------------------------------------------------

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
				
				# output Links
				sp_SectionStart('tagClass=spProfileShowLinksSection spCenter', 'profileLinks');

					
					if (SP()->user->thisUser->admin) {
						
						$editProfileLink = SP()->spPermalinks->get_url('profile/'.SP()->user->profileUser->ID.'/edit');
						printf( '<div class="spProfileEditProfileLink"><a class="sf-button-secondary" href="%s">%s</a></div>', $editProfileLink, SP()->primitives->front_text('Edit User Profile') );
					}
					
					sp_ProfileShowLink('tagClass=spProfileViewFullProfileLink sf-button-secondary', __sp('View Full Profile'));

				sp_SectionEnd('', 'profileHeader');
				
				sp_InsertBreak();

			sp_SectionEnd('', 'profileAvatarRank');

		# show user stats
			sp_SectionStart('tagClass=spProfileShowInfoSection spRight', 'profileStats');

				sp_ProfileShowDisplayName('tagClass=spProfileLabel spLeft', __sp('Username'));
				sp_ProfileShowMemberSince('tagClass=spProfileLabel spLeft', __sp('Member Since'));
				sp_ProfileShowLastVisit('tagClass=spProfileLabel spLeft', __sp('Last Visited'));
				sp_ProfileShowUserPosts('tagClass=spProfileLabel spLeft', __sp('Posts'));
				if (function_exists('sp_ProfileSendPm')) sp_ProfileSendPm('tagClass=spProfileLabel&icon=&buttonClass=spPmButton', __sp('Message'), __sp('Send PM'));

			sp_SectionEnd('', 'profileStats');

		sp_SectionEnd('tagClass=spClear', 'profileBasic');

			# show user identities
			sp_SectionStart('tagClass=spProfileShowInfoSection spRight', 'profileIdentities');

				sp_ProfileShowEmail('tagClass=spProfileLabel spLeft', __sp('Email'));
				sp_ProfileShowAIM('tagClass=spProfileLabel spLeft', __sp('AOL IM ID'));
				sp_ProfileShowYIM('tagClass=spProfileLabel spLeft', __sp('Yahoo IM ID'));
				sp_ProfileShowMSN('tagClass=spProfileLabel spLeft', __sp('MSN ID'));
				sp_ProfileShowICQ('tagClass=spProfileLabel spLeft', __sp('ICQ ID'));
				sp_ProfileShowGoogleTalk('tagClass=spProfileLabel spLeft', __sp('Google Talk ID'));
				sp_ProfileShowSkype('tagClass=spProfileLabel spLeft', __sp('Skype ID'));
				sp_ProfileShowMySpace('tagClass=spProfileLabel spLeft', __sp('MySpace ID'));
				sp_ProfileShowFacebook('tagClass=spProfileLabel spLeft', __sp('Facebook ID'));
				sp_ProfileShowTwitter('tagClass=spProfileLabel spLeft', __sp('Twitter ID'));
				sp_ProfileShowLinkedIn('tagClass=spProfileLabel spLeft', __sp('LinkedIn ID'));
				sp_ProfileShowYouTube('tagClass=spProfileLabel spLeft', __sp('YouTube ID'));

			sp_SectionEnd('', 'profileIdentities');

		sp_SectionStart('tagClass=spFlexSection spCenter', 'postedTo');

			sp_ProfileShowSearchPosts('tagClass=spLabel&leftClass=spPostedToSubmit&rightClass=spPostedToSubmit&middleClass=', __sp('View'), __sp('Topics Started by %USERNAME%'), __sp('%USERNAME%s Recent Posts'));

		sp_SectionEnd('', 'postedTo');

	sp_SectionEnd('tagClass=spClear', 'profileShow');
