<?php

namespace QueryWrangler\Handler;

interface HandlerItemTypeInterface {

	/**
	 * Machine name for item. AKA, slug/hook_key.
	 *
	 * @return string
	 */
	public function type();

	/**
	 * Human readable item title.
	 *
	 * @return string
	 */
	public function title();

	/**
	 * Description of what the item does.
	 *
	 * @return string
	 */
	public function description();

	/**
	 * List of entity types where this item can be implemented.
	 *
	 * @return array
	 */
	public function queryTypes();

}
