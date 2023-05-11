<?php
/*
Simple:Press
Admin Forums Ordering Form
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_forums_ordering_form($groupId=0) {
	$where = '';
	if ($groupId) $where = "group_id=$groupId";
	$groups = SP()->DB->table(SPGROUPS, $where, '', 'group_seq');
?>
<script>
	(function(spj, $, undefined) {
		$(document).ready(function() {
			<?php if ($groupId != 0) { ?>
			<?php } ?>
			$('#groupList').nestedSortable({
				handle: 'div',
				items: 'li',
				tolerance: 'intersect',
				listType: 'ul',
				protectRoot: true,
				placeholder: 'sortable-placeholder',
				forcePlaceholderSize: true,
				helper: 'clone',
				tabSize: 30,
				maxLevels: 10,
				scroll: true,
				scrollSensitivity: 1,
				scrollSpeed: 1
			});

			$('#sfforumorder').ajaxForm({
				target: '#sfmsgspot',
				beforeSubmit: function() {
					$('#sfmsgspot').show();
					$('#sfmsgspot').html(sp_platform_vars.pWait);
				},
				success: function() {
					$('#sfmsgspot').hide();
					<?php if ($groupId == 0) { ?>
					$('#sfreloadfo').click();
					<?php } else { ?>
					$('#sfreloadfb').click();
					<?php } ?>
					$('#sfmsgspot').show();
					$('#sfmsgspot').delay(3000).slideUp(500);
				},
				beforeSerialize: function() {
					$("input#spForumsOrder").val($("#groupList").nestedSortable('serialize'));
				}
			});
		});
	}(window.spj = window.spj || {}, jQuery));
</script>
<?php
	spa_paint_options_init();

	$ajaxURL = wp_nonce_url(SPAJAXURL.'forums-loader&amp;saveform=orderforum', 'forums-loader');
?>
	<form action="<?php echo $ajaxURL; ?>" method="post" id="sfforumorder" name="sfforumorder">
<?php
		echo sp_create_nonce('forum-adminform_forumorder');
        if ($groupId === 0) {
            spa_paint_open_tab(
                SP()->primitives->admin_text('Group and Forum Ordering'),
                true
            );
        }
		?>
            <fieldset class="sf-fieldset">
                <div class="sf-panel-body-top">
                    <h4><?php echo SP()->primitives->admin_text('Order Groups and Forums') ?></h4>
                    <?php echo spa_paint_help('order-forums') ?>
                </div>

                <?php
			//spa_paint_open_panel();
			//	spa_paint_open_fieldset(SP()->primitives->admin_text('Order Groups and Forums'), 'true', 'order-forums');
				?>
				<input type="hidden" id="cgroup" name="cgroup" value="<?php echo $groupId; ?>" />
				<?php
				echo '<div class="sf-alert-block sf-info">'.SP()->primitives->admin_text('Here you can set the order of Groups, Forums and SubForums by dragging and dropping below. After ordering, push the save button.').'</div>';

				if (!empty($groups)) {
					echo '<ul id="groupList" class="groupList sf-list">';
					foreach ($groups as $group) {
						echo "<li id='group-G$group->group_id' class='sf-list-item-depth-0'>";
						echo "<div class='sf-group-list sf-list-item'>";
						echo "<span class='sf-item-name'>$group->group_name</span>";
						echo '</div>';

						# now output any forums in the group
						$allForums = spa_get_forums_in_group($group->group_id);
						$depth = 1;

						if (!empty($allForums)) {
							echo "<ul id='forumList-$group->group_id' class='forumList sf-list'>";
							foreach ($allForums as $thisForum) {
								if ($thisForum->parent == 0) {
									sp_paint_order_forum($thisForum, $allForums, $depth);
								}
							}
							echo '</ul>';
						}
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '<input type="hidden" id="spForumsOrder" name="spForumsOrder" />';
                echo '</fieldset>';
			//	spa_paint_close_fieldset();
			//spa_paint_close_panel();
		spa_paint_close_container();
?>
		<div class="sf-form-submit-bar">
		<input type="submit" class="sf-button-primary" id="saveit" name="saveit" value="<?php SP()->primitives->admin_etext('Save Ordering'); ?>" />
        <?php if ($groupId) { ?>
		<input type="button" class="sf-button-primary spCancelForm" data-target="#group-<?php echo $group->group_id; ?>" id="sforder<?php echo $group->group_id; ?>" name="groupordercancel<?php echo $group->group_id; ?>" value="<?php SP()->primitives->admin_etext('Cancel'); ?>" />
        <?php } ?>

		</div>
<?php
		spa_paint_close_tab();
?>
	</form>
	<div class="sfform-panel-spacer"></div>
<?php
}

function sp_paint_order_forum($thisForum, $allForums, $depth) {
	# display this forum
	echo "<li id='forum-F$thisForum->forum_id' class='sf-list-item-depth-$depth'>";
	echo "<div class='sf-forum-list sf-list-item'>";
	echo "<span class='sf-item-name'>$thisForum->forum_name</span>";
	echo '</div>';
	if ($thisForum->children) {
		$depth++;
		$subForums = unserialize($thisForum->children);
		$subForums = sp_sort_by_seq($subForums, $allForums);
		echo "<ul id='subForumList-$thisForum->forum_id' class='subforumList sf-list'>";
		foreach ($subForums as $subForum) {
			foreach ($allForums as $whichForum) {
				if ($whichForum->forum_id == $subForum) {
					$thisSubForum = $whichForum;
				}
			}
			sp_paint_order_forum($thisSubForum, $allForums, $depth);
		}
		echo '</ul>';
	} else {
		echo '</li>';
	}
}

function sp_sort_by_seq($subForums, $allForums) {
	$order = array();
	foreach ($subForums as $sub) {
		foreach($allForums as $f) {
			if($f->forum_id == $sub) {
				$order[$f->forum_seq] = $sub;
			}
		}
	}
	ksort($order);
	return $order;
}
