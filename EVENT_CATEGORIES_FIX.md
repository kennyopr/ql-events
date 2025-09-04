# Event Categories Fix

## Problem

The `eventsCategories` field in GraphQL queries was returning empty nodes even though the REST API showed that events had categories associated with them. This was due to two issues:

1. **Missing explicit field resolver**: The `eventsCategories` field was relying on WPGraphQL's automatic taxonomy connection, which wasn't working properly for the `tribe_events_cat` taxonomy.

2. **DatabaseId mismatch**: Since TEC 6.2.3, there was a mismatch between the databaseId returned by GraphQL and the actual post ID, causing taxonomy connections to fail.

## Solution

### 1. Added explicit `eventsCategories` field resolver

Added a custom resolver in `includes/types/object/class-event-type.php` that:

- Gets the actual post ID from the source
- Retrieves taxonomy terms using `get_the_terms( $post_id, 'tribe_events_cat' )`
- Returns the terms in the proper GraphQL connection format with `nodes` and `edges`

### 2. Added custom `databaseId` resolver

Added a custom `databaseId` field resolver that ensures the correct post ID is returned:

```php
'databaseId' => [
    'type'        => [ 'non_null' => 'Int' ],
    'description' => __( 'Event database ID', 'ql-events' ),
    'resolve'     => function( $source ) {
        return $source->ID;
    },
],
```

## Files Modified

- `includes/types/object/class-event-type.php` - Added both `databaseId` and `eventsCategories` field resolvers

## Testing

Created `tests/wpunit/EventCategoriesTest.php` to verify:

1. Events with categories return the correct category data
2. Events without categories return empty category arrays
3. The databaseId matches the actual post ID

## Usage

After this fix, the following GraphQL query will work correctly:

```graphql
query getEvents($id: Int = 10001712) {
  events(where: {id: $id}) {
    nodes {
      databaseId
      title
      eventsCategories {
        nodes {
          id
          databaseId
          name
          slug
          description
        }
      }
    }
  }
}
```

The `eventsCategories` field will now return the actual categories associated with the event, matching what's shown in the REST API.


