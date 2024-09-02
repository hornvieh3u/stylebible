<?php

/**
 * Displays the list of actions
 */

defined('ABSPATH') || exit;

$rule_id = 'default';
$actions = $this->get_actions($rule_id);

$addons_url = admin_url(get_freemius_addons_path());
?>

<h2>
	<?php _e('Submission Actions', 'wpcf7-redirect'); ?>
</h2>
<?php wp_nonce_field('manage_cf7_redirect', 'actions-nonce'); ?>
<legend>
	<?php _e('You can add actions that will be fired on submission. For details and support check', 'wpcf7-redirect'); ?> <a href="<?php echo $addons_url; ?>" target="_blank"><?php _e('out our add-ons', 'wpcf7-redirect'); ?></a>.
</legend>

<div class="actions-list">
	<div class="actions">
		<table class="wp-list-table widefat fixed striped pages" data-wrapid="<?php echo $rule_id; ?>">
			<thead>
				<tr>
					<th class="manage-column cf7r-check-column">
						<a href="#"><?php _e('No.', 'wpcf7-redirect'); ?></a>
					</th>
					<th class="manage-column column-title column-primary sortable desc">
						<a href="#"><?php _e('Title', 'wpcf7-redirect'); ?></a>
					</th>
					<th class="manage-column column-primary sortable desc">
						<a href="#"><?php _e('Type', 'wpcf7-redirect'); ?></a>
					</th>
					<th class="manage-column column-primary sortable desc">
						<a href="#"><?php _e('Active', 'wpcf7-redirect'); ?></a>
					</th>
					<th class="manage-column cf7r-check-column">
					</th>
				</tr>
			</thead>
			<tbody id="the_list">
				<?php if ($actions) : ?>
					<?php foreach ($actions as $action) : ?>
						<?php echo $action->get_action_row(); ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<div class="add-new-action-wrap">
		<select class="new-action-selector" name="new-action-selector">
			<option value="" selected="selected"><?php _e('Choose Action', 'wpcf7-redirect'); ?></option>
			<?php foreach (wpcf7r_get_available_actions() as $available_action_key => $available_action_label) : ?>
				<option value="<?php echo $available_action_key; ?>" <?php echo $available_action_label['attr']; ?>><?php echo $available_action_label['label']; ?></option>
			<?php endforeach; ?>

			<?php foreach (wpcf7_get_extensions() as $extension) : ?>
				<?php if (!isset($extension['active']) || !$extension['active']) : ?>
					<?php
					$fs_blog_id = (is_multisite() && !is_network_admin()) ?
						get_current_blog_id() :
						0;
					$purchase_url = esc_url(network_admin_url('plugin-install.php?fs_allow_updater_and_dialog=true' . (!empty($fs_blog_id) ? '&fs_blog_id=' . $fs_blog_id : '') . '&tab=plugin-information&parent_plugin_id=' . freemius_get_id() . '&plugin=' . $extension['name'] .
						'&TB_iframe=true&width=850&height=650'))
					?>
					<option value="<?php echo $purchase_url; ?>" data-action="purchase">
						<?php echo $extension['title']; ?> (<?php _e('Purchase', 'wpcf7-redirect'); ?>)
					</option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<a type="button" name="button" class="button-primary wpcf7-add-new-action" data-ruleid="<?php echo $rule_id; ?>" data-id="<?php echo $this->get_id(); ?>">
			<?php _e('Add Action', 'wpcf7-redirect'); ?>
		</a>
	</div>
</div>