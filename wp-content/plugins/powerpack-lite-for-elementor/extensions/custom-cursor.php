<?php
namespace PowerpackElementsLite\Extensions;

// Powerpack Elements classes
use PowerpackElementsLite\Base\Extension_Base;

// Elementor classes
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Custom Cursor Extension
 *
 * Adds link around sections, columns and widgets
 *
 * @since 2.7.0
 */
class Extension_Custom_Cursor extends Extension_Base {

	/**
	 * Is Common Extension
	 *
	 * Defines if the current extension is common for all element types or not
	 *
	 * @since 2.7.0
	 * @access protected
	 *
	 * @var bool
	 */
	protected $is_common = true;

	/**
	 * A list of scripts that the widgets is depended in
	 *
	 * @since 2.7.0
	 **/
	public function get_script_depends() {
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return array(
				'powerpack-frontend',
				'pp-custom-cursor',
			);
		}

		return [];
	}

	/**
	 * The description of the current extension
	 *
	 * @since 2.7.0
	 **/
	public static function get_description() {
		return __( 'Adds custom mouse cursors on columns.', 'powerpack' );
	}

	/**
	 * Is disabled by default
	 *
	 * Return wether or not the extension should be disabled by default,
	 * prior to user actually saving a value in the admin page
	 *
	 * @access public
	 * @since 2.7.0
	 * @return bool
	 */
	public static function is_default_disabled() {
		return true;
	}

	/**
	 * Add common sections
	 *
	 * @since 2.7.0
	 *
	 * @access protected
	 */
	protected function add_common_sections_actions() {

		// Activate sections for sections
		add_action( 'elementor/element/section/section_advanced/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for columns
		add_action( 'elementor/element/column/section_advanced/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for widgets
		add_action( 'elementor/element/common/_section_style/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );

		// Activate sections for containers
		add_action( 'elementor/element/container/section_layout/after_section_end', function( $element, $args ) {

			$this->add_common_sections( $element, $args );

		}, 10, 2 );
	}

	/**
	 * Add Controls
	 *
	 * @since 2.7.0
	 *
	 * @access private
	 */
	private function add_controls( $element, $args ) {

		$element_type = $element->get_type();

		$element->add_control(
			'pp_custom_cursor_enable',
			array(
				'label'              => __( 'Custom Cursor', 'powerpack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'label_on'           => __( 'Yes', 'powerpack' ),
				'label_off'          => __( 'No', 'powerpack' ),
				'return_value'       => 'yes',
				'separator'          => 'before',
				'frontend_available' => true,
			)
		);

		$element->add_control(
			'pp_custom_cursor_target',
			array(
				'label'              => __( 'Apply On', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'container',
				'options'            => array(
					'container'    => ucfirst( $element_type ),
					'css-selector' => __( 'Element Class/ID', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'          => array(
					'pp_custom_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_css_selector',
			array(
				'label'              => __( 'CSS Selector', 'powerpack' ),
				'type'               => Controls_Manager::TEXT,
				'frontend_available' => true,
				'condition'          => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_target' => 'css-selector',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_type',
			array(
				'label'              => __( 'Cursor Type', 'powerpack' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'image',
				'options'            => array(
					'image'        => __( 'Image', 'powerpack' ),
					'follow-image' => __( 'Follow Image', 'powerpack' ),
					'follow-text'  => __( 'Follow Text', 'powerpack' ),
				),
				'frontend_available' => true,
				'condition'          => array(
					'pp_custom_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_icon',
			array(
				'label'              => __( 'Choose Cursor Icon', 'powerpack' ),
				'type'               => Controls_Manager::MEDIA,
				'frontend_available' => true,
				'condition'          => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => [ 'image', 'follow-image' ],
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_text',
			array(
				'label'              => __( 'Cursor Text', 'powerpack' ),
				'type'               => Controls_Manager::TEXT,
				'frontend_available' => true,
				'condition'          => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_left_offset',
			[
				'label'              => __( 'Left Offset', 'powerpack' ),
				'type'               => Controls_Manager::SLIDER,
				'frontend_available' => true,
				'range'      => [
					'px' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'size_units'         => '',
				'condition'          => [
					'pp_custom_cursor_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'pp_custom_cursor_top_offset',
			[
				'label'              => __( 'Top Offset', 'powerpack' ),
				'type'               => Controls_Manager::SLIDER,
				'frontend_available' => true,
				'range'              => [
					'px' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 1,
					],
				],
				'size_units'         => '',
				'condition'          => [
					'pp_custom_cursor_enable' => 'yes',
				],
			]
		);

		$element->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'pp_custom_cursor_text_typography',
				'label'     => __( 'Typography', 'powerpack' ),
				'selector'  => '{{WRAPPER}} .pp-cursor-pointer-text',
				'condition' => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_text_color',
			array(
				'label'     => __( 'Color', 'powerpack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .pp-cursor-pointer-text' => 'color: {{VALUE}}',
				),
				'condition' => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);

		$element->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'pp_custom_cursor_text_bg',
				'label'     => __( 'Background', 'powerpack' ),
				'types'     => [ 'classic', 'gradient' ],
				'exclude'   => array( 'image' ),
				'selector'  => '{{WRAPPER}} .pp-cursor-pointer-text',
				'condition' => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			]
		);

		$element->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'pp_custom_cursor_text_border',
				'label'       => __( 'Border', 'powerpack' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pp-cursor-pointer-text',
				'condition'   => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);

		$element->add_control(
			'pp_custom_cursor_text_border_radius',
			array(
				'label'      => __( 'Border Radius', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-cursor-pointer-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);

		$element->add_responsive_control(
			'pp_custom_cursor_text_padding',
			array(
				'label'      => __( 'Padding', 'powerpack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pp-cursor-pointer-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'pp_custom_cursor_enable' => 'yes',
					'pp_custom_cursor_type'   => 'follow-text',
				),
			)
		);
	}

	protected function render() {
		$settings = $element->get_settings();
	}

	/**
	 * Add Actions
	 *
	 * @since 2.7.0
	 *
	 * @access protected
	 */
	protected function add_actions() {

		// Activate controls for section
		add_action( 'elementor/element/section/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Activate controls for columns
		add_action( 'elementor/element/column/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Activate controls for widgets
		add_action( 'elementor/element/common/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Activate controls for containers
		add_action( 'elementor/element/container/section_powerpack_elements_advanced/before_section_end', function( $element, $args ) {
			$this->add_controls( $element, $args );
		}, 10, 2 );

		// Conditions for sections
		add_action( 'elementor/frontend/before_render', function( $element ) {
			$settings      = $element->get_settings_for_display();
			$cursor_url    = $settings['pp_custom_cursor_icon'];
			$cursor_text   = $settings['pp_custom_cursor_text'];
			$cursor_target = $settings['pp_custom_cursor_target'];
			$css_selector  = $settings['pp_custom_cursor_css_selector'];

			if ( 'yes' === $settings['pp_custom_cursor_enable'] ) {
				if ( ! \Elementor\Plugin::$instance->editor->is_edit_mode() || ! \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
					wp_enqueue_script( 'powerpack-frontend' );
					wp_enqueue_script( 'pp-custom-cursor' );
				}

				$custom_cursor_options = [
					'type' => $settings['pp_custom_cursor_type'],
				];

				if ( ! empty( $cursor_url ) ) {
					$custom_cursor_options['url'] = $cursor_url['url'];
				}

				if ( $cursor_text ) {
					$custom_cursor_options['text'] = $cursor_text;
				}

				if ( 'css-selector' === $cursor_target && $css_selector ) {
					$custom_cursor_options['target'] = 'selector';
					$custom_cursor_options['css_selector'] = $css_selector;
				}

				$element->add_render_attribute(
					'_wrapper', [
						'class'               => [ 'pp-custom-cursor', 'pp-custom-cursor-' . $element->get_id() ],
						'data-cursor-options' => wp_json_encode( $custom_cursor_options ),
					]
				);
			}
		}, 10, 1 );

		/* add_action( 'elementor/widget/print_template', function( $template, $widget ) {

			if ( ! $template ) {
				return;
			}

			ob_start();

			?><#

			if ( 'yes' === settings.pp_custom_cursor_enable ) {

				view.addRenderAttribute( '_wrapper', 'class', 'pp-custom-cursor' );
				view.addRenderAttribute( '_wrapper', 'id', 'hotip-content-' + view.$el.data('id') );

				#>

				<span {{{ view.getRenderAttributeString( 'tooltip' ) }}}>
					{{{ settings.tooltip_content }}}
				</span>

			<# } #><?php

			$template .= ob_get_clean();

			return $template;

		}, 10, 2 ); */
	}
}
