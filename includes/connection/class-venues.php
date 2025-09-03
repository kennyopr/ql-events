<?php
/**
 * Connection - Venues
 *
 * Registers connections to Venue
 *
 * @package WPGraphQL\QL_Events\Connection
 * @since   0.0.1
 */

namespace WPGraphQL\QL_Events\Connection;

use Tribe__Events__Main as Main;
use WPGraphQL\Type\Connection\PostObjects;

/**
 * Class - Venues
 */
class Venues extends PostObjects {
	/**
	 * Registers the various connections from other Types to Venues
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From Event to Venues.
		register_graphql_connection(
			self::get_connection_config(
				get_post_type_object( Main::VENUE_POST_TYPE ),
				[
					'fromType'      => 'Event',
					'toType'        => 'Venue',
					'fromFieldName' => 'venues',
				]
			)
		);
	}
}
