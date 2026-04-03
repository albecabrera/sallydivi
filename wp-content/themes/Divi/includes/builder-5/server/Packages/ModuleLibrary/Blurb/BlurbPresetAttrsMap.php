<?php
/**
 * Module Library: Blurb Module Preset Attributes Map.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\ModuleLibrary\Blurb;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

/**
 * Class BlurbPresetAttrsMap.
 *
 * @since ??
 *
 * @package ET\Builder\Packages\ModuleLibrary\Blurb
 */
class BlurbPresetAttrsMap {
	/**
	 * Get the preset attributes map for the Blurb module.
	 *
	 * @since ??
	 *
	 * @param array  $map         The preset attributes map.
	 * @param string $module_name The module name.
	 *
	 * @return array
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( 'divi/blurb' !== $module_name ) {
			return $map;
		}

		return array_merge(
			$map,
			[
				'imageIcon.decoration.sizing__iconFontSize' => [
					'attrName' => 'imageIcon.decoration.sizing',
					'preset'   => [ 'style' ],
					'subName'  => 'iconFontSize',
				],
			]
		);
	}
}
