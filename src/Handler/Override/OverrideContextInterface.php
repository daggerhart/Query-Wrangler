<?php

namespace QueryWrangler\Handler\Override;

use QueryWrangler\QueryPostEntity;

interface OverrideContextInterface {

	/**
	 * @param QueryPostEntity $query_post_entity
	 */
	public function setFoundEntity( QueryPostEntity $query_post_entity );

	/**
	 * @return QueryPostEntity
	 */
	public function getFoundEntity();

	/**
	 * @param OverrideInterface $override_type
	 */
	public function setOverrideType( OverrideInterface $override_type );

	/**
	 * @return OverrideInterface
	 */
	public function getOverrideType();

	/**
	 * @param array $vars
	 */
	public function setOriginalQueryVars( array $vars );

	/**
	 * @return array
	 */
	public function getOriginalQueryVars();

	/**
	 * @param object $object
	 */
	public function setOriginalQueriedObject( $object );

	/**
	 * @return object
	 */
	public function getOriginalQueriedObject();

}
