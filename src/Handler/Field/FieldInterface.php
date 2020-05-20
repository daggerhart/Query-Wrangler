<?php

namespace QueryWrangler\Handler\Field;

use Kinglet\Entity\TypeInterface;
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
	 *
	 * @param TypeInterface $entity
	 * @param array $settings
	 * @param array $tokens
	 *
	 * @return string
	 */
	public function render( TypeInterface $entity, array $settings, array $tokens = [] );

	/**
	 * HTML form output for the administration configuration of this field.
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public function settingsForm( array $field );

}
