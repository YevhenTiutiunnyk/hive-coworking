<?php
namespace Hive\Core\Events;

use Hive\Core\Content\Meta;
use Hive\Core\Rest\AvailabilityController;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET /hive/v1/events/{id}/ics: an event as an iCalendar file for "Add to calendar" links.
 */
final class EventsController {

	private const CONTENT_TYPE = 'text/calendar; charset=utf-8';

	/**
	 * Creates the controller.
	 *
	 * @param EventRepository $events Event lookup.
	 */
	public function __construct( private readonly EventRepository $events ) {}

	/**
	 * Registers the route and the raw output filter.
	 */
	public function register_routes(): void {
		register_rest_route(
			AvailabilityController::NAMESPACE,
			'/events/(?P<id>\d+)/ics',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_ics' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			)
		);

		add_filter( 'rest_pre_serve_request', array( self::class, 'serve_raw' ), 10, 2 );
	}

	/**
	 * Route callback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function get_ics( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$event = $this->events->find( (int) $request['id'] );
		if ( null === $event ) {
			return new WP_Error( 'hive_event_not_found', __( 'Event not found.', 'hive-core' ), array( 'status' => 404 ) );
		}

		$location = '';
		if ( $event->location_id ) {
			$address  = (string) get_post_meta( $event->location_id, Meta::LOCATION_ADDRESS, true );
			$location = implode( ', ', array_filter( array( get_the_title( $event->location_id ), $address ) ) );
		}

		$ics = Ics::event(
			array(
				'uid'         => sprintf( 'event-%d@%s', $event->id, wp_parse_url( home_url(), PHP_URL_HOST ) ),
				'title'       => html_entity_decode( $event->title, ENT_QUOTES, 'UTF-8' ),
				'description' => html_entity_decode( wp_strip_all_tags( $event->excerpt ), ENT_QUOTES, 'UTF-8' ),
				'location'    => html_entity_decode( $location, ENT_QUOTES, 'UTF-8' ),
				'url'         => $event->url,
				'start'       => $event->start,
				'end'         => $event->end,
				'stamp'       => current_datetime(),
			)
		);

		$response = new WP_REST_Response( $ics );
		$response->header( 'Content-Type', self::CONTENT_TYPE );
		$response->header( 'Content-Disposition', sprintf( 'attachment; filename="%s.ics"', sanitize_file_name( $event->slug ) ) );

		return $response;
	}

	/**
	 * Sends calendar responses as-is instead of JSON-encoding them.
	 *
	 * @param bool             $served Whether the request has been served.
	 * @param WP_HTTP_Response $result Response.
	 */
	public static function serve_raw( bool $served, WP_HTTP_Response $result ): bool {
		if ( $served || self::CONTENT_TYPE !== ( $result->get_headers()['Content-Type'] ?? '' ) ) {
			return $served;
		}

		echo $result->get_data(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- iCalendar text, not HTML.

		return true;
	}
}
