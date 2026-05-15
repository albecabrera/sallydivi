<?php
/**
 * Module Library: Signup Module Preset Attributes Map
 *
 * @package Divi
 * @since   ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Signup;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleLibrary\FormFieldVariantPresetMapTrait;
use ET\Builder\Packages\Module\Options\FormField\FieldDecorationPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Icon\IconPresetAttrsMap;


/**
 * Class SignupPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Signup
 */
class SignupPresetAttrsMap {
	use FormFieldVariantPresetMapTrait;


	/**
	 * Get the preset attributes map for the Signup module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/signup' !== $module_name ) {
			return $map;
		}

		unset( $map['button.decoration.button.innerContent__text'] );
		unset( $map['button.decoration.button.innerContent__linkUrl'] );
		unset( $map['button.decoration.button.innerContent__linkTarget'] );
		unset( $map['button.decoration.button.innerContent__rel'] );
		unset( $map['button.decoration.button.decoration.button__icon.enable'] );
		unset( $map['button.decoration.button.decoration.button__icon.settings'] );
		unset( $map['button.decoration.button.decoration.button__icon.color'] );
		unset( $map['button.decoration.button.decoration.button__icon.placement'] );
		unset( $map['button.decoration.button.decoration.button__icon.onHover'] );
		unset( $map['button.decoration.button.decoration.button__alignment'] );
		unset( $map['button.decoration.button.decoration.background__color'] );
		unset( $map['button.decoration.button.decoration.background__gradient.stops'] );
		unset( $map['button.decoration.button.decoration.background__gradient.enabled'] );
		unset( $map['button.decoration.button.decoration.background__gradient.type'] );
		unset( $map['button.decoration.button.decoration.background__gradient.direction'] );
		unset( $map['button.decoration.button.decoration.background__gradient.directionRadial'] );
		unset( $map['button.decoration.button.decoration.background__gradient.repeat'] );
		unset( $map['button.decoration.button.decoration.background__gradient.length'] );
		unset( $map['button.decoration.button.decoration.background__gradient.overlaysImage'] );
		unset( $map['button.decoration.button.decoration.background__image.url'] );
		unset( $map['button.decoration.button.decoration.background__image.parallax.enabled'] );
		unset( $map['button.decoration.button.decoration.background__image.parallax.method'] );
		unset( $map['button.decoration.button.decoration.background__image.size'] );
		unset( $map['button.decoration.button.decoration.background__image.width'] );
		unset( $map['button.decoration.button.decoration.background__image.height'] );
		unset( $map['button.decoration.button.decoration.background__image.position'] );
		unset( $map['button.decoration.button.decoration.background__image.horizontalOffset'] );
		unset( $map['button.decoration.button.decoration.background__image.verticalOffset'] );
		unset( $map['button.decoration.button.decoration.background__image.repeat'] );
		unset( $map['button.decoration.button.decoration.background__image.blend'] );
		unset( $map['button.decoration.button.decoration.background__video.mp4'] );
		unset( $map['button.decoration.button.decoration.background__video.webm'] );
		unset( $map['button.decoration.button.decoration.background__video.width'] );
		unset( $map['button.decoration.button.decoration.background__video.height'] );
		unset( $map['button.decoration.button.decoration.background__video.allowPlayerPause'] );
		unset( $map['button.decoration.button.decoration.background__video.pauseOutsideViewport'] );
		unset( $map['button.decoration.button.decoration.background__pattern.style'] );
		unset( $map['button.decoration.button.decoration.background__pattern.enabled'] );
		unset( $map['button.decoration.button.decoration.background__pattern.color'] );
		unset( $map['button.decoration.button.decoration.background__pattern.transform'] );
		unset( $map['button.decoration.button.decoration.background__pattern.size'] );
		unset( $map['button.decoration.button.decoration.background__pattern.width'] );
		unset( $map['button.decoration.button.decoration.background__pattern.height'] );
		unset( $map['button.decoration.button.decoration.background__pattern.repeatOrigin'] );
		unset( $map['button.decoration.button.decoration.background__pattern.horizontalOffset'] );
		unset( $map['button.decoration.button.decoration.background__pattern.verticalOffset'] );
		unset( $map['button.decoration.button.decoration.background__pattern.repeat'] );
		unset( $map['button.decoration.button.decoration.background__pattern.blend'] );
		unset( $map['button.decoration.button.decoration.background__mask.style'] );
		unset( $map['button.decoration.button.decoration.background__mask.enabled'] );
		unset( $map['button.decoration.button.decoration.background__mask.color'] );
		unset( $map['button.decoration.button.decoration.background__mask.transform'] );
		unset( $map['button.decoration.button.decoration.background__mask.aspectRatio'] );
		unset( $map['button.decoration.button.decoration.background__mask.size'] );
		unset( $map['button.decoration.button.decoration.background__mask.width'] );
		unset( $map['button.decoration.button.decoration.background__mask.height'] );
		unset( $map['button.decoration.button.decoration.background__mask.position'] );
		unset( $map['button.decoration.button.decoration.background__mask.horizontalOffset'] );
		unset( $map['button.decoration.button.decoration.background__mask.verticalOffset'] );
		unset( $map['button.decoration.button.decoration.background__mask.blend'] );
		unset( $map['button.decoration.button.decoration.border__radius'] );
		unset( $map['button.decoration.button.decoration.border__styles'] );
		unset( $map['button.decoration.button.decoration.border__styles.all.width'] );
		unset( $map['button.decoration.button.decoration.border__styles.top.width'] );
		unset( $map['button.decoration.button.decoration.border__styles.right.width'] );
		unset( $map['button.decoration.button.decoration.border__styles.bottom.width'] );
		unset( $map['button.decoration.button.decoration.border__styles.left.width'] );
		unset( $map['button.decoration.button.decoration.border__styles.all.color'] );
		unset( $map['button.decoration.button.decoration.border__styles.top.color'] );
		unset( $map['button.decoration.button.decoration.border__styles.right.color'] );
		unset( $map['button.decoration.button.decoration.border__styles.bottom.color'] );
		unset( $map['button.decoration.button.decoration.border__styles.left.color'] );
		unset( $map['button.decoration.button.decoration.border__styles.all.style'] );
		unset( $map['button.decoration.button.decoration.border__styles.top.style'] );
		unset( $map['button.decoration.button.decoration.border__styles.right.style'] );
		unset( $map['button.decoration.button.decoration.border__styles.bottom.style'] );
		unset( $map['button.decoration.button.decoration.border__styles.left.style'] );
		unset( $map['button.decoration.button.decoration.spacing__margin'] );
		unset( $map['button.decoration.button.decoration.spacing__padding'] );
		unset( $map['button.decoration.button.decoration.boxShadow__style'] );
		unset( $map['button.decoration.button.decoration.boxShadow__horizontal'] );
		unset( $map['button.decoration.button.decoration.boxShadow__vertical'] );
		unset( $map['button.decoration.button.decoration.boxShadow__blur'] );
		unset( $map['button.decoration.button.decoration.boxShadow__spread'] );
		unset( $map['button.decoration.button.decoration.boxShadow__color'] );
		unset( $map['button.decoration.button.decoration.boxShadow__position'] );
		unset( $map['button.decoration.button.decoration.font.font__family'] );
		unset( $map['button.decoration.button.decoration.font.font__weight'] );
		unset( $map['button.decoration.button.decoration.font.font__style'] );
		unset( $map['button.decoration.button.decoration.font.font__lineColor'] );
		unset( $map['button.decoration.button.decoration.font.font__lineStyle'] );
		unset( $map['button.decoration.button.decoration.font.font__textAlign'] );
		unset( $map['button.decoration.button.decoration.font.font__color'] );
		unset( $map['button.decoration.button.decoration.font.font__size'] );
		unset( $map['button.decoration.button.decoration.font.font__letterSpacing'] );
		unset( $map['button.decoration.button.decoration.font.font__lineHeight'] );
		unset( $map['button.decoration.button.decoration.font.textShadow__style'] );
		unset( $map['button.decoration.button.decoration.font.textShadow__horizontal'] );
		unset( $map['button.decoration.button.decoration.font.textShadow__vertical'] );
		unset( $map['button.decoration.button.decoration.font.textShadow__blur'] );
		unset( $map['button.decoration.button.decoration.font.textShadow__color'] );
		unset( $map['button.decoration.button.decoration.sizing__width'] );
		unset( $map['button.decoration.button.decoration.sizing__maxWidth'] );
		unset( $map['button.decoration.button.decoration.sizing__alignSelf'] );
		unset( $map['button.decoration.button.decoration.sizing__alignment'] );
		unset( $map['button.decoration.button.decoration.sizing__flexGrow'] );
		unset( $map['button.decoration.button.decoration.sizing__flexShrink'] );
		unset( $map['button.decoration.button.decoration.sizing__gridAlignSelf'] );
		unset( $map['button.decoration.button.decoration.sizing__gridColumnSpan'] );
		unset( $map['button.decoration.button.decoration.sizing__gridColumnStart'] );
		unset( $map['button.decoration.button.decoration.sizing__gridJustifySelf'] );
		unset( $map['button.decoration.button.decoration.sizing__gridRowSpan'] );
		unset( $map['button.decoration.button.decoration.sizing__gridRowStart'] );
		unset( $map['button.decoration.button.decoration.sizing__gridColumnEnd'] );
		unset( $map['button.decoration.button.decoration.sizing__gridRowEnd'] );
		unset( $map['button.decoration.button.decoration.sizing__minHeight'] );
		unset( $map['button.decoration.button.decoration.sizing__size'] );
		unset( $map['button.decoration.button.decoration.sizing__height'] );
		unset( $map['button.decoration.button.decoration.sizing__maxHeight'] );
		unset( $map['button.decoration.button.decoration.sizing__flexType'] );
		unset( $map['button.decoration.font.font__lineHeight'] );
		unset( $map['customFields.advanced.fields'] );
		unset( $map['customFields.advanced.notice'] );
		unset( $map['field.advanced.focus.font.font__size'] );
		unset( $map['field.advanced.focus.font.font__letterSpacing'] );
		unset( $map['field.advanced.focus.font.font__lineHeight'] );
		$field_decoration_map = FieldDecorationPresetAttrsMap::get_map();

		$merged_map = array_merge(
			$map,
			$field_decoration_map,
			[
				'title.innerContent'                       => [
					'attrName' => 'title.innerContent',
					'preset'   => 'content',
				],
				'button.innerContent__text'                => [
					'attrName' => 'button.innerContent',
					'preset'   => 'content',
					'subName'  => 'text',
				],
				'module.advanced.emailService__provider'   => [
					'attrName' => 'module.advanced.emailService',
					'preset'   => 'content',
					'subName'  => 'provider',
				],
				'module.advanced.emailService__account'    => [
					'attrName' => 'module.advanced.emailService',
					'preset'   => 'content',
					'subName'  => 'account',
				],
				'module.advanced.spamProtection__enabled'  => [
					'attrName' => 'module.advanced.spamProtection',
					'preset'   => 'content',
					'subName'  => 'enabled',
				],
				'module.advanced.spamProtection__provider' => [
					'attrName' => 'module.advanced.spamProtection',
					'preset'   => 'content',
					'subName'  => 'provider',
				],
				'module.advanced.spamProtection__account'  => [
					'attrName' => 'module.advanced.spamProtection',
					'preset'   => 'content',
					'subName'  => 'account',
				],
				'module.advanced.spamProtection__minScore' => [
					'attrName' => 'module.advanced.spamProtection',
					'preset'   => 'content',
					'subName'  => 'minScore',
				],
				'field.advanced.focus.background__color'   => [
					'attrName' => 'field.advanced.focus.background',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.focus.font.font__color'    => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'button.decoration.button__icon.enable'    => [
					'attrName' => 'button.decoration.button',
					'preset'   => [ 'style' ],
					'subName'  => 'icon.enable',
				],
				'button.decoration.button__icon.settings'  => [
					'attrName' => 'button.decoration.button',
					'preset'   => [ 'html', 'style' ],
					'subName'  => 'icon.settings',
				],
				'button.decoration.button__icon.color'     => [
					'attrName' => 'button.decoration.button',
					'preset'   => [ 'style' ],
					'subName'  => 'icon.color',
				],
				'button.decoration.button__icon.placement' => [
					'attrName' => 'button.decoration.button',
					'preset'   => [ 'style' ],
					'subName'  => 'icon.placement',
				],
				'button.innerContent__rel'                 => [
					'attrName' => 'button.innerContent',
					'preset'   => [ 'html' ],
					'subName'  => 'rel',
				],
				'button.decoration.button__icon.onHover'   => [
					'attrName' => 'button.decoration.button',
					'preset'   => [ 'style' ],
					'subName'  => 'icon.onHover',
				],
				'button.decoration.sizing__width'          => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'width',
				],
				'button.decoration.sizing__maxWidth'       => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'maxWidth',
				],
				'button.decoration.sizing__alignSelf'      => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'alignSelf',
				],
				'button.decoration.sizing__alignment'      => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'alignment',
				],
				'button.decoration.sizing__flexGrow'       => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'flexGrow',
				],
				'button.decoration.sizing__flexShrink'     => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'flexShrink',
				],
				'button.decoration.sizing__gridAlignSelf'  => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridAlignSelf',
				],
				'button.decoration.sizing__gridColumnSpan' => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridColumnSpan',
				],
				'button.decoration.sizing__gridColumnStart' => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridColumnStart',
				],
				'button.decoration.sizing__gridJustifySelf' => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridJustifySelf',
				],
				'button.decoration.sizing__gridRowSpan'    => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridRowSpan',
				],
				'button.decoration.sizing__gridRowStart'   => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridRowStart',
				],
				'button.decoration.sizing__gridColumnEnd'  => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridColumnEnd',
				],
				'button.decoration.sizing__gridRowEnd'     => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'gridRowEnd',
				],
				'button.decoration.sizing__minHeight'      => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'minHeight',
				],
				'button.decoration.sizing__size'           => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'button.decoration.sizing__height'         => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'height',
				],
				'button.decoration.sizing__maxHeight'      => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'maxHeight',
				],
				'button.decoration.sizing__flexType'       => [
					'attrName' => 'button.decoration.sizing',
					'preset'   => [ 'html' ],
					'subName'  => 'flexType',
				],
				'field.advanced.focus.border__radius'      => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'radius',
				],
				'field.advanced.focus.border__styles'      => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles',
				],
				'field.advanced.focus.border__styles.all.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.width',
				],
				'field.advanced.focus.border__styles.top.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.width',
				],
				'field.advanced.focus.border__styles.right.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.width',
				],
				'field.advanced.focus.border__styles.bottom.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.width',
				],
				'field.advanced.focus.border__styles.left.width' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.width',
				],
				'field.advanced.focus.border__styles.all.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.color',
				],
				'field.advanced.focus.border__styles.top.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.color',
				],
				'field.advanced.focus.border__styles.right.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.color',
				],
				'field.advanced.focus.border__styles.bottom.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.color',
				],
				'field.advanced.focus.border__styles.left.color' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.color',
				],
				'field.advanced.focus.border__styles.all.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.all.style',
				],
				'field.advanced.focus.border__styles.top.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.top.style',
				],
				'field.advanced.focus.border__styles.right.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.right.style',
				],
				'field.advanced.focus.border__styles.bottom.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.bottom.style',
				],
				'field.advanced.focus.border__styles.left.style' => [
					'attrName' => 'field.advanced.focus.border',
					'preset'   => [ 'style' ],
					'subName'  => 'styles.left.style',
				],
				'module.advanced.html__elementType'        => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'elementType',
				],
				'module.advanced.html__htmlAfter'          => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'htmlAfter',
				],
				'module.advanced.html__htmlBefore'         => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'htmlBefore',
				],
			]
		);

		$checkbox_map = self::_duplicate_map_entries_by_prefix( $merged_map, 'field.', 'checkbox.' );
		$checkbox_map = self::_filter_form_field_variant_map( $checkbox_map, 'checkbox.' );
		$radio_map    = self::_duplicate_map_entries_by_prefix( $merged_map, 'field.', 'radio.' );
		$radio_map    = self::_filter_form_field_variant_map( $radio_map, 'radio.' );

		$checkbox_icon_map      = IconPresetAttrsMap::get_map( 'checkbox.decoration.icon' );
		$radio_icon_map         = IconPresetAttrsMap::get_map( 'radio.decoration.icon' );
		$checkbox_icon_root_map = [
			'checkbox.decoration.icon' => [
				'attrName' => 'checkbox.decoration.icon',
				'preset'   => [ 'style', 'html' ],
			],
		];
		$radio_icon_root_map    = [
			'radio.decoration.icon' => [
				'attrName' => 'radio.decoration.icon',
				'preset'   => [ 'style', 'html' ],
			],
		];

		return array_merge(
			$merged_map,
			$checkbox_map,
			$checkbox_icon_root_map,
			$radio_map,
			$radio_icon_root_map,
			$checkbox_icon_map,
			$radio_icon_map
		);
	}
}
