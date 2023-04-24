<?php
/**
 * WPObject Type - WooTicket
 *
 * Registers "Product" type to "Ticket" interface
 *
 * @package \WPGraphQL\QL_Events\Type\WPObject
 * @since   0.1.1
 */

namespace WPGraphQL\QL_Events\Type\WPObject;

use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;

/**
 * Class - WooTicket_Type
 */
class WooTicket_Type {
	/**
	 * Resolves the GraphQL type for "WooOrder".
	 *
	 * @return void
	 */
	public static function register_to_ticket_interface() {
		add_filter(
			'ql_events_resolve_ticket_type',
			function ( $type, $value ) {
				$type_registry = \WPGraphQL::get_type_registry();
				if ( tribe( 'tickets-plus.commerce.woo' )->ticket_object ) {
					$type = $type_registry->get_type( 'SimpleProduct' );
				}

				return $type;
			},
			10,
			2
		);
	}
}
