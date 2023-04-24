<?php
/*
Simple:Press
Admin Options Global Display Form
$LastChangedDate: 2016-06-25 11:55:17 +0100 (Sat, 25 Jun 2016) $
$Rev: 14322 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_options_newposts_form() {
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			spj.loadAjaxForm('sfnewpostsform', '');
			$('#color-background').farbtastic('#flag-background');
			$('#color-text').farbtastic('#flag-color');
                        
                        // farbtastic >
                        $('.sf-wrap-farbtastic input').focus(function() {
                            $(this).closest('.sf-wrap-farbtastic').find('.sf-farbtastic').show();
                        });
                        $(document).click(function() {
                            $('.sf-farbtastic').hide();
                        });
                        $('.sf-wrap-farbtastic').click(function(e) {
                            e.stopPropagation();
                            $('.sf-wrap-farbtastic').not(this).find('.sf-farbtastic').hide();
                        });
                        // < farbtastic
                        // flag >
                        flagInit();
                        $('[name="flagstext"], .sf-wrap-farbtastic').on('propertychange input click', flagInit);
                        function flagInit() {
                            $('#sf-flag-target')
                                    .text($('[name="flagstext"]').val())
                                    .css({
                                        color: $('#flag-color').val(),
                                        backgroundColor: $('#flag-background').val()
                                    });
                        }
                        // < flag
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	$sfoptions = spa_get_newposts_data();
    $ajaxURL = wp_nonce_url(SPAJAXURL.'options-loader&amp;saveform=newposts', 'options-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfnewpostsform" name="sfnewposts">
	<?php echo sp_create_nonce('forum-adminform_newposts'); ?>
<?php
	spa_paint_options_init();

    #== GLOBAL Tab ============================================================

	spa_paint_open_tab(SP()->primitives->admin_text('New Posts Handling'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Post Caching'), true, 'topic-cache');
				spa_paint_input(SP()->primitives->admin_text('Cache limit'), 'topiccache', $sfoptions['topiccache'], false, false, '', SP()->primitives->admin_text('Number of new posts to keep in cache list'));
            spa_paint_close_fieldset();
		spa_paint_close_panel();
		spa_paint_open_panel();

			spa_paint_open_fieldset(SP()->primitives->admin_text('Unread Posts'), true, 'unread-posts');
				spa_paint_input(SP()->primitives->admin_text('Default'), 'sfdefunreadposts', $sfoptions['sfdefunreadposts'], false, false, '', SP()->primitives->admin_text('Default number of unread posts for users'));
				spa_paint_input(SP()->primitives->admin_text('Maximum'), 'sfmaxunreadposts', $sfoptions['sfmaxunreadposts'], false, false, '', SP()->primitives->admin_text('Maximum number of unread posts allowed to be set by users'));
                spa_paint_checkbox(SP()->primitives->admin_text('Allow users to set number of unread posts in profile'), 'sfusersunread', $sfoptions['sfusersunread']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_options_newposts_left_panel');
	spa_paint_tab_right_cell();

    	spa_paint_open_panel();
    		spa_paint_open_fieldset(__('Post Flagging', 'sp-polls'), true, 'flag-display');         	
?>
            <div class="sf-form-row">
                <label><?php echo SP()->primitives->admin_text('Flag Preview') ?></label>
                <span id="sf-flag-target" class="sf-after-label sf-badge"><?php echo $sfoptions['flagstext'] ?></span>
            </div>
            <?php spa_paint_input(SP()->primitives->admin_text('Flag Text'), 'flagstext', $sfoptions['flagstext'], false, false, '', SP()->primitives->admin_text('Text to use in flags')) ?>
            <div class="sf-form-row sf-half sf-wrap-farbtastic">
                <div class="sf-input-group sf-input-icon">
                    <label for="flag-color"><?php echo SP()->primitives->admin_text('Text color') ?></label>
                    <input id="flag-color" type="text" value="#<?php echo $sfoptions['flagscolor']; ?>" name="flagscolor" />
                </div>
                <div class="sf-farbtastic"><div id="color-text"></div></div>
                <span class="sf-sublabel sf-sublabel-small"><?php echo SP()->primitives->admin_text('New Post Flag text color') ?></span>
            </div>
            <div class="sf-form-row sf-half sf-wrap-farbtastic">
                <div class="sf-input-group sf-input-icon">
                    <label for="flag-background"><?php echo SP()->primitives->admin_text('Background color') ?></label>
                    <input id="flag-background" type="text" value="#<?php echo $sfoptions['flagsbground']; ?>" name="flagsbground" />
                </div>
                <div class="sf-farbtastic"><div id="color-background"></div></div>
                <span class="sf-sublabel sf-sublabel-small"><?php echo SP()->primitives->admin_text('New Post Flag background color') ?></span>
            </div>  
<?php
                spa_paint_checkbox(SP()->primitives->admin_text('Display new post flags'), 'flagsuse', $sfoptions['flagsuse']);
    		spa_paint_close_fieldset();
    	spa_paint_close_panel();

		do_action('sph_options_newposts_right_panel');

		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Save Changes'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
