<?php
/**
 * Module Library: ContactField Module
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\ContactField;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\ModuleLibrary\FormFieldVariantPresetMapTrait;
use ET\Builder\Packages\Module\Options\FormField\FieldDecorationPresetAttrsMap;
use ET\Builder\Packages\Module\Options\Icon\IconPresetAttrsMap;


/**
 * Class ContactFieldPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\ContactField
 */
class ContactFieldPresetAttrsMap {
	use FormFieldVariantPresetMapTrait;

	/**
	 * Get the preset attributes map for the ContactField module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/contact-field' !== $module_name ) {
			return $map;
		}

		$keys_to_remove = [
			'module.advanced.text.text__orientation',
			'module.advanced.text.text__color',
			'module.advanced.text.textShadow__style',
			'module.advanced.text.textShadow__horizontal',
			'module.advanced.text.textShadow__vertical',
			'module.advanced.text.textShadow__blur',
			'module.advanced.text.textShadow__color',
			'module.decoration.disabledOn',
			'module.decoration.sticky__position',
			'module.decoration.sticky__offset.top',
			'module.decoration.sticky__offset.bottom',
			'module.decoration.sticky__limit.top',
			'module.decoration.sticky__limit.bottom',
			'module.decoration.sticky__offset.surrounding',
			'module.decoration.sticky__transition',
		];

		foreach ( $keys_to_remove as $key ) {
			unset( $map[ $key ] );
		}

		$field_decoration_map = FieldDecorationPresetAttrsMap::get_map();

		$merged_map = array_merge(
			$map,
			$field_decoration_map,
			[
				'field.advanced.focus.background__color'  => [
					'attrName' => 'field.advanced.focus.background',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.focus.font.font__color'   => [
					'attrName' => 'field.advanced.focus.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'fieldTitle.decoration.font.font__color'  => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'fieldTitle.decoration.font.font__family' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'family',
				],
				'fieldTitle.decoration.font.font__letterSpacing' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'fieldTitle.decoration.font.font__lineColor' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineColor',
				],
				'fieldTitle.decoration.font.font__lineHeight' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
				],
				'fieldTitle.decoration.font.font__lineStyle' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineStyle',
				],
				'fieldTitle.decoration.font.font__size'   => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'fieldTitle.decoration.font.font__style'  => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'fieldTitle.decoration.font.font__textAlign' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'textAlign',
				],
				'fieldTitle.decoration.font.font__weight' => [
					'attrName' => 'fieldTitle.decoration.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'weight',
				],
				'fieldTitle.decoration.font.textShadow__blur' => [
					'attrName' => 'fieldTitle.decoration.font.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'blur',
				],
				'fieldTitle.decoration.font.textShadow__color' => [
					'attrName' => 'fieldTitle.decoration.font.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'fieldTitle.decoration.font.textShadow__horizontal' => [
					'attrName' => 'fieldTitle.decoration.font.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'horizontal',
				],
				'fieldTitle.decoration.font.textShadow__style' => [
					'attrName' => 'fieldTitle.decoration.font.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'style',
				],
				'fieldTitle.decoration.font.textShadow__vertical' => [
					'attrName' => 'fieldTitle.decoration.font.textShadow',
					'preset'   => [ 'style' ],
					'subName'  => 'vertical',
				],
				'module.advanced.text__orientation'       => [
					'attrName' => 'module.advanced.text',
					'preset'   => [ 'html' ],
					'subName'  => 'orientation',
				],
				'module.advanced.html__elementType'       => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'elementType',
				],
				'module.advanced.html__htmlAfter'         => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'htmlAfter',
				],
				'module.advanced.html__htmlBefore'        => [
					'attrName' => 'module.advanced.html',
					'preset'   => [ 'html' ],
					'subName'  => 'htmlBefore',
				],
			]
		);

		$checkbox_map = self::_duplicate_map_entries_by_prefix(
			$merged_map,
			'field.',
			'checkbox.'
		);
		$checkbox_map = self::_filter_form_field_variant_map(
			$checkbox_map,
			'checkbox.'
		);
		$radio_map    = self::_duplicate_map_entries_by_prefix(
			$merged_map,
			'field.',
			'radio.'
		);
		$radio_map    = self::_filter_form_field_variant_map(
			$radio_map,
			'radio.'
		);

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
			$checkbox_icon_map,
			$radio_map,
			$radio_icon_root_map,
			$radio_icon_map
		);
	}
}
