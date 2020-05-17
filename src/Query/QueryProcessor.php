<?php

namespace QueryWrangler\Query;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Entity\TypeInterface;

class QueryProcessor implements ContainerInjectionInterface {

	public static function create( ContainerInterface $container ) {
		return new static();
	}

	/**
	 * @param Query $query
	 */
	public function execute( Query $query ) {

	}

	public function preprocessQueryData( $data ) {
		return $data;
	}

}
