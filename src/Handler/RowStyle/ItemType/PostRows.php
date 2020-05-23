<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleBase;
use QueryWrangler\Query\QwQuery;

class PostRows extends RowStyleBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'posts';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Posts', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( '', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm() {
		// TODO: Implement settingsForm() method.
	}

	/**
	 * @inheritDoc
	 */
	public function render( QwQuery $qw_query, QueryInterface $entity_query, HandlerTypeManagerInterface $field_type_manager ) {
		$display = $qw_query->getDisplay();
		$grouped_rows = [];
		$current_post_id = get_the_ID();
		$group_by = isset( $display['field_settings']['group_by_field'] ) ? $display['field_settings']['group_by_field'] : NULL;
		$i = 0;

		$entity_query->execute( function( $item ) use ( $qw_query, $display, $current_post_id, $group_by, &$grouped_rows, &$i ) {
			/** @var TypeInterface $item */
			$row = [
				'row_classes' => [],
				'fields' => [],
			];
			$field_classes = [ 'query-post-wrapper' ];

			// add class for active menu trail
			if ( is_singular() && get_the_ID() === $current_post_id ) {
				$field_classes[] = 'active-item';
			}

			$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );

			// @todo - replace TW rendering
			$row['fields'][ $i ]['output']  = theme( 'query_display_rows', [
				'template' => 'query-' . $display['post_settings']['size'],
				'slug'     => $qw_query->slug(),
				'style'    => $display['post_settings']['size'],
			] );
			$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];


			// can't really group posts row style
			$grouped_rows[ $i ][ $i ] = $row;
			$i += 1;
		} );

		// Flatten and add classes.
		$rows = $this->flattenGroupedRows( $grouped_rows, $group_by );
		$last_row = count( $rows ) -1;
		foreach ( $rows as $i => $row ) {
			$classes = array_merge( $rows[ $i ]['row_classes'], $this->rowClasses( $i, $last_row ) );
			$rows[ $i ]['row_classes'] = implode( ' ', $classes );
		}

		return $rows;
	}

}
