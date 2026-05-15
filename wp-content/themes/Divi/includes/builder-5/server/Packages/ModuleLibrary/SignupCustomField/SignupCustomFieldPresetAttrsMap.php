<?php
/**
 * Module Library: Signup Custom Field Module Preset Attributes Map
 *
 * @package Divi
 * @since   ??
 */

namespace ET\Builder\Packages\ModuleLibrary\SignupCustomField;

use ET\Builder\Packages\ModuleLibrary\FormFieldVariantPresetMapTrait;
use ET\Builder\Packages\Module\Options\Icon\IconPresetAttrsMap;
use ET\Builder\Packages\Module\Options\FormField\FormFieldPresetAttrsMap;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class SignupCustomFieldPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\SignupCustomField
 */
class SignupCustomFieldPresetAttrsMap {
	use FormFieldVariantPresetMapTrait;


	/**
	 * Get the preset attributes map for the Signup Custom Field module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/signup-custom-field' !== $module_name ) {
			return $map;
		}

		unset( $map['module.advanced.text.text__color'] );
		unset( $map['module.advanced.text.textShadow__style'] );
		unset( $map['module.advanced.text.textShadow__horizontal'] );
		unset( $map['module.advanced.text.textShadow__vertical'] );
		unset( $map['module.advanced.text.textShadow__blur'] );
		unset( $map['module.advanced.text.textShadow__color'] );
		unset( $map['module.decoration.disabledOn'] );
		unset( $map['fieldItem.advanced.predefinedField'] );
		unset( $map['fieldItem.advanced.hidden'] );

		$merged_map     = array_merge(
			$map,
			FormFieldPresetAttrsMap::get_map( 'field' ),
			[
				'fieldItem.advanced.hidden'                => [
					'attrName' => 'fieldItem.advanced.hidden',
					'preset'   => [ 'html' ],
				],
				'field.decoration.font.font__headingLevel' => [
					'attrName' => 'field.decoration.font.font',
					'preset'   => [ 'html' ],
					'subName'  => 'headingLevel',
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
				'field.decoration.labelFont.font__family'  => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'family',
				],
				'field.decoration.labelFont.font__weight'  => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'weight',
				],
				'field.decoration.labelFont.font__style'   => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'field.decoration.labelFont.font__lineColor' => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineColor',
				],
				'field.decoration.labelFont.font__lineStyle' => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineStyle',
				],
				'field.decoration.labelFont.font__textAlign' => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'textAlign',
				],
				'field.decoration.labelFont.font__color'   => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.decoration.labelFont.font__size'    => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'field.decoration.labelFont.font__letterSpacing' => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'field.decoration.labelFont.font__lineHeight' => [
					'attrName' => 'field.decoration.labelFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
				],
				'field.decoration.labelFont.textShadow__style' => [
					'attrName' => 'field.decoration.labelFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'field.decoration.labelFont.textShadow__horizontal' => [
					'attrName' => 'field.decoration.labelFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'horizontal',
				],
				'field.decoration.labelFont.textShadow__vertical' => [
					'attrName' => 'field.decoration.labelFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'vertical',
				],
				'field.decoration.labelFont.textShadow__blur' => [
					'attrName' => 'field.decoration.labelFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'blur',
				],
				'field.decoration.labelFont.textShadow__color' => [
					'attrName' => 'field.decoration.labelFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.decoration.placeholderFont.font__family' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'family',
				],
				'field.decoration.placeholderFont.font__weight' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'weight',
				],
				'field.decoration.placeholderFont.font__style' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'field.decoration.placeholderFont.font__lineColor' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineColor',
				],
				'field.decoration.placeholderFont.font__lineStyle' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineStyle',
				],
				'field.decoration.placeholderFont.font__textAlign' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'textAlign',
				],
				'field.decoration.placeholderFont.font__color' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.decoration.placeholderFont.font__size' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'field.decoration.placeholderFont.font__letterSpacing' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'field.decoration.placeholderFont.font__lineHeight' => [
					'attrName' => 'field.decoration.placeholderFont.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
				],
				'field.decoration.placeholderFont.textShadow__style' => [
					'attrName' => 'field.decoration.placeholderFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'field.decoration.placeholderFont.textShadow__horizontal' => [
					'attrName' => 'field.decoration.placeholderFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'horizontal',
				],
				'field.decoration.placeholderFont.textShadow__vertical' => [
					'attrName' => 'field.decoration.placeholderFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'vertical',
				],
				'field.decoration.placeholderFont.textShadow__blur' => [
					'attrName' => 'field.decoration.placeholderFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'blur',
				],
				'field.decoration.placeholderFont.textShadow__color' => [
					'attrName' => 'field.decoration.placeholderFont.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
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
		$keys_to_remove = [
			'field.advanced.placeholder.font.font__family',
			'field.advanced.placeholder.font.font__weight',
			'field.advanced.placeholder.font.font__style',
			'field.advanced.placeholder.font.font__lineColor',
			'field.advanced.placeholder.font.font__lineStyle',
			'field.advanced.placeholder.font.font__textAlign',
			'field.advanced.placeholder.font.textShadow__style',
			'field.advanced.placeholder.font.textShadow__horizontal',
			'field.advanced.placeholder.font.textShadow__vertical',
			'field.advanced.placeholder.font.textShadow__blur',
			'field.advanced.placeholder.font.textShadow__color',
			'field.advanced.focus.font.font__family',
			'field.advanced.focus.font.font__weight',
			'field.advanced.focus.font.font__style',
			'field.advanced.focus.font.font__lineColor',
			'field.advanced.focus.font.font__lineStyle',
			'field.advanced.focus.font.font__textAlign',
			'field.advanced.focus.font.font__size',
			'field.advanced.focus.font.font__letterSpacing',
			'field.advanced.focus.font.font__lineHeight',
			'field.advanced.focus.font.textShadow__style',
			'field.advanced.focus.font.textShadow__horizontal',
			'field.advanced.focus.font.textShadow__vertical',
			'field.advanced.focus.font.textShadow__blur',
			'field.advanced.focus.font.textShadow__color',
		];

		foreach ( $keys_to_remove as $key ) {
			unset( $merged_map[ $key ] );
		}

		$checkbox_map = self::_duplicate_map_entries_by_prefix( $merged_map, 'field.', 'checkbox.' );
		$checkbox_map = self::_filter_form_field_variant_map( $checkbox_map, 'checkbox.', true );
		$radio_map    = self::_duplicate_map_entries_by_prefix( $merged_map, 'field.', 'radio.' );
		$radio_map    = self::_filter_form_field_variant_map( $radio_map, 'radio.', true );

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
