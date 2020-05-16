<?php

namespace QueryWrangler\PostType\MetaBox;

use QueryWrangler\PostType\QWQuery;

class Preview {

	static public function init() {
		$box = new self();
		add_action( 'add_meta_boxes', [ $box, 'add' ] );
		add_action( 'save_post_' . QWQuery::SLUG, [ $box, 'save' ] );
		return $box;
	}

	public function add() {
		add_meta_box(
			'query_preview',
			__( 'Preview' ),
			[ $this, 'render' ],
			QWQuery::SLUG
		);
	}

	public function render( $post ) {
		?>
		Hello
		<?php
		print $post->post_title;
	}

	public function save( $post_id ) {

	}
}
