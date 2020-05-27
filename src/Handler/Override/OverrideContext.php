<?php

namespace QueryWrangler\Handler\Override;

use Kinglet\Registry\Registry;
use QueryWrangler\QueryPostEntity;

class OverrideContext extends Registry implements OverrideContextInterface {

	/**
	 * @inheritDoc
	 */
	public function setFoundEntity( QueryPostEntity $query_post_entity ) {
		$this->set( 'entity', $query_post_entity );
	}

	/**
	 * @inheritDoc
	 */
	public function getFoundEntity() {
		return $this->get( 'entity' );
	}

	/**
	 * @inheritDoc
	 */
	public function setOverrideType( OverrideInterface $override_type ) {
		$this->set( 'override_type', $override_type );
	}

	/**
	 * @inheritDoc
	 */
	public function getOverrideType() {
		return $this->get( 'override_type' );
	}

	/**
	 * @inheritDoc
	 */
	public function setOriginalQueryVars( array $vars ) {
		$this->set( 'original_query_vars', $vars );
	}

	/**
	 * @inheritDoc
	 */
	public function getOriginalQueryVars() {
		return $this->get( 'original_query_vars' );
	}

	/**
	 * @inheritDoc
	 */
	public function setOriginalQueriedObject( $object ) {
		$this->set( 'original_queried_object', $object );
	}

	/**
	 * @inheritDoc
	 */
	public function getOriginalQueriedObject() {
		return $this->get( 'original_queried_object' );
	}

}
