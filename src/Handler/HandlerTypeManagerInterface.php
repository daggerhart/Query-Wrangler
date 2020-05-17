<?php

namespace QueryWrangler\Handler;

interface HandlerTypeManagerInterface {

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

}
