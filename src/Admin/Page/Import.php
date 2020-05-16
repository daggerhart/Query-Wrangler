<?php

namespace QueryWrangler\Admin\Page;

use Kinglet\Admin\PageBase;

class Settings extends PageBase {

	/**
	 * @inheritDoc
	 */
	function title() {
		return __( 'Settings', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	function description() {
		return __( '', 'query-wrangler' );
	}

	/**
	 * @inheritDoc
	 */
	function slug() {
		return 'qw-settings';
	}

	/**
	 * @inheritDoc
	 */
	function content() {

	}
}
