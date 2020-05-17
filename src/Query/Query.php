<?php

namespace QueryWrangler\Query;

use Kinglet\Entity\Type\Post;

class Query extends Post {

	protected $type = 'widget';
	protected $display = [];
	protected $fields = [];
	protected $filters = [];
	protected $sorts = [];
	protected $args = [];

	public function __construct( $object ) {
		parent::__construct( $object );
		$data = $this->meta( 'query_data' );
		$this->populateV1( $data );
	}

	/**
	 * Populate the object expecting version 1.x values.
	 *
	 * @param $data
	 */
	public function populateV1( $data ) {
		if ( !empty( $data['type'] ) ) {
			$this->type = $data['type'];
		}
		if ( !empty( $data['data']['display']['field_settings']['fields'] ) ) {
			$this->fields = $data['data']['display']['field_settings']['fields'];
			unset( $data['data']['display']['field_settings']['fields'] );
		}
		if ( !empty( $data['data']['display'] ) ) {
			$this->display = $data['data']['display'];
		}
		if ( !empty( $data['data']['args']['sorts'] ) ) {
			$this->sorts = $data['data']['args']['sorts'];
			unset( $data['data']['args']['sorts'] );
		}
		if ( !empty( $data['data']['args']['filters'] ) ) {
			$this->filters = $data['data']['args']['filters'];
			unset( $data['data']['args']['filters'] );
		}
		if ( !empty( $data['data']['args'] ) ) {
			$this->args = $data['data']['args'];
		}
	}

}
