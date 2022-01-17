<?php
/**
 * Type TicketTypeEnum
 *
 * @package WPGraphQL\TEC\Tickets\Type\Enum
 * @since   0.0.1
 */

namespace WPGraphQL\TEC\Tickets\Type\Enum;

use WPGraphQL\TEC\TEC;
use WPGraphQL\TEC\Utils\Utils;

/**
 * Class - TicketTypeEnum
 */
class TicketTypeEnum {
	/**
	 * Name of the type.
	 *
	 * @var string Type name.
	 */
	public static $type = 'TicketTypeEnum';

	/**
	 * Registers the GraphQL type
	 */
	public static function register_type() : void {
		register_graphql_enum_type(
			self::$type,
			[
				'description' => __( 'The ticket post type.', 'wp-graphql-tec' ),
				'values'      => self::get_values(),
			]
		);
	}

	/**
	 * Generates the Enum values for the config.
	 *
	 * @return array
	 */
	public static function get_values() : array {
		$ticket_types = Utils::get_et_ticket_types();
		$values       = [];

		foreach ( $ticket_types as $value => $name ) {
			$values[ $value ] = [
				'name'        => strtoupper( str_replace( 'Ticket', '_Ticket', $name ) ),
				'value'       => $value,
				/* translators: GraphQL ticket type name */
				'description' => sprintf( __( 'A %s ticket type', 'wp-graphql-tec' ), $name ),
			];
		}
		return $values;
	}
}
