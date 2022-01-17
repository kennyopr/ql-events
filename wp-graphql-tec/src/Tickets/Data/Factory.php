<?php
/**
 * Factory Class
 *
 * This class serves as a factory for all ET resolvers.
 *
 * @package WPGraphQL\TEC\Tickets\Data
 * @since 0.0.1
 */

namespace WPGraphQL\TEC\Tickets\Data;

use WP_Post;
use WPGraphQL\Model\Model as GraphQLModel;
use WPGraphQL\TEC\Tickets\Model;
use WPGraphQL\TEC\Tickets\Type\WPInterface;
use WPGraphQL\TEC\Common\Type\WPInterface as CommonInterface;
use WPGraphQL\TEC\Traits\PostTypeResolverMethod;
use WPGraphQL\TEC\Utils\Utils;

/**
 * Class - Factory
 */
class Factory {
	use PostTypeResolverMethod;

	/**
	 * Overwrites the GraphQL config for auto-registered object types.
	 *
	 * @param array $config .
	 */
	public static function set_object_type_config( array $config ) : array {
		$post_type = Utils::graphql_type_to_post_type( $config['name'] );

		if ( is_null( $post_type ) ) {
			return $config;
		}

		switch ( true ) {
			case 'tribe_rsvp_tickets' === $post_type:
				$config['interfaces'] = [ WPInterface\Ticket::$type ];
				break;
			case in_array( $post_type, [ 'tec_tc_ticket', 'tribe_tpp_tickets' ], true ):
				$config['interfaces'] = [ WPInterface\PurchasableTicket::$type ];
				break;
			case in_array( $post_type, Utils::get_enabled_post_types_for_tickets(), true ):
				$config['interfaces'] = array_merge(
					$config['interfaces'],
					[
						WPInterface\NodeWithTickets::$type,
						WPInterface\NodeWithAttendees::$type,
						CommonInterface\NodeWithJsonLd::$type,
					]
				);
				break;
			case in_array( $post_type, array_keys( Utils::get_et_attendee_types() ), true ):
				$config['interfaces'] = [ WPInterface\Attendee::$type ];
				break;
			case in_array( $post_type, array_keys( Utils::get_et_order_types() ), true ):
				$config['interfaces'] = [ WPInterface\Order::$type ];
				break;
		}
		return $config;
	}

	/**
	 * Ensures the correct models are used even if the dataloader is wrong.
	 *
	 * @param null  $model  Possible model instance to be loader.
	 * @param mixed $entry  Data source.
	 * @return GraphQLModel|null
	 */
	public static function set_models_for_dataloaders( $model, $entry ) {
		if ( is_a( $entry, WP_Post::class ) ) {
			switch ( $entry->post_type ) {
				case 'tribe_rsvp_tickets':
					$model = new Model\RsvpTicket( $entry );
					break;
				case 'tec_tc_ticket':
				case 'tribe_tpp_tickets':
					$model = new Model\PurchasableTicket( $entry );
					break;
				case 'tec_tc_attendee':
				case 'tribe_rsvp_attendees':
				case 'tribe_tpp_attendees':
					$model = new Model\Attendee( $entry );
					break;
				case 'tec_tc_order':
				case 'tec_tpp_orders':
					$model = new Model\Order( $entry );
			}
		}

		return $model;
	}

	/**
	 * Resolves Relay node for some TEC GraphQL types.
	 *
	 * @param mixed $type     Node type.
	 * @param mixed $node     Node object.
	 *
	 * @return mixed
	 */
	public static function resolve_node_type( $type, $node ) {
		switch ( true ) {
			case is_a( $node, Model\RsvpTicket::class ):
				$type = Model\RsvpTicket::class;
				break;
			case is_a( $node, Model\PurchasableTicket::class ):
				$type = Model\PurchasableTicket::class;
				break;
			case is_a( $node, Model\Attendee::class ):
				$type = Model\Attendee::class;
				break;
			case is_a( $node, Model\Order::class ):
				$type = Model\Order::class;
				break;
		}

		return $type;
	}

	/**
	 * Overwrites the GraphQL config for auto-registered object types.
	 *
	 * @param array $config .
	 */
	public static function set_connection_type_config( array $config ) : array {
		// Return early if not RootQuery or EventsCategory.
		if ( 'RootQuery' !== $config['fromType'] ) {
			return $config;
		}

		switch ( true ) {
			case in_array( $config['toType'], Utils::get_et_ticket_types(), true ):
				$config = TicketHelper::get_connection_config( $config );
				break;
			case in_array( $config['toType'], Utils::get_et_attendee_types(), true ):
				$config = AttendeeHelper::get_connection_config( $config );
				break;
			case in_array( $config['toType'], Utils::get_et_order_types(), true ):
				$config = OrderHelper::get_connection_config( $config );
				break;
		}

		return $config;
	}

	/**
	 * Fixes the default orderby args set by TEC.
	 *
	 * @param array $query_args An array of the query arguments the query will be initialized with.
	 */
	public static function tribe_fix_orderby_args( array $query_args ) : array {
		// Checks if `orderby` isnt using an associative array.
		if ( isset( $query_args['orderby'] ) && is_array( $query_args['orderby'] ) && isset( $query_args['orderby'][0] ) ) {
			$orderby = [];
			$order   = $query_args['order'] ?? 'DESC';

			foreach ( $query_args['orderby'] as $field ) {
				$orderby[ $field ] = $order;
			}
			$query_args['orderby'] = $orderby;
		}

		return $query_args;
	}
}
