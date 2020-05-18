<?php

namespace QueryWrangler\Handler\Display;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface DisplayInterface extends HandlerItemTypeInterface {

	/**
	 * The numeric order of where this item appears in the administration form.
	 *
	 * @return int
	 */
	public function order();

	/**
	 * HTML form output for the administration configuration of this sort.
	 *
	 * @param array $display
	 * @param array $values
	 *
	 * @return string
	 */
	public function settingsForm( array $display, array $values );

}
