<?php

namespace QueryWrangler\Handler\Sort;

use Kinglet\Invoker\InvokerInterface;
use Kinglet\Template\RendererInterface;

class LegacySort implements SortInterface {

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $hookKey;

	/**
	 * @var array
	 */
	protected $registration;

	/**
	 * @var InvokerInterface
	 */
	protected $invoker;

	/**
	 * @var RendererInterface
	 */
	protected $renderer;

	/**
	 * LegacyFilter constructor.
	 *
	 * @param string $type
	 * @param array $registration
	 */
	public function __construct( $type, array $registration ) {
		$this->registration = $registration;
		$this->type = !empty( $this->registration['type'] ) ? $this->registration['type'] : $type;
		$this->hookKey = $type;
	}

	/**
	 * @param InvokerInterface $invoker
	 */
	public function setInvoker( InvokerInterface $invoker ) {
		$this->invoker = $invoker;
	}

	/**
	 * @param RendererInterface $renderer
	 */
	public function setRenderer( RendererInterface $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * @inheritDoc
	 */
	public function type() {
		return $this->type;
	}

	public function hookKey() {
		return $this->hookKey;
	}

	/**
	 * @inheritDoc
	 */
	public function title() {
		return $this->registration['title'];
	}

	/**
	 * @inheritDoc
	 */
	public function description() {
		return $this->registration['description'];
	}

	/**
	 * @inheritDoc
	 */
	public function queryTypes() {
		return ['post'];
	}

	/**
	 * @inheritDoc
	 */
	public function orderByKey() {
		return !empty( $this->registration['orderby_key'] ) ? $this->registration['orderby_key'] : 'orderby';
	}

	/**
	 * @inheritDoc
	 */
	public function orderKey() {
		return !empty( $this->registration['order_key'] ) ? $this->registration['order_key'] : 'order';
	}

	/**
	 * @inheritDoc
	 */
	public function orderOptions() {
		if ( !empty( $this->registration['order_options'] ) ) {
			return $this->registration['order_options'];
		}

		return [
			'ASC' => __( 'Ascending', 'query-wrangler' ),
			'DESC' => __( 'Descending', 'query-wrangler' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function process( array $args, array $values ) {

		if ( !empty( $this->registration['query_args_callback'] ) && is_callable( $this->registration['query_args_callback'] ) ) {
			call_user_func_array( $this->registration['query_args_callback'], [
				'args' => &$args,
				'sort' => $values,
			] );

			return $args;
		}

		// Default pattern.
		$args[ $this->orderByKey() ] = $this->type;
		$args[ $this->orderKey() ] = $values['order_value'];

		return $args;
	}

	/**
	 * @inheritDoc
	 */
	public function settingsForm( array $sort ) {
		if ( empty( $this->registration['form_callback'] ) || !is_callable( $this->registration['form_callback'] ) ) {
			$this->registration['form_callback'] = [ $this, 'legacyDefaultForm' ];
		}
		return $this->renderer->render( $this->registration['form_callback'], [
			'sort' => $sort,
		] );
	}

	/**
	 * Default sort options template.
	 * @param array $sort
	 */
	protected function legacyDefaultForm( array $sort ) {
		if ( !empty( $sort['order_options'] ) ) {
			?>
			<label class="qw-label" for="sort--<?php echo esc_attr( $this->type ) ?>">
				<?php _e('Order by', 'query-wrangler' )?>
				<?php echo $sort['title']; ?> :
			</label>
			<select class='qw-js-title' id="sort--<?php echo esc_attr( $this->type ) ?>"
			        name="<?php echo esc_attr( $sort['form_prefix'] ); ?>[order_value]">
				<?php foreach ( $sort['order_options'] as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ); ?>"
						<?php selected( $sort['values']['order_value'], $value ) ?>>
						<?php echo $label; ?>
					</option>
				<?php } ?>
			</select>
			<p class="description"><?php _e( 'Select how to order the results.', 'query-wrangler' ) ?></p>
			<?php
		}
	}
}
