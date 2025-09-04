<?php

use QL_Events\Test\Factories\Venue;
use QL_Events\Test\Factories\Event;

class VenueConnectionTest extends \QL_Events\Test\TestCase\QLEventsTestCase {
    
    public function testEventVenuesConnection() {
        // Create test venues
        $venue1_id = $this->factory->venue->create([
            'post_title' => 'Test Venue 1',
            'meta_input' => [
                '_VenueAddress' => '123 Main St',
                '_VenueCity' => 'Test City',
                '_VenueState' => 'TS',
                '_VenueZip' => '12345',
            ]
        ]);
        
        $venue2_id = $this->factory->venue->create([
            'post_title' => 'Test Venue 2',
            'meta_input' => [
                '_VenueAddress' => '456 Oak Ave',
                '_VenueCity' => 'Another City',
                '_VenueState' => 'AS',
                '_VenueZip' => '67890',
            ]
        ]);

        // Create test event with one venue (typical TEC behavior)
        $event_id = $this->factory->event->create([
            'post_title' => 'Test Event',
            'meta_input' => [
                '_EventVenueID' => $venue1_id, // Single venue, not array
            ]
        ]);

        // Create test query
        $query = '
            query($id: ID!) {
                event(id: $id) {
                    id
                    databaseId
                    venues {
                        nodes {
                            id
                            databaseId
                            title
                            address
                            city
                            state
                            zip
                        }
                    }
                }
            }
        ';

        /**
         * Assertion
         */
        $this->loginAs(1); // Login as admin
        $variables = array( 'id' => $this->toRelayId( 'post', $event_id ) );
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Check that the response is successful
        $this->assertQuerySuccessful( $response );
        
        // Check that venues are returned
        $this->assertArrayHasKey( 'data', $response );
        $this->assertArrayHasKey( 'event', $response['data'] );
        $this->assertArrayHasKey( 'venues', $response['data']['event'] );
        $this->assertArrayHasKey( 'nodes', $response['data']['event']['venues'] );
        
        // Should have 1 venue (the one associated with this event)
        $this->assertCount( 1, $response['data']['event']['venues']['nodes'] );
        
        // Check venue data
        $venues = $response['data']['event']['venues']['nodes'];
        $venue_titles = array_column( $venues, 'title' );
        $this->assertContains( 'Test Venue 1', $venue_titles );
        $this->assertNotContains( 'Test Venue 2', $venue_titles ); // Should not contain venue not associated with this event
    }
    
    public function testVenuesRootQuery() {
        // Create test venues
        $venue1_id = $this->factory->venue->create([
            'post_title' => 'Root Venue 1',
            'meta_input' => [
                '_VenueAddress' => '789 Pine St',
                '_VenueCity' => 'Root City',
                '_VenueState' => 'RC',
                '_VenueZip' => '11111',
            ]
        ]);
        
        $venue2_id = $this->factory->venue->create([
            'post_title' => 'Root Venue 2',
            'meta_input' => [
                '_VenueAddress' => '321 Elm St',
                '_VenueCity' => 'Another Root City',
                '_VenueState' => 'AR',
                '_VenueZip' => '22222',
            ]
        ]);

        // Create test query to get all venues
        $query = '
            query {
                venues {
                    nodes {
                        id
                        databaseId
                        title
                        address
                        city
                        state
                        zip
                    }
                }
            }
        ';

        /**
         * Assertion
         */
        $this->loginAs(1); // Login as admin
        $response = $this->graphql( compact( 'query' ) );

        // Check that the response is successful
        $this->assertQuerySuccessful( $response );
        
        // Check that venues are returned
        $this->assertArrayHasKey( 'data', $response );
        $this->assertArrayHasKey( 'venues', $response['data'] );
        $this->assertArrayHasKey( 'nodes', $response['data']['venues'] );
        
        // Should have at least 2 venues (the ones we created)
        $this->assertGreaterThanOrEqual( 2, count( $response['data']['venues']['nodes'] ) );
        
        // Check venue data
        $venues = $response['data']['venues']['nodes'];
        $venue_titles = array_column( $venues, 'title' );
        $this->assertContains( 'Root Venue 1', $venue_titles );
        $this->assertContains( 'Root Venue 2', $venue_titles );
    }
}
