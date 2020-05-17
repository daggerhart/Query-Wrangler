<?php

namespace QueryWrangler\Handler\Field;

use QueryWrangler\Handler\HandlerItemTypeInterface;

interface FieldInterface extends HandlerItemTypeInterface {

	/**
	 * Whether or not this field output should receive common content processing
	 * options such as do_shortcode(), wpautop, etc.
	 *
	 * @return bool
	 */
	public function processAsContent();

	/**
	 * Array of context during rendering.
	 * Includes:
	 *   - $post WP_Post
	 *   - $field array
	 *   - $tokens array
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function render( array $context );

	/**
	 * HTML form output for the administration configuration of this field.
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public function settingsForm( array $field );

}
