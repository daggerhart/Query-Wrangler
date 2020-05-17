<?php

namespace QueryWrangler\Admin\Page;

use Kinglet\Admin\Messenger;
use Kinglet\Admin\PageBase;
use Kinglet\Form\FormFactory;
use QueryWrangler\PostType\Query;

class Import extends PageBase {

    /**
     * @var FormFactory
     */
    protected $formFactory;

	/**
	 * Import constructor.
	 *
	 * @param FormFactory $form_factory
	 * @param Messenger $messenger
	 */
    public function __construct( FormFactory $form_factory, Messenger $messenger ) {
        $this->formFactory = $form_factory;
        $this->messenger = $messenger;
	    parent::__construct();
    }

	/**
	 * @inheritDoc
	 */
	function title() {
		return __( 'Import', 'query-wrangler' );
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
		return 'qw-import';
	}

	/**
	 * @inheritDoc
	 */
	public function parentSlug() {
		return 'edit.php?post_type=qw_query';
	}

    /**
     * @inheritDoc
     */
	public function actions() {
        return [
            'import_query' => [ $this, 'importQuery' ],
        ];
    }

    /**
	 * @inheritDoc
	 */
	function content() {
        print $this->form()->render();
	}

	public function form() {
	    return $this->formFactory->create( [
	        'form_prefix' => 'qw-import',
            'action' => $this->actionPath( 'import_query' ),
            'fields' => [
                'query_name' => [
                    'title' => __( 'Query Name', 'query-wrangler' ),
                    'description' => __( 'Optionally override the name of this query during import.', 'query-wrangler' ),
                    'type' => 'text',
                    'value' => '',
                ],
                'query_json' => [
                    'title' => __( 'Query JSON', 'query-wrangler' ),
                    'type' => 'code_editor',
                    'value' => '',
                    'editor_settings' => [
                        'type' => 'application/json',
                    ],
                ],
                'submit' => [
                    'type' => 'submit',
                    'value' => __( 'Import', 'query-wrangler' ),
                    'class' => [ 'button', 'button-primary' ],
                ]
            ]
        ] );
    }

    public function importQuery() {
	    $this->validateAction();
        $submitted = $this->form()->getSubmittedValues();
        if ( !empty( $submitted['query_json'] ) ) {
            $data = $this->decodeImport( $submitted['query_json'] );
            if ( is_array( $data ) ) {
                $post_id = wp_insert_post( [
                    'post_type' => Query::SLUG,
                    'post_title' => !empty( $submitted['query_name'] ) ? $submitted['query_name'] : $data['name'],
	                'post_name' => $data['slug'],
                ] );
                update_post_meta( $post_id, 'query_data', $data );
                return $this->result( __( 'Query imported.' ), '/wp-admin/post.php?post=' .$post_id.'&action=edit' );
            }
        }
    }

    /**
     * @param $string
     * @return mixed
     */
    public function decodeImport( $string ) {
        $string = stripslashes( $string );
        $data = json_decode( $string, TRUE );
        if ( is_array( $data ) && isset( $data['display']['field_settings']['fields'] ) ) {
            $fields = &$data['display']['field_settings']['fields'];

            foreach( $fields as $field_name => $field ) {
                $fields[ $field_name ]['custom_output'] = htmlspecialchars_decode( $field['custom_output'] );
                $fields[ $field_name ]['empty_field_content'] = htmlspecialchars_decode( $field['empty_field_content'] );
            }
        }

        return $data;
    }

}
