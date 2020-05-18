<?php

namespace QueryWrangler\Display\RowStyle;

use Kinglet\Entity\QueryInterface;
use QueryWrangler\Query\QwQuery;

class FieldRows implements RowStyleInterface {

	/**
	 * @inheritDoc
	 */
	public function type() {
		return 'fields';
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return __( 'Fields', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return __( 'Define a set of fields that will make up the rows within the query.', 'query-wrangler' );
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
	public function render( QwQuery $qw_query, QueryInterface $entity_query ) {

	}

	public function renderRows( QwQuery $qw_query, QueryInterface $entity_query ) {
		$display = $qw_query->getDisplay();
		$fields = $qw_query->getFields();
		$grouped_rows = [];
		$tokens = [];
		$current_post_id = get_the_ID();
		$group_by = isset( $display['field_settings']['group_by_field'] ) ? $display['field_settings']['group_by_field'] : NULL;
		$i = 0;

		// @todo - sort fields according to weight
		$entity_query->execute( function( $item ) use ( $fields, $display, $current_post_id, $group_by, &$grouped_rows, &$tokens, &$i ) {
			$row = [
				'row_classes' => [],
				'fields' => [],
			];

			foreach ( $fields as $name => $settings ) {
				if ( ! isset( $settings['empty_field_content_enabled'] ) ) {
					$settings['empty_field_content_enabled'] = FALSE;
					$settings['empty_field_content'] = '';
				}

				$classes = [
					'query-field',
					'query-field-' . $settings['name'],
				];

				// add class for active menu trail
				if ( is_singular() && get_the_ID() === $current_post_id ) {
					$classes[] = 'active-item';
				}

				// add additional classes defined in the field settings
				if ( ! empty( $settings['classes'] ) ) {
					$classes[] = $settings['classes'];
				}

				$row['fields'][ $name ]['output'] = '';
				$row['fields'][ $name ]['classes'] = implode( " ", $classes );

				// @todo - field type renders itself (prev "output_callback"

				// @todo ... end field type work

				// Handle field emptiness.
				$row['is_empty'] = empty( $row['fields'][ $name ]['output'] );
				if ( $row['is_empty'] && $settings['empty_field_content_enabled'] && $settings['empty_field_content'] ) {
					$row['fields'][ $name ]['output'] = $settings['empty_field_content'];
					$row['is_empty'] = FALSE;
				}

				// Create token for early replacement in rewrite_output.
				$tokens[ '{{' . $name . '}}' ] = $row['fields'][ $name ]['output'];

				// Look for rewrite output and replace tokens.
				// @todo - maybe the field type handles rewriting...?
				//       - actually it seems like a LOT of this could be a part of field type handling
				//       - but maybe it's best to keep all html heere... TBD
				if ( isset( $settings['rewrite_output'] ) ) {
					// replace tokens with results
					$settings['custom_output'] = str_replace( array_keys( $tokens ), array_values( $tokens ), $settings['custom_output'] );
					$row['fields'][ $name ]['output'] = $settings['custom_output'];
				}

				// apply link to field
				if ( isset( $settings['link'] ) ) {
					$row['fields'][ $name ]['output'] = '<a class="query-field-link" href="' . get_permalink() . '">' . $row['fields'][ $name ]['output'] . '</a>';
				}

				// get default field label for tables
				$row['fields'][ $name ]['label'] = ( isset( $settings['has_label'] ) ) ? $settings['label'] : '';

				// apply labels to full style fields
				if ( isset( $settings['has_label'] ) && $display['row_style'] != 'posts' && $display['style'] != 'table' ) {
					$row['fields'][ $name ]['output'] = '<label class="query-label">' . $settings['label'] . '</label> ' . $row['fields'][ $name ]['output'];
				}
				// the_content filter
				if ( isset( $settings['apply_the_content'] ) ) {
					$row['fields'][ $name ]['output'] = apply_filters( 'the_content', $row['fields'][ $name ]['output'] );
				} else {
					// apply shortcodes to field output
					$row['fields'][ $name ]['output'] = do_shortcode( $row['fields'][ $name ]['output'] );
				}

				// update the token for replacement by later fields
				$tokens[ '{{' . $name . '}}' ] = $row['fields'][ $name ]['output'];

				// save a copy of the field output in case it is excluded, but we need it later
				$row['fields'][ $name ]['content'] = $row['fields'][ $name ]['output'];

				// hide if empty
				$row['hide'] = ( isset( $settings['hide_if_empty'] ) && $row['is_empty'] );

				// after all operations, remove if excluded
				if ( isset( $settings['exclude_display'] ) || $row['hide'] ) {
					unset( $row['fields'][ $name ]['output'] );
				}
			}

			// Hash the output of the group_by field for matching between rows.
			$group_hash = md5( $i );
			if ( $group_by && isset( $row['fields'][ $group_by ] ) ) {
				// Clean up group by field.
				if ( !empty( $display['field_settings']['strip_group_by_field'] ) ) {
					$row['fields'][ $group_by ]['content'] = strip_tags( $row['fields'][ $group_by ]['content'] );
				}
				$group_by_field_content = $row['fields'][ $group_by ]['content'];
				$group_hash = md5( $group_by_field_content );
			}

			$grouped_rows[ $group_hash ][ $i ] = $row;
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

	/**
	 * @param int $i
	 * @param int $last
	 *
	 * @return array
	 */
	protected function rowClasses( $i, $last ) {
		$classes   = [
			'query-row',
			'query-row-' . $i,
		];
		$classes[] = ( $i % 2 ) ? 'query-row-odd' : 'query-row-even';

		if ( $i === 0 ){
			$classes[] = 'query-row-first';
		}
		else if ( $i === $last ){
			$classes[] = 'query-row-last';
		}

		return $classes;
	}

	/**
	 * @param array $grouped_rows
	 * @param string|null $group_by
	 *
	 * @return array
	 */
	protected function flattenGroupedRows( $grouped_rows, $group_by = NULL ) {
		$rows = [];

		foreach ( $grouped_rows as $group ) {
			$first_row = reset( $group );

			// group row
			if ( $group_by && isset( $first_row['fields'][ $group_by ] ) ) {

				// create the row that acts as the group header
				$rows[] = [
					'row_classes' => 'query-group-row',
					'fields' => [
						$group_by => [
							'classes' => 'query-group-row-field',
							'output' => $first_row['fields'][ $group_by ]['content']
						],
					],
				];
			}

			foreach ( $group as $row ) {
				$rows[] = $row;
			}
		}

		return $rows;
	}

}
