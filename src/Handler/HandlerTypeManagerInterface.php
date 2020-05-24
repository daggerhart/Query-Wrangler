<?php

namespace QueryWrangler\Handler;

use Kinglet\Registry\RegistryInterface;
use QueryWrangler\Query\QueryPostEntity;

interface HandlerTypeManagerInterface extends RegistryInterface {

	/**
	 * Unique name for the type of items managed.
	 *
	 * @return string
	 */
	public function type();

	/**
	 * Whether or not this type of handler item can be used more than once per query.
	 *
	 * @return bool
	 */
	public function multiple();

	/**
	 * Gather all item types of the handler type.
	 *
	 * @void
	 */
	public function collect();

	/**
	 * @param QueryPostEntity $query
	 *
	 * @return array
	 */
	public function getDataFromQuery( QueryPostEntity $query );

}
