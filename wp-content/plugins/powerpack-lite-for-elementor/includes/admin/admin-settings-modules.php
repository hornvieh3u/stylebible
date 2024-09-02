<?php
$current_filter = isset( $_GET['show'] ) ? $_GET['show'] : '';
if ( 'notused' === $current_filter || 'used' === $current_filter ) {
	$modules = pp_elements_lite_get_filter_modules( $current_filter );
} else {
	$modules = pp_elements_lite_get_modules();
}
$enabled_modules    = pp_elements_lite_get_enabled_modules();
$usage_tracking     = false;
?>
<?php if ( $usage_tracking ) { ?>
<div class="pp-general-wrap">
	<table class="form-table" style="max-width: 100%;">
		<tr valign="top">
			<th scope="row" valign="top">
				<label for="pp_allowed_tracking">
				<?php esc_html_e( 'Allow usage tracking?', 'powerpack' ); ?>
				</label>
			</th>
			<td>
				<p>
					<label for="pp_allowed_tracking">
						<input
							id="pp_allowed_tracking"
							name="pp_allowed_tracking"
							type="checkbox"
							value="on"
							<?php echo get_option( 'pp_allowed_tracking' ) ? ' checked="checked"' : ''; ?>
						/>
					</label>
				</p>
				<p class="description"><?php _e( 'Allow PowerPack to anonymously track how this plugin is used and help us make the plugin better. Opt-in to tracking and our newsletter. No sensitive data is tracked.', 'powerpack' ); ?></p>
			</td>
		</tr>
	</table>
</div>
<?php } ?>

<div class="pp-settings-section">
	<div class="pp-settings-section-header">
		<h3 class="pp-settings-section-title"><?php _e( 'Widgets', 'powerpack' ); ?></h3>
	</div>
	<div class="pp-settings-section-content">
		<button type="button" class="button toggle-all-widgets"><?php _e( 'Toggle All', 'powerpack' ); ?></button>
		<div class="pp-modules-manager-filters">
			<select class="pp-modules-manager-filter">
				<option value=""><?php esc_html_e( 'Filter: All Widgets', 'powerpack' ); ?></option>
				<option value="used"<?php echo 'used' == $current_filter ? ' selected' : ''; ?>><?php esc_html_e( 'Filter: Used Widgets', 'powerpack' ); ?></option>
				<option value="notused"<?php echo 'notused' == $current_filter ? ' selected' : ''; ?>><?php esc_html_e( 'Filter: Not Used Widgets', 'powerpack' ); ?></option>
			</select>
		</div>
		<table class="form-table pp-settings-elements-grid">
			<?php
			foreach ( $modules as $module_name => $module_title ) :
				if ( ! is_array( $enabled_modules ) && 'disabled' !== $enabled_modules ) {
					$module_enabled = true;
				} elseif ( ! is_array( $enabled_modules ) && 'disabled' === $enabled_modules ) {
					$module_enabled = false;
				} else {
					$module_enabled = in_array( $module_name, $enabled_modules ) || isset( $enabled_modules[ $module_name ] );
				}
				?>
			<tr valign="top">
				<th scope="row" valign="top">
					<label for="<?php echo $module_name; ?>">
						<?php echo $module_title; ?>
					</label>
				</th>
				<td>
					<label class="pp-admin-field-toggle">
						<input
							id="<?php echo $module_name; ?>"
							name="pp_enabled_modules[]"
							type="checkbox"
							value="<?php echo $module_name; ?>"
							<?php echo $module_enabled ? ' checked="checked"' : ''; ?>
						/>
						<span class="pp-admin-field-toggle-slider" aria-hidden="true"></span>
					</label>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>
<?php wp_nonce_field( 'pp-modules-settings', 'pp-modules-settings-nonce' ); ?>

<script>
(function($) {
	if ( $('input[name="pp_enabled_modules[]"]:checked').length > 0 ) {
		$('.toggle-all-widgets').addClass('checked');
	}
	$('.toggle-all-widgets').on('click', function() {
		if ( $(this).hasClass('checked') ) {
			$('input[name="pp_enabled_modules[]"]').prop('checked', false);
			$(this).removeClass('checked');
		} else {
			$('input[name="pp_enabled_modules[]"]').prop('checked', true);
			$(this).addClass('checked');
		}
	});

	// Filter.
	$('.pp-modules-manager-filter').on('change', function() {
		var currentUrl = location.href;
		currentUrl = currentUrl.replace( /&show=.*/g, '' );
		if ( $(this).val() !== '' ) {
			currentUrl = currentUrl + '&show=' + $(this).val();
		}
		location.href = currentUrl;
	});
})(jQuery);
</script>
