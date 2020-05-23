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
	protected $callableRenderer;

	/**
	 * @var RendererInterface
	 */
	protected $fileRenderer;

	/**
	 * @var RendererInterface
	 */
	protected $stringRenderer;

	/**
	 * HandlerTypeManagerBase constructor.
	 *
	 * @param InvokerInterface $invoker
	 * @param RendererInterface $file_renderer
	 * @param RendererInterface $callable_renderer
	 * @param RendererInterface $string_renderer
	 * @param array $items
	 */
	public function __construct(
		InvokerInterface $invoker,
		RendererInterface $file_renderer,
		RendererInterface $callable_renderer,
		RendererInterface $string_renderer,
		array $items = [] )
	{
		$this->invoker = $invoker;
		$this->fileRenderer = $file_renderer;
		$this->callableRenderer = $callable_renderer;
		$this->stringRenderer = $string_renderer;
		parent::__construct( $items );
	}

	/**
	 * @inheritDoc
	 */
	public static function create( ContainerInterface $container ) {
		return new static(
			$container->get( 'invoker' ),
			$container->get( 'renderer.file' ),
			$container->get( 'renderer.callable' ),
			$container->get( 'renderer.string' )
		);
	}

}
