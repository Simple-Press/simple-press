<?php
/*
Simple:Press
Admin Profiles Tabs and Menus Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if ( ! defined( 'ABSPATH' ) ) {
	die('Access denied - you cannot directly call this file');
}

function spa_profiles_tabs_menus_form() {
	# get profile tabs and menus
	$tabs = spa_get_tabsmenus_data();
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			spj.loadAjaxForm('sptabsmenusform', 'sfreloadptm');

			$('#tabsList').sortable({
				placeholder: 'sortable-placeholder',
				update: function () {
					/* run sortable on tabs since the order changed */
					$("input#spTabsOrder").val($("#tabsList").sortable('serialize'));
				}
			});

			$('.sf-list-sortable').sortable({
				placeholder: 'sortable-placeholder',
				connectWith: $('.sf-list-sortable'),
				update: function () {
					/* run sortable on changed menu */
					id = this.id;
					tid = id.substring(16);
					$("input#spMenusOrder"+tid).val($("#sf-list-sortable"+tid).sortable('serialize'));
				}
			});

			<?php if ($tabs) { ?>
				/* now run sortable on tabs and menus so they're guaranteed to be populate */
				$("input#spTabsOrder").val($("#tabsList").sortable('serialize'));
				num = <?php echo esc_js(count($tabs)); ?>;
				for (i=0; i<=num; i++) {
					$("input#spMenusOrder"+i).val($("#sf-list-sortable"+i).sortable('serialize'));
				}
			<?php } ?>
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php

    $ajaxURL = wp_nonce_url(SPAJAXURL.'profiles-loader&amp;saveform=tabs-menus', 'profiles-loader');
?>
	<form action="<?php echo esc_url($ajaxURL); ?>" method="post" id="sptabsmenusform" name="sptabsmenusform">
	<?php sp_echo_create_nonce('forum-adminform_tabsmenus'); ?>
<?php
	spa_paint_options_init();

    #== CUSTOM FIELDS Tab ============================================================

	spa_paint_open_tab(/*SP()->primitives->admin_text('Profiles').' - '.*/SP()->primitives->admin_text('Profile Tabs & Menus'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(SP()->primitives->admin_text('Profile Menu Order'), true, 'profile-menus');
				echo '<div class="sf-alert-block sf-info">'.esc_html(SP()->primitives->admin_text('Here you can set the order of Profile Tabs and Menus by dragging and dropping below.  Additionally, you can edit any of the Tabs or Menus.')).'</div>';

				if (!empty($tabs)) {
					echo '<ul id="tabsList" class="tabsList sf-list">';
					foreach ($tabs as $tindex => $tab) {
						echo '<li id="tab-'.esc_attr($tindex).'" class="sf-list-item-depth-0">';
                        $class = ($tab['display']) ? '' : ' sf-list-item-disabled';
						echo "<div class='sf-list-item".esc_attr($class)."'>";
						echo '<span class="sf-item-name">'.esc_html($tab['name']).'</span>';
						echo '<span class="sf-item-controls">';
						echo '<span class="sf-item-type">'.esc_html(SP()->primitives->admin_text('Tab')).'</span>';
						echo '<a class="sf-item-edit spLayerToggle" data-target="item-edit-'.esc_attr($tindex).'">'.esc_html(SP()->primitives->admin_text('Edit Menu')).'</a>';
						echo '<input type="hidden" size="70" id="spMenusOrder'.esc_attr($tindex).'" name="spMenusOrder'.esc_attr($tindex).'" />';
						echo '</span>';
						echo '</div>';
						echo '<div id="item-edit-'.esc_attr($tindex).'" class="sf-list-item-settings sf-inline-edit">';
						echo '<p class="sf-description">'.esc_html(SP()->primitives->admin_text('Tab Name')).'<br /><input type="text" class="sfpostcontrol" id="tab-name-'.esc_attr($tindex).'" name="tab-name-'.esc_attr($tindex).'" value="'.esc_attr(SP()->displayFilters->title($tab['name'])).'" /></p>';
                        echo '<input type="hidden" id="tab-slug-'.esc_attr($tindex).'" name="tab-slug-'.esc_attr($tindex).'" value="'.esc_attr($tab['slug']).'" />';
						echo '<p class="sf-description">'.esc_html(SP()->primitives->admin_text('Tab Auth')).'<br /><input type="text" class="sfpostcontrol" id="tab-auth-'.esc_attr($tindex).'" name="tab-auth-'.esc_attr($tindex).'" value="'.esc_attr(SP()->displayFilters->title($tab['auth'])).'" /></p>';
						$checked = ($tab['display']) ? $checked = 'checked="checked" ' : '';
						echo '<p class="sf-description"><input type="checkbox" '.esc_attr($checked).'name="tab-display-'.esc_attr($tindex).'" id="sf-tab-display-'.esc_attr($tindex).'" /><label for="sf-tab-display-'.esc_attr($tindex).'">'.esc_html(SP()->primitives->admin_text('Display Tab')).'</label></p>';
						echo '<p><a class="spLayerToggle" data-target="item-edit-'.esc_attr($tindex).'" >'.esc_html(SP()->primitives->admin_text('Close')).'</a></p>';
						echo '</div>';

						# now output any menus on the tab
						echo '<ul id="sf-list-sortable'.esc_attr($tindex).'" class="sf-list-sortable sf-list">';
						if (!empty($tab['menus'])) {
							foreach ($tab['menus'] as $mindex => $menu) {
								echo '<li id="tab'.esc_attr($tindex).'-'.esc_attr($mindex).'" class="sf-list-item-depth-1">';
                                $class = ($menu['display']) ? '' : ' sf-list-item-disabled';
								echo "<div class='sf-list-item".esc_attr($class)."'>";
								echo '<span class="sf-item-name">'.esc_html($menu['name']).'</span>';
								echo '<span class="sf-item-controls sf-mr-5">';
								echo '<span class="sf-item-type">'.esc_html(SP()->primitives->admin_text('Menu')).'</span>';
								echo '<a class="sf-item-edit spLayerToggle" data-target="item-edit-'.esc_attr($tindex).'-'.esc_attr($mindex).'" >'.esc_html(SP()->primitives->admin_text('Edit Menu')).'</a>';
								echo '</span>';
								echo '</div>';
								echo '<div id="item-edit-'.esc_attr($tindex).'-'.esc_attr($mindex).'" class="sf-list-item-settings sf-inline-edit">';
								echo '<p class="sf-description">'.esc_html(SP()->primitives->admin_text('Menu Name')).'<br /><input type="text" class="sfpostcontrol" id="menu-name-'.esc_attr($tindex).'-'.esc_attr($mindex).'" name="menu-name-'.esc_attr($tindex).'-'.esc_attr($mindex).'" value="'.esc_attr(SP()->displayFilters->title($menu['name'])).'" /></p>';
                                echo '<input type="hidden" id="menu-slug-'.esc_attr($tindex).'-'.esc_attr($mindex).'" name="menu-slug-'.esc_attr($tindex).'-'.esc_attr($mindex).'" value="'.esc_attr($menu['slug']).'" />';
								echo '<p class="sf-description">'.esc_html(SP()->primitives->admin_text('Menu Auth')).'<br /><input type="text" class="sfpostcontrol" id="menu-auth-'.esc_attr($tindex).'-'.esc_attr($mindex).'" name="menu-auth-'.esc_attr($tindex).'-'.esc_attr($mindex).'" value="'.esc_attr(SP()->displayFilters->title($menu['auth'])).'" /></p>';
								echo '<p class="sf-description">'.esc_html(SP()->primitives->admin_text('Menu Form')).'<br /><input type="text" class="sfpostcontrol" id="menu-form-'.esc_attr($tindex).'-'.esc_attr($mindex).'" name="menu-form-'.esc_attr($tindex).'-'.esc_attr($mindex).'" value="'.esc_attr($menu['form']).'" /></p>';
								$checked = ($menu['display']) ? $checked = 'checked="checked" ' : '';
								echo '<p class="sf-description"><input type="checkbox" '.esc_attr($checked).'name="menu-display-'.esc_attr($tindex).'-'.esc_attr($mindex).'" id="sf-list-display-'.esc_attr($tindex).'-'.esc_attr($mindex).'" /><label for="sf-list-display-'.esc_attr($tindex).'-'.esc_attr($mindex).'">'.esc_html(SP()->primitives->admin_text('Display Menu')).'</label></p>';
								echo '<p><a class="spLayerToggle" data-target="item-edit-'.esc_attr($tindex).'-'.esc_attr($mindex).'" >'.esc_html(SP()->primitives->admin_text('Close')).'</a></p>';
								echo '</div>';
								echo '</li>';
							}
						}
						echo '</ul>';
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '<input type="hidden" class="sf-inline-edit" size="70" id="spTabsOrder" name="spTabsOrder" />';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_tabsmenus_panel');
		spa_paint_close_container();
?>
	<div class="sf-form-submit-bar">
	<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Update Profile Tabs and Menus'); ?>" />
	</div>
<?php
	spa_paint_close_tab();
?>
	</form>
<?php
}
