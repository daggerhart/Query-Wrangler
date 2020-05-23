<?php

namespace QueryWrangler\Query;

use Kinglet\Entity\Type\Post;
use WP_Post;

class QwQuery extends Post {

	protected $queryType = 'post';
	protected $args = [];
	protected $display = [];

	protected $displayType = 'widget';
	protected $fields = [];
	protected $filters = [];
	protected $pagerEnabled = false;
	protected $pagerStyle = [];
	protected $paging = [];
	protected $rowStyle = [];
	protected $sorts = [];
	protected $templateStyle = [];

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
				if ( !is_array( $data ) ) {
					try {
						$data = json_decode( $data, true );
					}
					catch ( \Exception $e ) {}
				}
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
	public function getDisplayType() {
		return $this->displayType;
	}

	/**
	 * @return string
	 */
	public function getQueryType() {
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
	 * @return bool
	 */
	public function getPagerEnabled() {
		return $this->pagerEnabled;
	}

	/**
	 * @return array
	 */
	public function getPagerStyle() {
		return $this->pagerStyle;
	}

	/**
	 * @return array
	 */
	public function getPaging() {
		return $this->paging;
	}

	/**
	 * @return array
	 */
	public function getRowStyle() {
		return $this->rowStyle;
	}

	/**
	 * @return array
	 */
	public function getTemplateStyle() {
		return $this->templateStyle;
	}

	/**
	 * Populate the object expecting version 1.x values.
	 *
	 * @param $data
	 */
	protected function populateV1( $data ) {
		// Display Type
		if ( !empty( $data['type'] ) ) {
			$this->displayType = $data['type'];
		}

		// Fields
		if ( !empty( $data['data']['display']['field_settings']['fields'] ) ) {
			$this->fields = $data['data']['display']['field_settings']['fields'];
			unset( $data['data']['display']['field_settings']['fields'] );
		}
		// Pager Style
		if ( !empty( $data['data']['display']['page']['pager'] ) ) {
			$this->pagerStyle = $data['data']['display']['page']['pager'];
			$this->pagerEnabled = !empty( $this->pagerStyle['active'] );
			unset( $data['data']['display']['page']['pager'] );
		}
		// Page
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
		// Template Style
		if ( !empty( $data['data']['display']['style'] ) ) {
			$this->templateStyle = [
				'type' => $data['data']['display']['style'],
			];
			unset( $data['data']['display']['style'] );
		}
		// Row Style
		if ( !empty( $data['data']['display']['row_style'] ) ) {
			$row_style_type = $data['data']['display']['row_style'];
			$row_style_type_singular =  rtrim ( $row_style_type, 's' );
			$this->rowStyle = [
				'type' => $row_style_type,
			];
			unset( $data['data']['display']['row_style'] );

			if ( isset( $data['data']['display'][ $row_style_type_singular . '_settings' ] ) ) {
				$this->rowStyle = array_merge( $this->rowStyle, $data['data']['display'][ $row_style_type_singular . '_settings' ] );
				unset( $data['data']['display'][ $row_style_type_singular . '_settings' ] );
			}
		}
		// Sorts
		if ( !empty( $data['data']['args']['sorts'] ) ) {
			$this->sorts = $data['data']['args']['sorts'];
			unset( $data['data']['args']['sorts'] );
		}
		// Filters
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
		// Display (whatever is left)
		if ( !empty( $data['data']['display'] ) ) {
			$this->display = $data['data']['display'];
		}
		// Args (whatever is left)
		if ( !empty( $data['data']['args'] ) ) {
			$this->args = $data['data']['args'];
		}
	}

}
