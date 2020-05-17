<?php

namespace QueryWrangler\Handler;

interface HandlerManagerInterface {

	/**
	 * Unique name for the type of items managed.
	 *
	 * @return string
	 */
	public function type();

	/**
	 * Gather all item types of the handler type.
	 *
	 * @void
	 */
	public function collect();

}
