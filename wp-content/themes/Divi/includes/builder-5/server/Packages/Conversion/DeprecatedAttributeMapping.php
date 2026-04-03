<?php
/**
 * Deprecated Attribute Mapping for Preset Conversion.
 *
 * This class handles attribute mappings for deprecated settings that have been
 * removed from module UIs but still need to be included in preset conversion
 * for backwards compatibility.
 *
 * @package Divi
 * @since ??
 */

namespace ET\Builder\Packages\Conversion;

/**
 * Class DeprecatedAttributeMapping
 *
 * Manages deprecated attribute mappings for modules that need them for preset conversion.
 */
class DeprecatedAttributeMapping {

	/**
	 * Get deprecated attribute mappings for a specific module.
	 *
	 * @since ??
	 *
	 * @param string $module_name The module name (e.g., 'divi/sidebar').
	 *
	 * @return array Array of deprecated attribute mappings for the module.
	 */
	public static function get_deprecated_attrs_for_module( $module_name ) {
		$module_attribute_types = self::_get_module_attribute_types();
		$attribute_definitions  = self::_get_attribute_definitions();

		$deprecated_attrs = [];

		// Get the deprecated attribute types for this module.
		$attribute_types = $module_attribute_types[ $module_name ] ?? [];

		// Build the deprecated attributes array from the attribute type definitions.
		foreach ( $attribute_types as $attribute_type ) {
			if ( isset( $attribute_definitions[ $attribute_type ] ) ) {
				$deprecated_attrs = array_merge( $deprecated_attrs, $attribute_definitions[ $attribute_type ] );
			}
		}

		return $deprecated_attrs;
	}

	/**
	 * Check if a module has deprecated attributes.
	 *
	 * @since ??
	 *
	 * @param string $module_name The module name (e.g., 'divi/sidebar').
	 *
	 * @return bool True if the module has deprecated attributes, false otherwise.
	 */
	public static function has_deprecated_attrs( $module_name ) {
		$module_attribute_types = self::_get_module_attribute_types();

		return isset( $module_attribute_types[ $module_name ] );
	}

	/**
	 * Get mapping of modules to their deprecated attribute types.
	 *
	 * @since ??
	 *
	 * @return array Array mapping module names to their deprecated attribute types.
	 */
	private static function _get_module_attribute_types() {
		return [
			'divi/accordion'                           => [ 'htmlAttributes' ],
			'divi/audio'                               => [ 'htmlAttributes' ],
			'divi/blog'                                => [ 'htmlAttributes', 'blogLayout', 'blogGridFlexType' ],
			'divi/blurb'                               => [ 'htmlAttributes', 'imageIconAlt', 'imageIconAnimation', 'imageIconWidth', 'imageIconAlignment', 'imageIconBackground' ],
			'divi/button'                              => [ 'htmlAttributes', 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/circle-counter'                      => [ 'htmlAttributes' ],
			'divi/code'                                => [ 'htmlAttributes' ],
			'divi/column'                              => [ 'htmlAttributes' ],
			'divi/column-inner'                        => [ 'htmlAttributes' ],
			'divi/comments'                            => [ 'htmlAttributes', 'buttonEnable', 'buttonAlignment' ],
			'divi/contact-field'                       => [ 'htmlAttributes', 'contactFieldFullwidth' ],
			'divi/contact-form'                        => [ 'htmlAttributes', 'buttonEnable', 'buttonAlignment' ],
			'divi/countdown-timer'                     => [ 'htmlAttributes' ],
			'divi/counters'                            => [ 'htmlAttributes' ],
			'divi/cta'                                 => [ 'htmlAttributes', 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/divider'                             => [ 'htmlAttributes' ],
			'divi/filterable-portfolio'                => [ 'htmlAttributes', 'portfolioLayout', 'portfolioGridFlexType' ],
			'divi/fullwidth-code'                      => [ 'htmlAttributes' ],
			'divi/fullwidth-header'                    => [ 'htmlAttributes', 'logoAlt', 'imageAlt', 'buttonOneRel', 'buttonTwoRel', 'buttonOneEnable', 'buttonTwoEnable', 'imageTitle', 'logoTitle' ],
			'divi/fullwidth-image'                     => [ 'htmlAttributes', 'imageAlt', 'imageTitleText' ],
			'divi/fullwidth-map'                       => [ 'htmlAttributes' ],
			'divi/fullwidth-menu'                      => [ 'htmlAttributes', 'logoAlt' ],
			'divi/fullwidth-portfolio'                 => [ 'htmlAttributes', 'portfolioGridFlexType' ],
			'divi/fullwidth-post-content'              => [ 'htmlAttributes' ],
			'divi/fullwidth-post-slider'               => [ 'htmlAttributes', 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/fullwidth-post-title'                => [ 'htmlAttributes' ],
			'divi/fullwidth-slider'                    => [ 'htmlAttributes', 'childrenButtonRel', 'childrenButtonEnable' ],
			'divi/gallery'                             => [ 'htmlAttributes', 'galleryGridFlexType' ],
			'divi/group'                               => [ 'htmlAttributes' ],
			'divi/group-carousel'                      => [ 'htmlAttributes' ],
			'divi/heading'                             => [ 'htmlAttributes' ],
			'divi/icon'                                => [ 'htmlAttributes', 'iconTitle' ],
			'divi/icon-list'                           => [ 'htmlAttributes' ],
			'divi/icon-list-item'                      => [ 'htmlAttributes', 'iconTitle' ],
			'divi/image'                               => [ 'htmlAttributes', 'imageAlt', 'imageTitleText', 'imageRel' ],
			'divi/login'                               => [ 'htmlAttributes', 'buttonEnable', 'buttonAlignment' ],
			'divi/lottie'                              => [ 'htmlAttributes' ],
			'divi/map'                                 => [ 'htmlAttributes' ],
			'divi/menu'                                => [ 'htmlAttributes', 'logoAlt' ],
			'divi/number-counter'                      => [ 'htmlAttributes' ],
			'divi/portfolio'                           => [ 'htmlAttributes', 'portfolioLayout', 'portfolioGridFlexType' ],
			'divi/post-content'                        => [ 'htmlAttributes' ],
			'divi/post-nav'                            => [ 'htmlAttributes' ],
			'divi/post-slider'                         => [ 'htmlAttributes', 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/post-title'                          => [ 'htmlAttributes' ],
			'divi/pricing-table'                       => [ 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/pricing-tables'                      => [ 'htmlAttributes', 'childrenButtonRel', 'childrenButtonEnable' ],
			'divi/pricing-tables-item'                 => [ 'htmlAttributes', 'buttonEnable', 'buttonAlignment' ],
			'divi/row'                                 => [ 'htmlAttributes' ],
			'divi/row-inner'                           => [ 'htmlAttributes' ],
			'divi/search'                              => [ 'htmlAttributes' ],
			'divi/section'                             => [ 'htmlAttributes' ],
			'divi/shop'                                => [ 'htmlAttributes' ],
			'divi/sidebar'                             => [ 'htmlAttributes' ],
			'divi/signup'                              => [ 'htmlAttributes', 'buttonRel', 'buttonEnable', 'buttonAlignment', 'formLayout' ],
			'divi/slide'                               => [ 'imageAlt', 'buttonRel', 'buttonEnable', 'buttonAlignment' ],
			'divi/slider'                              => [ 'htmlAttributes', 'childrenButtonRel', 'childrenButtonEnable' ],
			'divi/social-media-follow'                 => [ 'htmlAttributes', 'buttonEnable', 'buttonAlignment' ],
			'divi/tabs'                                => [ 'htmlAttributes', 'inactiveTabBackground' ],
			'divi/team-member'                         => [ 'htmlAttributes' ],
			'divi/testimonial'                         => [ 'htmlAttributes' ],
			'divi/text'                                => [ 'htmlAttributes' ],
			'divi/toggle'                              => [ 'htmlAttributes' ],
			'divi/video'                               => [ 'htmlAttributes' ],
			'divi/video-slider'                        => [ 'htmlAttributes' ],
			'divi/woocommerce-breadcrumb'              => [ 'htmlAttributes' ],
			'divi/woocommerce-cart-notice'             => [ 'htmlAttributes' ],
			'divi/woocommerce-product-add-to-cart'     => [ 'htmlAttributes' ],
			'divi/woocommerce-product-additional-info' => [ 'htmlAttributes' ],
			'divi/woocommerce-product-description'     => [ 'htmlAttributes' ],
			'divi/woocommerce-product-gallery'         => [ 'htmlAttributes' ],
			'divi/woocommerce-product-images'          => [ 'htmlAttributes' ],
			'divi/woocommerce-product-meta'            => [ 'htmlAttributes' ],
			'divi/woocommerce-product-price'           => [ 'htmlAttributes' ],
			'divi/woocommerce-product-rating'          => [ 'htmlAttributes' ],
			'divi/woocommerce-product-reviews'         => [ 'htmlAttributes' ],
			'divi/woocommerce-product-stock'           => [ 'htmlAttributes' ],
			'divi/woocommerce-product-tabs'            => [ 'htmlAttributes' ],
			'divi/woocommerce-product-title'           => [ 'htmlAttributes' ],
			'divi/woocommerce-product-upsell'          => [ 'htmlAttributes' ],
			'divi/woocommerce-related-products'        => [ 'htmlAttributes' ],
		];
	}

	/**
	 * Get attribute definitions organized by attribute type.
	 *
	 * @since ??
	 *
	 * @return array Array of attribute definitions organized by attribute type.
	 */
	private static function _get_attribute_definitions() {
		return [
			'htmlAttributes'        => self::_get_html_attributes_definition(),
			'imageIconAlt'          => self::_get_image_icon_alt_definition(),
			'imageAlt'              => self::_get_image_alt_definition(),
			'imageTitleText'        => self::_get_image_title_text_definition(),
			'logoAlt'               => self::_get_logo_alt_definition(),
			'buttonRel'             => self::_get_button_rel_definition(),
			'buttonEnable'          => self::_get_button_enable_definition(),
			'buttonAlignment'       => self::_get_button_alignment_definition(),
			'iconTitle'             => self::_get_icon_title_definition(),
			'buttonOneRel'          => self::_get_button_one_rel_definition(),
			'buttonOneEnable'       => self::_get_button_one_enable_definition(),
			'buttonTwoRel'          => self::_get_button_two_rel_definition(),
			'buttonTwoEnable'       => self::_get_button_two_enable_definition(),
			'imageTitle'            => self::_get_image_title_definition(),
			'logoTitle'             => self::_get_logo_title_definition(),
			'childrenButtonRel'     => self::_get_children_button_rel_definition(),
			'childrenButtonEnable'  => self::_get_children_button_enable_definition(),
			'imageRel'              => self::_get_image_rel_definition(),
			'imageIconAnimation'    => self::_get_image_icon_animation_definition(),
			'imageIconWidth'        => self::_get_image_icon_width_definition(),
			'imageIconAlignment'    => self::_get_image_icon_alignment_definition(),
			'imageIconBackground'   => self::_get_image_icon_background_definition(),
			'inactiveTabBackground' => self::_get_inactive_tab_background_definition(),
			'formLayout'            => self::_get_form_layout_definition(),
			'portfolioLayout'       => self::_get_portfolio_layout_definition(),
			'blogLayout'            => self::_get_blog_layout_definition(),
			'portfolioGridFlexType' => self::_get_portfolio_grid_flex_type_definition(),
			'blogGridFlexType'      => self::_get_blog_grid_flex_type_definition(),
			'galleryGridFlexType'   => self::_get_gallery_grid_flex_type_definition(),
			'contactFieldFullwidth' => self::_get_contact_field_fullwidth_definition(),
		];
	}

	/**
	 * Get htmlAttributes deprecated attribute definition.
	 *
	 * Many modules have deprecated htmlAttributes from the UI but still
	 * need them for preset conversion.
	 *
	 * @since ??
	 *
	 * @return array Array of htmlAttributes deprecated attribute mappings.
	 */
	private static function _get_html_attributes_definition() {
		return [
			'module.advanced.htmlAttributes__id'    => [
				'attrName' => 'module.advanced.htmlAttributes',
				'preset'   => 'content',
				'subName'  => 'id',
			],
			'module.advanced.htmlAttributes__class' => [
				'attrName' => 'module.advanced.htmlAttributes',
				'preset'   => [ 'html' ],
				'subName'  => 'class',
			],
		];
	}

	/**
	 * Get imageIconAlt deprecated attribute definition.
	 *
	 * Used by Blurb module for imageIcon.innerContent__alt attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of imageIconAlt deprecated attribute mappings.
	 */
	private static function _get_image_icon_alt_definition() {
		return [
			'imageIcon.innerContent__alt' => [
				'attrName' => 'imageIcon.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'alt',
			],
		];
	}

	/**
	 * Get imageAlt deprecated attribute definition.
	 *
	 * Used by modules with image elements for image.innerContent__alt attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of imageAlt deprecated attribute mappings.
	 */
	private static function _get_image_alt_definition() {
		return [
			'image.innerContent__alt' => [
				'attrName' => 'image.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'alt',
			],
		];
	}

	/**
	 * Get imageTitleText deprecated attribute definition.
	 *
	 * Used by modules with image elements for image.innerContent__titleText attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of imageTitleText deprecated attribute mappings.
	 */
	private static function _get_image_title_text_definition() {
		return [
			'image.innerContent__titleText' => [
				'attrName' => 'image.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'titleText',
			],
		];
	}

	/**
	 * Get logoAlt deprecated attribute definition.
	 *
	 * Used by modules with logo elements for logo.innerContent__alt attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of logoAlt deprecated attribute mappings.
	 */
	private static function _get_logo_alt_definition() {
		return [
			'logo.innerContent__alt' => [
				'attrName' => 'logo.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'alt',
			],
		];
	}

	/**
	 * Get formLayout deprecated attribute definition.
	 *
	 * Used by Signup module for module.advanced.layout attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of formLayout deprecated attribute mappings.
	 */
	private static function _get_form_layout_definition() {
		return [
			'module.advanced.layout' => [
				'attrName' => 'module.advanced.layout',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get portfolioLayout deprecated attribute definition.
	 *
	 * Used by Portfolio and Filterable Portfolio modules for portfolio.advanced.layout attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of portfolioLayout deprecated attribute mappings.
	 */
	private static function _get_portfolio_layout_definition() {
		return [
			'portfolio.advanced.layout' => [
				'attrName' => 'portfolio.advanced.layout',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get blogLayout deprecated attribute definition.
	 *
	 * Used by Blog module for fullwidth.advanced.enable attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of blogLayout deprecated attribute mappings.
	 */
	private static function _get_blog_layout_definition() {
		return [
			'fullwidth.advanced.enable' => [
				'attrName' => 'fullwidth.advanced.enable',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get portfolioGridFlexType deprecated attribute definition.
	 *
	 * Used by Portfolio and Filterable Portfolio modules for portfolioGrid.advanced.flexType attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of portfolioGridFlexType deprecated attribute mappings.
	 */
	private static function _get_portfolio_grid_flex_type_definition() {
		return [
			'portfolioGrid.advanced.flexType' => [
				'attrName' => 'portfolioGrid.advanced.flexType',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get blogGridFlexType deprecated attribute definition.
	 *
	 * Used by Blog module for blogGrid.advanced.flexType attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of blogGridFlexType deprecated attribute mappings.
	 */
	private static function _get_blog_grid_flex_type_definition() {
		return [
			'blogGrid.advanced.flexType' => [
				'attrName' => 'blogGrid.advanced.flexType',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get galleryGridFlexType deprecated attribute definition.
	 *
	 * Used by Gallery module for galleryGrid.advanced.flexType attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of galleryGridFlexType deprecated attribute mappings.
	 */
	private static function _get_gallery_grid_flex_type_definition() {
		return [
			'galleryGrid.advanced.flexType' => [
				'attrName' => 'galleryGrid.advanced.flexType',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get contactFieldFullwidth deprecated attribute definition.
	 *
	 * Used by Contact Field module for fieldItem.advanced.fullwidth attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of contactFieldFullwidth deprecated attribute mappings.
	 */
	private static function _get_contact_field_fullwidth_definition() {
		return [
			'fieldItem.advanced.fullwidth' => [
				'attrName' => 'fieldItem.advanced.fullwidth',
				'preset'   => [ 'html' ],
			],
		];
	}

	/**
	 * Get buttonRel deprecated attribute definition.
	 *
	 * Used by Button, CTA, and other modules for button.innerContent__rel attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonRel deprecated attribute mappings.
	 */
	private static function _get_button_rel_definition() {
		return [
			'button.innerContent__rel' => [
				'attrName' => 'button.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}

	/**
	 * Get buttonEnable deprecated attribute definition.
	 *
	 * Preserves legacy custom button toggle for preset conversion.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonEnable deprecated attribute mappings.
	 */
	private static function _get_button_enable_definition() {
		return [
			'button.decoration.button__enable' => [
				'attrName' => 'button.decoration.button',
				'preset'   => [ 'style' ],
				'subName'  => 'enable',
			],
		];
	}

	/**
	 * Get buttonAlignment deprecated attribute definition.
	 *
	 * Preserves legacy button alignment before composible migration moves it.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonAlignment deprecated attribute mappings.
	 */
	private static function _get_button_alignment_definition() {
		return [
			'button.decoration.button__alignment' => [
				'attrName' => 'button.decoration.button',
				'preset'   => [ 'style' ],
				'subName'  => 'alignment',
			],
		];
	}

	/**
	 * Get iconTitle deprecated attribute definition.
	 *
	 * Used by Icon module for icon.innerContent__title attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of iconTitle deprecated attribute mappings.
	 */
	private static function _get_icon_title_definition() {
		return [
			'icon.innerContent__title' => [
				'attrName' => 'icon.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'title',
			],
		];
	}

	/**
	 * Get buttonOneRel deprecated attribute definition.
	 *
	 * Used by Fullwidth Header module for buttonOne.innerContent__rel attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonOneRel deprecated attribute mappings.
	 */
	private static function _get_button_one_rel_definition() {
		return [
			'buttonOne.innerContent__rel' => [
				'attrName' => 'buttonOne.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}

	/**
	 * Get buttonOneEnable deprecated attribute definition.
	 *
	 * Preserves Fullwidth Header legacy button one custom style toggle.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonOneEnable deprecated attribute mappings.
	 */
	private static function _get_button_one_enable_definition() {
		return [
			'buttonOne.decoration.button__enable' => [
				'attrName' => 'buttonOne.decoration.button',
				'preset'   => [ 'style' ],
				'subName'  => 'enable',
			],
		];
	}

	/**
	 * Get buttonTwoRel deprecated attribute definition.
	 *
	 * Used by Fullwidth Header module for buttonTwo.innerContent__rel attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonTwoRel deprecated attribute mappings.
	 */
	private static function _get_button_two_rel_definition() {
		return [
			'buttonTwo.innerContent__rel' => [
				'attrName' => 'buttonTwo.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}

	/**
	 * Get buttonTwoEnable deprecated attribute definition.
	 *
	 * Preserves Fullwidth Header legacy button two custom style toggle.
	 *
	 * @since ??
	 *
	 * @return array Array of buttonTwoEnable deprecated attribute mappings.
	 */
	private static function _get_button_two_enable_definition() {
		return [
			'buttonTwo.decoration.button__enable' => [
				'attrName' => 'buttonTwo.decoration.button',
				'preset'   => [ 'style' ],
				'subName'  => 'enable',
			],
		];
	}

	/**
	 * Get imageTitle deprecated attribute definition.
	 *
	 * Used by Fullwidth Header module for image.innerContent__title attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of imageTitle deprecated attribute mappings.
	 */
	private static function _get_image_title_definition() {
		return [
			'image.innerContent__title' => [
				'attrName' => 'image.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'title',
			],
		];
	}

	/**
	 * Get logoTitle deprecated attribute definition.
	 *
	 * Used by Fullwidth Header module for logo.innerContent__title attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of logoTitle deprecated attribute mappings.
	 */
	private static function _get_logo_title_definition() {
		return [
			'logo.innerContent__title' => [
				'attrName' => 'logo.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'title',
			],
		];
	}

	/**
	 * Get childrenButtonRel deprecated attribute definition.
	 *
	 * Used by Slider and Pricing Tables modules for children button rel attributes.
	 *
	 * @since ??
	 *
	 * @return array Array of childrenButtonRel deprecated attribute mappings.
	 */
	private static function _get_children_button_rel_definition() {
		return [
			'children.button.innerContent__rel' => [
				'attrName' => 'children.button.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}

	/**
	 * Get childrenButtonEnable deprecated attribute definition.
	 *
	 * Preserves legacy children button custom style toggle for slider-like modules.
	 *
	 * @since ??
	 *
	 * @return array Array of childrenButtonEnable deprecated attribute mappings.
	 */
	private static function _get_children_button_enable_definition() {
		return [
			'children.button.decoration.button__enable' => [
				'attrName' => 'children.button.decoration.button',
				'preset'   => [ 'style' ],
				'subName'  => 'enable',
			],
		];
	}

	/**
	 * Get imageRel deprecated attribute definition.
	 *
	 * Used by Image module for image.innerContent__rel attribute.
	 *
	 * @since ??
	 *
	 * @return array Array of imageRel deprecated attribute mappings.
	 */
	private static function _get_image_rel_definition() {
		return [
			'image.innerContent__rel' => [
				'attrName' => 'image.innerContent',
				'preset'   => [ 'html' ],
				'subName'  => 'rel',
			],
		];
	}

	/**
	 * Get imageIconAnimation deprecated attribute definition.
	 *
	 * Preserves legacy Blurb image/icon animation before composible migration.
	 *
	 * @since ??
	 *
	 * @return array Array of imageIconAnimation deprecated attribute mappings.
	 */
	private static function _get_image_icon_animation_definition() {
		return [
			'imageIcon.innerContent__animation' => [
				'attrName' => 'imageIcon.innerContent',
				'preset'   => [ 'style' ],
				'subName'  => 'animation',
			],
		];
	}

	/**
	 * Get imageIconWidth deprecated attribute definition.
	 *
	 * Preserves legacy Blurb image/icon width before composible migration.
	 *
	 * @since ??
	 *
	 * @return array Array of imageIconWidth deprecated attribute mappings.
	 */
	private static function _get_image_icon_width_definition() {
		return [
			'imageIcon.advanced.width__image' => [
				'attrName' => 'imageIcon.advanced.width',
				'preset'   => [ 'style' ],
				'subName'  => 'image',
			],
			'imageIcon.advanced.width__icon'  => [
				'attrName' => 'imageIcon.advanced.width',
				'preset'   => [ 'style' ],
				'subName'  => 'icon',
			],
		];
	}

	/**
	 * Get imageIconAlignment deprecated attribute definition.
	 *
	 * Preserves legacy Blurb image/icon alignment before composible migration.
	 *
	 * @since ??
	 *
	 * @return array Array of imageIconAlignment deprecated attribute mappings.
	 */
	private static function _get_image_icon_alignment_definition() {
		return [
			'imageIcon.advanced.alignment' => [
				'attrName' => 'imageIcon.advanced.alignment',
				'preset'   => [ 'style' ],
			],
		];
	}

	/**
	 * Get imageIconBackground deprecated attribute definition.
	 *
	 * Preserves legacy Blurb image/icon background value shape before composible migration.
	 *
	 * @since ??
	 *
	 * @return array Array of imageIconBackground deprecated attribute mappings.
	 */
	private static function _get_image_icon_background_definition() {
		return [
			'imageIcon.decoration.background' => [
				'attrName' => 'imageIcon.decoration.background',
				'preset'   => [ 'style' ],
			],
		];
	}

	/**
	 * Get inactiveTabBackground deprecated attribute definition.
	 *
	 * Preserves legacy Tabs inactiveTab background before composible migration.
	 *
	 * @since ??
	 *
	 * @return array Array of inactiveTabBackground deprecated attribute mappings.
	 */
	private static function _get_inactive_tab_background_definition() {
		return [
			'inactiveTab.decoration.background' => [
				'attrName' => 'inactiveTab.decoration.background',
				'preset'   => [ 'style' ],
			],
		];
	}
}
