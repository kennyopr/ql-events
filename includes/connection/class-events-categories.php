<?php
/**
 * Connection - Events Categories
 *
 * Registers connections to Events Categories
 *
 * @package WPGraphQL\QL_Events\Connection
 * @since   0.0.1
 */

namespace WPGraphQL\QL_Events\Connection;

use Tribe__Events__Main as Main;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;

/**
 * Class - Events_Categories
 */
class Events_Categories {
	/**
	 * Registers the various connections from other Types to Events Categories
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From Event to Events Categories.
		register_graphql_connection(
			[
				'fromType'       => 'Event',
				'toType'         => 'EventsCategory',
				'fromFieldName'  => 'eventsCategories',
				'connectionArgs' => [
					'first'  => [
						'type'        => 'Int',
						'description' => __( 'The number of items to return after the referenced "after" cursor', 'ql-events' ),
					],
					'last'   => [
						'type'        => 'Int',
						'description' => __( 'The number of items to return before the referenced "before" cursor', 'ql-events' ),
					],
					'after'  => [
						'type'        => 'String',
						'description' => __( 'Cursor used along with the "first" argument to reference where in the dataset to get data', 'ql-events' ),
					],
					'before' => [
						'type'        => 'String',
						'description' => __( 'Cursor used along with the "last" argument to reference where in the dataset to get data', 'ql-events' ),
					],
				],
				'resolve'        => function( $source, $args, $context, $info ) {
					// Get the object ID, handling preview posts
					$object_id = true === $source->isPreview && ! empty( $source->parentDatabaseId ) ? $source->parentDatabaseId : $source->databaseId;

					if ( empty( $object_id ) || ! absint( $object_id ) ) {
						return null;
					}

					// Normalize event IDs from TEC custom tables
					if ( $object_id >= 10000000 && class_exists( '\TEC\Events\Custom_Tables\V1\Models\Occurrence' ) ) {
						$object_id = \TEC\Events\Custom_Tables\V1\Models\Occurrence::normalize_id( $object_id );
					}

					$resolver = new TermObjectConnectionResolver( $source, $args, $context, $info, Main::TAXONOMY );
					$resolver->set_query_arg( 'object_ids', absint( $object_id ) );
					return $resolver->get_connection();
				},
			]
		);
	}
}
