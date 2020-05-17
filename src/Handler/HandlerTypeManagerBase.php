<?php

namespace QueryWrangler\Handler;

use Kinglet\Container\ContainerInjectionInterface;
use Kinglet\Container\ContainerInterface;
use Kinglet\Invoker\InvokerInterface;
use Kinglet\Registry\Registry;
use Kinglet\Template\RendererInterface;

abstract class HandlerTypeManagerBase extends Registry implements HandlerTypeManagerInterface, ContainerInjectionInterface {

	/**
	 * @var InvokerInterface
	 */
	protected $invoker;

	/**
	 * @var RendererInterface
	 */
	protected $renderer;

	/**
	 * FilterManager constructor.
	 *
	 * @param InvokerInterface $invoker
	 * @param RendererInterface $renderer
	 * @param array $items
	 */
	public function __construct( InvokerInterface $invoker, RendererInterface $renderer, array $items = [] ) {
		$this->invoker = $invoker;
		$this->renderer = $renderer;
		parent::__construct( $items );
	}

	/**
	 * @inheritDoc
	 */
	public static function create( ContainerInterface $container ) {
		return new static(
			$container->get( 'invoker' ),
			$container->get( 'renderer.callable' )
		);
	}

}
