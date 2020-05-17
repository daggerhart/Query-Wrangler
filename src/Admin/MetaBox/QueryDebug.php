<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Container\ContainerInterface;
use QueryWrangler\Query\QwQuery;

class QueryDebug extends MetaBoxBase {

    /**
     * @var QwQuery
     */
    protected $query;

	/**
	 * @var ContainerInterface
	 */
    protected $container;

	/**
	 * Preview constructor.
	 *
	 * @param string|string[] $post_types
	 * @param ContainerInterface $container
	 */
    public function __construct( $post_types, ContainerInterface $container ) {
        parent::__construct( $post_types );
        $this->container = $container;
    }

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'query-debug';
	}

	/**
	 * {@inheritdoc}
	 */
	public function title() {
		return __( 'Debug', 'query-wrangler' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( $post ) {
		try {
			$is_new = empty( $post->post_title );
			$this->query = new QwQuery( $post );
			$queryProcessor = $this->container->get('query.processor');
			echo $queryProcessor->execute( $this->query );
			dump('end of debug metabox');
		}
		catch (\Exception $e) {
			print "<pre>";
			dump($e->getMessage());
			print "</pre>";
		}

	}

}
