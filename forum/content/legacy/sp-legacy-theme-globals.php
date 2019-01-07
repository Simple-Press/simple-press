<?php
/*
Simple:Press
Legacy SP theme support - V1 themes
$LastChangedDate: 2017-08-12 10:30:12 +0100 (Sat, 12 Aug 2017) $
$Rev: 15504 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# These legacy globals allow level/version 1 themes to be used
# in the 6.0 class based core when used in conjunction with the 
# legacy support functions
#
# --------------------------------------------------------------------------------------

global $spThisPostUser;
global $spDevice;
global $spVars;
global $spThisMember;
global $spThisPost;
global $spThisUser;
global $spListView;
global $spProfileUser;
global $spThisPostList;

