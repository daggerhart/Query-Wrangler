<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleBase;
use QueryWrangler\Query\QwQuery;

class TemplatePartRows extends RowStyleBase {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'template_part';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Template Part', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Use get_template_part() to have a theme template output the query rows.', 'query-wrangler' );
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
		$row_style_settings = $qw_query->getRowStyle();
		$grouped_rows = [];
		$current_post_id = get_the_ID();
		$i = 0;

		$entity_query->execute( function( $item ) use ( $qw_query, $row_style_settings, $current_post_id, &$grouped_rows, &$i ) {
			$path = $row_style_settings['path'];
			$name = $row_style_settings['name'];
			$row = [
				'row_classes' => [],
				'fields' => [],
			];
			$field_classes = [
				'query-post-wrapper'
			];

			// add class for active menu trail
			if ( is_singular() && get_the_ID() === $current_post_id ) {
				$field_classes[] = 'active-item';
			}

			ob_start();
				get_template_part( $path, $name );
			$output = ob_get_clean();

			$row['fields'][ $i ]['classes'] = implode( " ", $field_classes );
			$row['fields'][ $i ]['output'] = $output;
			$row['fields'][ $i ]['content'] = $row['fields'][ $i ]['output'];

			// can't really group posts row style
			$groups[ $i ][ $i ] = $row;
			$i ++;
		} );

		// Flatten and add classes.
		$rows = $this->flattenGroupedRows( $grouped_rows );
		$last_row = count( $rows ) -1;
		foreach ( $rows as $i => $row ) {
			$classes = array_merge( $rows[ $i ]['row_classes'], $this->rowClasses( $i, $last_row ) );
			$rows[ $i ]['row_classes'] = implode( ' ', $classes );
		}

		return $rows;
	}

}
