<?php

defined( 'ABSPATH' ) || exit;

class CookiesWithEase_Post_Type
{

	public function __construct()
	{

		add_action( 'init', array( $this, 'register_custom_post_type' ) );

	}

	public function register_custom_post_type()
	{

		$labels = array(
			'name'					=> __('Cookies with Ease','cookies-with-ease'),
			'menu_name'				=> __('Cookies w/ Ease','cookies-with-ease'),
	        'all_items'             => __('Applications','cookies-with-ease'),
			'singular_name'			=> __('Application','cookies-with-ease'),
			'add_new'				=> __('Add New','cookies-with-ease'),
			'add_new_item'			=> __('Add New Application','cookies-with-ease'),
			'edit_item'				=> __('Edit Application','cookies-with-ease'),
			'new_item'				=> __('New Application','cookies-with-ease'),
			'view_item'				=> __('View Application','cookies-with-ease'),
			'search_items'			=> __('Search Applications','cookies-with-ease'),
			'not_found'				=> __('Nothing found','cookies-with-ease'),
			'not_found_in_trash'	=> __('Nothing found in Trash','cookies-with-ease'),
			'parent_item_colon'		=> ''
		);
		$args = array(
			'labels'				=> $labels,
			'public'				=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'show_ui'				=> true,
			'query_var'				=> true,
			'rewrite'				=> true,

			'capabilities' => array(
				'publish_posts' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'delete_posts' => 'manage_options',
				'delete_others_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
				'edit_post' => 'manage_options',
				'delete_post' => 'manage_options',
				'read_post' => 'manage_options',
			),
			'hierarchical'			=> false,
			'menu_position'			=> 81,
			'menu_icon'				=> 'dashicons-shield-alt',
			'supports'				=> array( 'title' )
		);
		register_post_type('cookieswithease_app', $args );
	}

}
