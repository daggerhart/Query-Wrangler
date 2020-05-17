<?php

namespace QueryWrangler\Handler;

use Kinglet\Registry\Registry;

class HandlerManager extends Registry {

	/**
	 * {@inheritDoc}
	 * @return HandlerTypeManagerInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

}
