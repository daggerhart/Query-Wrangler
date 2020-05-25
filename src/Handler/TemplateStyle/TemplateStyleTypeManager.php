<?php

namespace QueryWrangler\Handler\TemplateStyle;

use QueryWrangler\Handler\HandlerItemTypeDiscoverableRegistry;
use QueryWrangler\Handler\HandlerTypeManagerBase;
use QueryWrangler\QueryPostEntity;

class TemplateStyleTypeManager extends HandlerTypeManagerBase {

	/**
	 * @var bool
	 */
	protected $typesRegistered = FALSE;

	/**
	 * {@inheritDoc}
	 * @return TemplateStyleInterface
	 */
	public function get( $key ) {
		return parent::get( $key );
	}

	/**
	 * {@inheritDoc}
	 */
	public function type() {
		return 'template_style';
	}

	/**
	 * {@inheritDoc}
	 */
	public function multiple() {
		return FALSE;
	}

	/**
	 * @inheritDoc
	 */
	public function getDataFromQuery( QueryPostEntity $query ) {
		return $query->getTemplateStyle();
	}

	/**
	 * {@inheritDoc}
	 */
	public function collect() {
		$this->collectLegacy();
		$this->collectTypes();
	}

	/**
	 * Collect legacy template types
	 */
	protected function collectLegacy() {
		$legacy = apply_filters( 'qw_styles', [] );
		foreach ( $legacy as $type => $item ) {
			$instance = new LegacyTemplateStyle( $type, $item );
			$instance->setFileRenderer( $this->fileRenderer );
			$this->set( $instance->type(), $instance );
		}
	}

	/**
	 * Collect all new item types for this handler type.
	 */
	protected function collectTypes() {
		if ( !$this->typesRegistered ) {
			$this->typesRegistered = TRUE;
			add_filter( "qw_handler_item_types--{$this->type()}", function( $sources ) {
				$sources['QueryWrangler\Handler\TemplateStyle\ItemType'] = QW_PLUGIN_DIR . '/src/Handler/TemplateStyle/ItemType';
				return $sources;
			} );
		}

		$items = new HandlerItemTypeDiscoverableRegistry(
			'QueryWrangler\Handler\TemplateStyle\TemplateStyleInterface',
			'type',
			"qw_handler_item_types--{$this->type()}"
		);

		foreach ( $items->all() as $type => $item ) {
			try {
				/** @var TemplateStyleInterface $instance */
				$instance = $items->getInstance( $type );
				$instance->setFileRenderer( $this->fileRenderer );
				$this->set( $instance->type(), $instance );
			}
			catch ( \ReflectionException $exception ) {}
		}
	}

}
