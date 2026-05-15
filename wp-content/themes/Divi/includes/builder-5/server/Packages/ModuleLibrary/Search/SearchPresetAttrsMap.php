<?php
/**
 * Module Library:Search Module Preset Attributes Map
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Search;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}


/**
 * Class SearchPresetAttrsMap
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Search
 */
class SearchPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Search module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/search' !== $module_name ) {
			return $map;
		}

		unset( $map['field.decoration.spacing__margin'] );
		unset( $map['field.decoration.spacing__padding'] );

		unset( $map['button.decoration.font.font__textAlign'] );
		unset( $map['title.decoration.font.font__headingLevel'] );

		return array_merge(
			$map,
			[
				'searchPlaceholder.innerContent'           => [
					'attrName' => 'searchPlaceholder.innerContent',
					'preset'   => 'content',
				],
				'field.advanced.placeholder.font.font__color' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'color',
				],
				'field.advanced.placeholder.font.font__size' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'size',
				],
				'field.advanced.placeholder.font.font__letterSpacing' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'letterSpacing',
				],
				'field.advanced.placeholder.font.font__lineHeight' => [
					'attrName' => 'field.advanced.placeholder.font.font',
					'preset'   => [ 'style' ],
					'subName'  => 'lineHeight',
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
				'field.decoration.font.font__headingLevel' => [
					'attrName' => 'field.decoration.font.font',
					'preset'   => [ 'html' ],
					'subName'  => 'headingLevel',
				],
			]
		);
	}
}
