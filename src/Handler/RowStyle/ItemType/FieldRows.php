<?php

namespace QueryWrangler\Handler\RowStyle\ItemType;

use Kinglet\Entity\QueryInterface;
use Kinglet\Entity\TypeInterface;
use QueryWrangler\Handler\Field\FieldInterface;
use QueryWrangler\Handler\HandlerTypeManagerInterface;
use QueryWrangler\Handler\RowStyle\RowStyleBase;
use QueryWrangler\Query\QwQuery;

class FieldRows extends RowStyleBase {

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
	public function queryTypes() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function render( QwQuery $qw_query, QueryInterface $entity_query, HandlerTypeManagerInterface $field_type_manager ) {
		$display = $qw_query->getDisplay();
		$fields = $qw_query->getFields();
		$row_style_settings = $qw_query->getRowStyle();
		$grouped_rows = [];
		$tokens = [];
		$current_post_id = get_the_ID();
		$group_by = isset( $row_style_settings['group_by_field'] ) ? $row_style_settings['group_by_field'] : NULL;
		$i = 0;

		// sort according to weights
		uasort( $fields, 'qw_cmp' );

		$entity_query->execute( function( $item ) use ( $field_type_manager, $fields, $display, $row_style_settings, $current_post_id, $group_by, &$grouped_rows, &$tokens, &$i ) {
			/** @var TypeInterface $item */
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
				// @todo - context wrangling
				if ( is_singular() && get_the_ID() === $current_post_id ) {
					$classes[] = 'active-item';
				}

				// add additional classes defined in the field settings
				if ( ! empty( $settings['classes'] ) ) {
					$classes[] = $settings['classes'];
				}

				$row['fields'][ $name ]['output'] = '';
				$row['fields'][ $name ]['classes'] = implode( " ", $classes );

				if ( $field_type_manager->has( $settings['type'] ) ) {
					/** @var FieldInterface $field_type */
					$field_type = $field_type_manager->get( $settings['type'] );
					$row['fields'][ $name ]['output'] = $field_type->render( $item, $settings, $tokens );
				}

				// Handle field emptiness.
				$row['is_empty'] = empty( $row['fields'][ $name ]['output'] );
				if ( $row['is_empty'] && $settings['empty_field_content_enabled'] && $settings['empty_field_content'] ) {
					$row['fields'][ $name ]['output'] = $settings['empty_field_content'];
					$row['is_empty'] = FALSE;
				}

				// Create token for early replacement in rewrite_output.
				$tokens[ $name ] = $row['fields'][ $name ]['output'];

				// Look for rewrite output and replace tokens.
				// @todo - maybe the field type handles rewriting...?
				//       - actually it seems like a LOT of this could be a part of field type handling
				//       - but maybe it's best to keep all html heere... TBD
				if ( isset( $settings['rewrite_output'] ) ) {
					// replace tokens with results
					$settings['custom_output'] = $this->stringRenderer->render( $settings['custom_output'], $tokens );
					$row['fields'][ $name ]['output'] = $settings['custom_output'];
				}

				// apply link to field
				if ( isset( $settings['link'] ) ) {
					$row['fields'][ $name ]['output'] = '<a class="query-field-link" href="' . $item->url() . '">' . $row['fields'][ $name ]['output'] . '</a>';
				}

				// get default field label for tables
				$row['fields'][ $name ]['label'] = ( isset( $settings['has_label'] ) ) ? $settings['label'] : '';

				// apply labels to full style fields
				if ( isset( $settings['has_label'] ) && $display['style'] != 'table' ) {
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
				$tokens[ $name ] = $row['fields'][ $name ]['output'];

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
				if ( !empty( $row_style_settings['strip_group_by_field'] ) ) {
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

}
