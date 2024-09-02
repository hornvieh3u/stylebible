<?php
/**
 * Render upload field
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="field-wrap field-wrap-<?php echo $field['name']; ?> <?php echo isset( $field['class'] ) ? $field['class'] : ''; ?>">
	<?php if ( isset( $field['label'] ) && $field['label'] ) : ?>
		<label for="wpcf7-redirect-<?php echo $field['name']; ?>">
			<strong><?php echo esc_html( $field['label'] ); ?></strong>
			<?php echo isset( $field['tooltip'] ) ? cf7r_tooltip( $field['tooltip'] ) : ''; ?>
		</label>
	<?php endif; ?>
	<?php if ( isset( $field['sub_title'] ) && $field['sub_title'] ) : ?>
		<div class="wpcf7-subtitle">
			<?php echo $field['sub_title']; ?>
		</div>
	<?php endif; ?>
	<div class="image-container file-container">
		<input value="<?php echo esc_html( $field['value'] ); ?>" alt="" type="text" class="file-url" />
		<a title="Set File" href="javascript:;" class="image-uploader-btn browser button button-hero"><?php _e( 'Select File', 'wpcf7-redirect' ); ?></a>
		<a title="Remove File" href="javascript:;" class="image-remove-btn browser button"><span class="dashicons dashicons-no-alt"></span></a>
		<input type="hidden" class="wpcf7-redirect-<?php echo $field['name']; ?>-fields" placeholder="<?php echo esc_html( $field['placeholder'] ); ?>" name="wpcf7-redirect<?php echo $prefix; ?>[<?php echo $field['name']; ?>]" value="<?php echo esc_html( $field['value'] ); ?>" <?php echo isset( $field['input_attr'] ) ? $field['input_attr'] : ''; ?>>
	</div>
	<div class="field-footer">
		<?php echo isset( $field['footer'] ) ? $field['footer'] : ''; ?>
	</div>
</div>
