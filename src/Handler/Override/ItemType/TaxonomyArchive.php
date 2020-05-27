<?php

namespace QueryWrangler\Handler\Override\ItemType;

use QueryWrangler\Handler\Override\OverrideContextInterface;
use QueryWrangler\Handler\Override\OverrideTypeBase;
use QueryWrangler\QueryPostEntity;
use QueryWrangler\QueryPostType;
use WP_Query;
use WP_Term;

class TaxonomyArchive extends OverrideTypeBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'taxonomies';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Taxonomy Archive', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Override term archive pages for an entire taxonomy.', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function findOverrideEntity( WP_Query $wp_query ) {
		if ( $wp_query->is_archive() && ( $wp_query->is_tag() || $wp_query->is_category() || $wp_query->is_tag() ) ) {
			/** @var WP_Term $term */
			$term = $wp_query->get_queried_object();
			$posts = get_posts( [
				'post_type' => QueryPostType::SLUG,
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'meta_query' => [
					'query_override_'. $this->type() => [
						// @todo - implement this meta_key
						'key' => 'query_override_'. $this->type(),
						'value' => $term->taxonomy,
					],
				],
			] );

			if ( count( $posts ) ) {
				return QueryPostEntity::load( $posts[0] );
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function overrideEntity( QueryPostEntity $entity, OverrideContextInterface $override_context ) {
		/** @var WP_Term $term */
		$term = $override_context->getOriginalQueriedObject();

		// @todo - This is how 1.x did it. decide if this is the right NEW way
		$entity->addFilter( 'query_override_' . $this->type(), 'taxonomy_' . $term->taxonomy, [
			'terms'            => [ $term->term_id => $term->name ],
			'operator'         => 'IN',
			'include_children' => TRUE,
		] );
		$entity->setRendered( 'title', single_term_title( '', false ) );
	}

}
