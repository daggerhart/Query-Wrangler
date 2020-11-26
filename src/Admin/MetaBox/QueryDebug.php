<?php

namespace QueryWrangler\Admin\MetaBox;

use Kinglet\Admin\MetaBoxBase;
use Kinglet\Container\ContainerInterface;
use QueryWrangler\QueryPostEntity;

class QueryDebug extends MetaBoxBase {

    /**
     * @var QueryPostEntity
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
	 */
    public function __construct( $post_types ) {
        parent::__construct( $post_types );
        global $query_wrangler_loader;
        $this->container = $query_wrangler_loader->getContainer();
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
			$this->query = new QueryPostEntity( $post );
			//dump($this->query);
			$queryProcessor = $this->container->get('query.processor');
			echo $queryProcessor->execute( $this->query );
			$this->d('end of debug metabox');
		}
		catch (\Exception $e) {
			print "<pre>";
			$this->d($e->getMessage());
			print "</pre>";
		}

	}

}
