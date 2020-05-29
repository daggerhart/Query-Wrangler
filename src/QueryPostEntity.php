<?php

namespace QueryWrangler;

use Kinglet\Entity\Type\Post;
use Kinglet\Registry\Registry;
use Kinglet\Registry\RegistryInterface;
use WP_Post;

class QueryPostEntity extends Post {

	protected $queryType = 'post';
	protected $args = [];
	protected $display = [];

	protected $displayType = 'widget';
	protected $fields = [];
	protected $filters = [];
	protected $overrides = [];
	protected $pagerEnabled = false;
	protected $pagerStyle = [];
	protected $paging = [];
	protected $rowStyle = [];
	protected $sorts = [];
	protected $templateStyle = [];
	protected $wrapperStyle = [];

	/**
	 * @var RegistryInterface
	 */
	protected $renderContext;

	/**
	 * QwQuery constructor.
	 *
	 * @param int|WP_Post $object
	 */
	public function __construct( $object ) {
		parent::__construct( $object );

		if ( $this->isLoaded() ) {
			$this->populate();
			$this->renderContext = new Registry();
		}
	}

	/**
	 * Load a Query post entity by its post_name.
	 *
	 * @param string $slug
	 *
	 * @return bool|QueryPostEntity
	 */
	static public function loadBySlug( $slug ) {
		$posts = get_posts( [
			'post_type' => QueryPostType::SLUG,
			'post_name' => $slug,
			'posts_per_page' => 1,
			'ignore_sticky_posts' => 1,
		] );
		if ( count( $posts ) ) {
			return static::load( reset( $posts ) );
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
	 * @return RegistryInterface
	 */
	public function getRenderContext() {
		return $this->renderContext;
	}

	/**
	 * Set an item value in the render registry.
	 *
	 * @param $key
	 * @param $value
	 */
	public function setRendered( $key, $value ) {
		$this->renderContext->set( $key, $value );
	}

	/**
	 * Retrieve a value from the render registry.
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function getRendered( $key ) {
		return $this->renderContext->get( $key );
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
	 * Add a new filter to the query.
	 *
	 * @param string $name
	 * @param string $type
	 * @param array $settings
	 */
	public function addFilter( string $name, string $type, array $settings ) {
		$this->filters[ $name ] = $settings + [
			'type' => $type,
		];
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
	public function getOverrides() {
		return $this->overrides;
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
	 * @return array
	 */
	public function getWrapperStyle() {
		return $this->wrapperStyle;
	}

	/**
	 * Populate the object with data.
	 *
	 * @param array $data
	 * @param array $replace
	 */
	public function populate( $data = [], $replace = [] ) {
		if ( empty( $data ) ) {
			$data = $this->meta( 'query_data' );
		}

		if ( !empty( $data ) ) {
			if ( !is_array( $data ) ) {
				$data = json_decode( $data, true );
			}

			// Version 1.x imported data.
			if ( isset( $data['data'] ) ) {
				$this->populateLegacy( $data, $replace );
			}
		}
	}

	/**
	 * Populate the object expecting version 1.x values.
	 *
	 * @param array $data
	 * @param array $replace
	 */
	protected function populateLegacy( $data, $replace ) {
		if ( !empty( $replace ) ) {
			$data['data'] = array_replace_recursive( $data['data'], $replace );
		}

		// Display Type
		if ( !empty( $data['type'] ) ) {
			$this->displayType = $data['type'];
		}

		// Overrides
		if ( !empty( $data['data']['override'] ) ) {
			$this->overrides = $data['data']['override'];
			unset( $data['data']['override'] );
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
		// Wrapper Style
		if ( !empty( $data['data']['display']['title'] ) ) {
			$this->wrapperStyle = [
				'type' => 'legacy',
				'title' => $data['data']['display']['title'] ?? '',
				'header' => $data['data']['display']['header'] ?? '',
				'footer' => $data['data']['display']['footer'] ?? '',
				'empty' => $data['data']['display']['empty'] ?? '',
				'wrapper_classes' => $data['data']['display']['wrapper-classes'] ?? '',
			];
			unset(
				$data['data']['display']['title'],
				$data['data']['display']['header'],
				$data['data']['display']['footer'],
				$data['data']['display']['empty'],
				$data['data']['display']['wrapper-classes']
			);
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