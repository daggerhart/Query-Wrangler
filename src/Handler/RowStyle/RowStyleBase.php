<?php

namespace QueryWrangler\Handler\RowStyle;

use Kinglet\Template\RendererInterface;

abstract class RowStyleBase implements RowStyleInterface {

	/**
	 * @var RendererInterface
	 */
	protected $fileRenderer;

	/**
	 * @var RendererInterface
	 */
	protected $stringRenderer;

	/**
	 * @var RendererInterface
	 */
	protected $callableRenderer;

	/**
	 * {@inheritDoc}
	 */
	public function setFileRenderer( RendererInterface $renderer ) {
		$this->fileRenderer = $renderer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setStringRenderer( RendererInterface $renderer ) {
		$this->stringRenderer = $renderer;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setCallableRenderer( RendererInterface $renderer ) {
		$this->callableRenderer = $renderer;
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
