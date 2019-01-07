<?php
global $tooltips;

$tooltips = array();
$tooltips['plugins'] = 'The Plugins folder contains all of the forum plugins you have added to your installation.  Plugins may reside in this directory or within a subdirectory one level down from this directory.
For the benefits of relocating folders, please click on the help button in the top right of this form.';
$tooltips['themes'] = 'The themes folder contains all of the forum themes and theme templates you have added to your installation.
For the benefits of relocating folders, please click on the help button in the top right of this form.';
$tooltips['avatars'] = "The Avatars folder contains both the default three avatars and all avatars uploaded by your members. If you choose to not use the forum avatar options then you may ignore this folder.
If this folder failed to get created during the installation (due to permission settings) then you need to create it manually. Please follow these instructions:
[1] Create the new folder within the WordPress 'wp-content' folder. You may give this folder any name you choose or create a sub-folder path. The default name is 'forum-avatars'.
[2] Move or copy the three supplied default avatars into this new folder. They are supplied in the '/styles/avatars/' folder but can not be used from that location.
[3] Make sure that your new folder has the correct permissions. If you are allowing your members to upload their avatars this will need to be '775'.
[4] Finally - if you changed the name of the avatars folder - enter the path into thos storage locations form and update it.";
$tooltips['avatar-pool'] = 'The Avatar Pool Folder is the location for storing a pool of images uploaded by the forum admin from which his users can select an avatar to use. Use of the Avatar Pool will depend, of course, of the general avatar settings made in the Profile > Avatars panel.';
$tooltips['smileys'] = "The Smileys folder contains both the default supplied smileys and all smileys you upload and add to the forum. If you choose to not use the forum smiley options then you may ignore this folder.
If this folder failed to get created during the installation (due to permission settings) then you need to create it manually. Please follow these instructions:
[1] Create the new folder within the WordPress 'wp-content' folder. You may give this folder any name you choose or create a sub-folder
path. The default name is 'forum-smileys'.
[2] Move or copy the supplied default smileys into this new folder. They are supplied in the '/styles/smileys/' folder but can not be used from that location.
[3] Make sure that your new folder has the correct permissions. If you are likely to upload additional smileys this will need to be '775'.
[4] Finally - if you changed the name of the smileys folder - enter the path into thos storage locations form and update it.";
$tooltips['ranks'] = "The Forum Badges folder contains any custom images that you want to use for forum ranks.  If you are not using forum ranks or do not want images (ie badges) with the forum ranks, then you do not need to worry about this storage location path.
This folder needs to be manually created with permissions of '775' and the path entered here.";
$tooltips['language-sp'] = 'The Simple:Press Language folder should contain the .mo language file for the core Simple:Press plugin that matches your language.';
$tooltips['language-sp-plugins'] = 'The Simple:Press Plugin Language folder should contain the .mo language files, if available, for any Simple:Press plugins you may have active.';
$tooltips['language-sp-themes'] = 'The Simple:Press Theme Language folder should contain the .mo language files, if available, for the Simple:Press theme you are using.';
$tooltips['custom-icons'] = 'The Custom icons folder is a general storage area for any custom icons used by the forum. These can be replacement icons for Groups and Forums
or the three custom locations set aside for custom icons available in the Conpoents > Custom Icons panel.';
$tooltips['cache'] = 'The Cache folder contains the CSS and JavaScript library cache files.';
$tooltips['forum-images'] = 'A place to store individual forum feature images.';

$tooltips = apply_filters('sph_integration_tooltips', $tooltips);
