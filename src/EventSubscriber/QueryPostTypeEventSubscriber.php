<?php

namespace QueryWrangler\EventSubscriber;

use Kinglet\Container\ContainerInterface;
use Kinglet\Registry\Registry;
use Kinglet\Registry\RegistryInterface;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use QueryWrangler\Service\QueryProcessor;
use WP_Post;

class QueryPostTypeEventSubscriber {

	/**
	 * @var QueryProcessor
	 */
	protected $queryProcessor;

	/**
	 * @var RegistryInterface
	 */
	protected $processedQueryEntities;

	/**
	 * QueryPostTypeEventSubscriber constructor.
	 *
	 * @param QueryProcessor $query_processor
	 */
	private function __construct( QueryProcessor $query_processor ) {
		$this->queryProcessor = $query_processor;
		$this->processedQueryEntities = new Registry();
	}

	/**
	 * @param ContainerInterface $container
	 */
	public static function subscribe( ContainerInterface $container ) {
		$self = new static(
			$container->get( 'query.processor' )
		);

		add_action( 'init', [ $self, 'actionInit' ] );
		add_action( 'the_title', [ $self, 'actionTheTitle' ], 100 );
		add_action( 'the_content', [ $self, 'actionTheContent' ],100 );
	}

	/**
	 * @param int|WP_Post $post_id
	 *
	 * @return QueryPostEntity
	 */
	protected function processQuery( $post_id ) {
		$query_post_entity = QueryPostEntity::load( $post_id );

		$this->queryProcessor->execute( $query_post_entity );
		$this->processedQueryEntities->set( $post_id, $query_post_entity );

		return $query_post_entity;
	}

	/**
	 * Register Query post type.
	 */
	public function actionInit() {
		QueryPostType::register();
	}

	/**
	 * When displaying QueryPostType on the frontend, provide processed
	 * query as the_title() for the post.
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function actionTheTitle( $title ) {
		if ( !is_admin() && get_post_type() == QueryPostType::SLUG ) {
			$query_post_entity = $this->processedQueryEntities->get( get_the_ID() );
			if ( !$query_post_entity ) {
				$query_post_entity = $this->processQuery( get_the_ID() );
			}
			$title = $query_post_entity->getRendered( 'title' );
		}

		return $title;
	}

	/**
	 * When displaying QueryPostType on the frontend, provide processed
	 * query as the_content() for the post.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function actionTheContent( $content ) {
		if ( !is_admin() && get_post_type() == QueryPostType::SLUG ) {
			$query_post_entity = $this->processedQueryEntities->get( get_the_ID() );
			if ( !$query_post_entity ) {
				$query_post_entity = $this->processQuery( get_the_ID() );
			}
			$content = $query_post_entity->getRendered( 'wrapper' );
		}

		return $content;
	}

}
