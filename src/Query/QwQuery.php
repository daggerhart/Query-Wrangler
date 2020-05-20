<?php

namespace QueryWrangler\Query;

use Kinglet\Entity\Type\Post;
use WP_Post;

class QwQuery extends Post {

	protected $queryType = 'post';
	protected $displayType = 'widget';
	protected $display = [];
	protected $paging = [];
	protected $fields = [];
	protected $filters = [];
	protected $sorts = [];
	protected $args = [];
	protected $rowStyle = 'posts';

	/**
	 * QwQuery constructor.
	 *
	 * @param int|WP_Post $object
	 */
	public function __construct( $object ) {
		parent::__construct( $object );

		if ( $this->isLoaded() ) {
			$data = $this->meta( 'query_data' );
			if ( !empty( $data ) ) {
				$this->populateV1( $data );
			}
		}
	}

	/**
	 * Load a Query post entity by its post_name.
	 *
	 * @param string $slug
	 *
	 * @return bool|QwQuery
	 */
	static public function loadBySlug( $slug ) {
		$posts = get_posts( [
			'post_type' => 'qw_query',
			'post_name' => $slug,
			'posts_per_page' => 1,
			'ignore_sticky_posts' => 1,
		] );
		if ( count( $posts ) ) {
			return self::load( reset( $posts ) );
		}
		return FALSE;
	}

	/**
	 * Determine if this instance has a loaded object.
	 *
	 * @return bool
	 */
	public function isLoaded() {
		return !! $this->object();
	}

	/**
	 * @return string
	 */
	public function displayType() {
		return $this->displayType;
	}

	/**
	 * @return string
	 */
	public function queryType() {
		return $this->queryType;
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return $this->filters;
	}

	/**
	 * @return array
	 */
	public function getSorts() {
		return $this->sorts;
	}

	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @return array
	 */
	public function getDisplay() {
		return $this->display;
	}

	/**
	 * @return array
	 */
	public function getPaging() {
		return $this->paging;
	}

	public function getRowStyle() {
		return $this->rowStyle;
	}

	/**
	 * Populate the object expecting version 1.x values.
	 *
	 * @param $data
	 */
	public function populateV1( $data ) {
		if ( !empty( $data['type'] ) ) {
			$this->displayType = $data['type'];
		}

		// Display
		if ( !empty( $data['data']['display']['field_settings']['fields'] ) ) {
			$this->fields = $data['data']['display']['field_settings']['fields'];
			unset( $data['data']['display']['field_settings']['fields'] );
		}
		if ( !empty( $data['data']['display']['page'] ) ) {
			$this->paging = $data['data']['display']['page'];
			unset( $data['data']['display']['page'] );

			// Some old "args" are now paging item types.
			$items = ['posts_per_page', 'offset'];
			foreach ( $items as $item ) {
				if ( isset( $data['data']['args'][ $item ] ) ) {
					$this->paging[ $item ] = $data['data']['args'][ $item ];
				}
			}
		}
		if ( !empty( $data['data']['display']['row_style'] ) ) {
			$this->rowStyle = $data['data']['display']['row_style'];
			unset( $data['data']['display']['row_style'] );
		}
		if ( !empty( $data['data']['display'] ) ) {
			$this->display = $data['data']['display'];
		}

		// Args
		if ( !empty( $data['data']['args']['sorts'] ) ) {
			$this->sorts = $data['data']['args']['sorts'];
			unset( $data['data']['args']['sorts'] );
		}
		if ( !empty( $data['data']['args']['filters'] ) ) {
			$this->filters = $data['data']['args']['filters'];
			unset( $data['data']['args']['filters'] );

			// Some old "args" are now filter item types.
			$items = ['post_status', 'ignore_sticky_posts'];
			foreach ( $items as $item ) {
				if ( isset( $data['data']['args'][ $item ] ) ) {
					$this->filters[ $item ] = [
						'type' => $item,
						'value' => $data['data']['args'][ $item ]
					];
				}
			}
		}
		if ( !empty( $data['data']['args'] ) ) {
			$this->args = $data['data']['args'];
		}
	}

}
