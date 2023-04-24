<?php
/**
 * The section defines the root functionality for a settings section
 *
 * @package WPGraphQL\WooCommerce\Admin
 * @since 0.1.0
 */

namespace WPGraphQL\QL_Events\Admin;

/**
 * Section class
 */
abstract class Section {

	/**
	 * Returns Section settings fields.
	 *
	 * @return array
	 */
	abstract public static function get_fields();
}
