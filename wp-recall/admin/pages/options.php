<?php

global $rcl_options, $wpdb;

require_once RCL_PATH . 'admin/classes/class-rcl-options-manager.php';

//needed for the working of old cases
require_once RCL_PATH . 'classes/class-rcl-options.php';

rcl_font_awesome_style();

wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_style( 'wp-jquery-ui-dialog' );

$rcl_options = get_site_option( 'rcl_global_options' );

$pages = rcl_get_pages_ids();

$options = new Rcl_Options_Manager( [
	'option_name'  => 'rcl_global_options',
	'page_options' => 'rcl-options',
	'extends'      => true,
] );

$options->add_box( 'primary', [
	'title' => __( 'General settings', 'wp-recall' ),
	'icon'  => 'fa-cogs',
] )->add_group( 'office', [
	'title'  => __( 'Personal cabinet', 'wp-recall' ),
	'extend' => true,
] )->add_options( [
	$options->add_box( 'primary', [
		'title' => __( 'General settings', 'wp-recall' ),
		'icon'  => 'fa-cogs',
	] )->add_group( 'office', [
		'title'  => __( 'Personal cabinet', 'wp-recall' ),
		'extend' => true,
	] )->add_options( [
		[
			'type'      => 'select',
			'slug'      => 'view_user_lk_rcl',
			'title'     => __( 'Personal Cabinet output', 'wp-recall' ),
			'values'    => [
				__( 'On the author’s archive page', 'wp-recall' ),
				__( 'Using shortcode [wp-recall]', 'wp-recall' ),
			],
			'help'      => __( 'Attention! Changing this parameter is not required. '
			                   . 'Detailed instructions on personal account output using author.php '
			                   . 'file can be received here <a href="https://codeseller.ru/post-group/ustanovka-plagina-wp-recall-na-sajt/ " target="_blank">here</a>', 'wp-recall' ),
			'notice'    => __( 'If author archive page is selected, the template author.php should contain the code if(function_exists(\'wp_recall\')) wp_recall();', 'wp-recall' ),
			'childrens' => [
				1 => [
					[
						'type'   => 'select',
						'slug'   => 'lk_page_rcl',
						'title'  => __( 'Shortcode host page', 'wp-recall' ),
						'values' => $pages,
					],
					[
						'type'  => 'text',
						'slug'  => 'link_user_lk_rcl',
						'title' => __( 'Link format to personal account', 'wp-recall' ),
						'help'  => __( 'The link is formed according to principle "/slug_page/?get=ID". The parameter "get" can be set here. By default user', 'wp-recall' ),
					],
				],
			],
		],
		[
			'type'      => 'runner',
			'slug'      => 'timeout',
			'value_min' => 1,
			'value_max' => 20,
			'default'   => 10,
			'help'      => __( 'This value sets the maximum time a user is considered "online" in the absence of activity', 'wp-recall' ),
			'title'     => __( 'Inactivity timeout', 'wp-recall' ),
			'notice'    => __( 'Specify the time in minutes after which the user will be considered offline if you did not show activity on the website. The default is 10 minutes.', 'wp-recall' ),
		],
	] ),
] );

$options->box( 'primary' )->add_group( 'security', [
	'title'  => __( 'Security', 'wp-recall' ),
	'extend' => true,
] )->add_options( [
	[
		'type'     => 'password',
		'required' => 1,
		'slug'     => 'security-key',
		'title'    => __( 'The key of security for ajax-requests and other', 'wp-recall' ),
	],
] );

$options->box( 'primary' )->add_group( 'design', [
	'title' => __( 'Design', 'wp-recall' ),
] )->add_options( [
	[
		'type'    => 'color',
		'slug'    => 'primary-color',
		'title'   => __( 'Primary color', 'wp-recall' ),
		'default' => '#4C8CBD',
	],
	[
		'type'   => 'select',
		'slug'   => 'buttons_place',
		'title'  => __( 'The location of the section buttons', 'wp-recall' ),
		'values' => [
			__( 'Top', 'wp-recall' ),
			__( 'Left', 'wp-recall' ),
		],
	],
	[
		'type'      => 'radio',
		'slug'      => 'rcl_hide_avatar',
		'title'     => __( 'Disable avatar uploader in personal account?', 'wp-recall' ),
		'values'    => [ __( 'No', 'wp-recall' ), __( 'Yes', 'wp-recall' ) ],
		'default'   => 0,
		'childrens' => [
			0 => [
				[
					'type'       => 'uploader',
					'temp_media' => 1,
					'multiple'   => 0,
					'crop'       => 1,
					'filetitle'  => 'rcl-default-avatar',
					'filename'   => 'rcl-default-avatar',
					'slug'       => 'default_avatar',
					'title'      => __( 'Default avatar', 'wp-recall' ),
				],
			],
		],
	],
] );

$options->box( 'primary' )->add_group( 'usersign', [
	'title' => __( 'Login and register', 'wp-recall' ),
] )->add_options( [
	[
		'type'      => 'select',
		'slug'      => 'login_form_recall',
		'title'     => __( 'The order of output the form of login and registration', 'wp-recall' ),
		'values'    => [
			__( 'Floating form', 'wp-recall' ),
			__( 'On a separate page', 'wp-recall' ),
			__( 'Wordpress Forms', 'wp-recall' ),
			__( 'Widget form', 'wp-recall' ),
		],
		'notice'    => __( 'The form of login and registration of the plugin can be outputed with help of widget "Control panel" '
		                   . 'and a shortcode [loginform], but you can use the standart login form of WordPress also', 'wp-recall' ),
		'childrens' => [
			1 => [
				[
					'type'   => 'select',
					'slug'   => 'page_login_form_recall',
					'title'  => __( 'ID of the shortcode page [loginform]', 'wp-recall' ),
					'values' => $pages,
				],
			],
		],
	],
	[
		'type'   => 'select',
		'slug'   => 'confirm_register_recall',
		'help'   => __( 'If you are using the registration confirmation, after registration, the user will need to confirm your email by clicking on the link in the sent email', 'wp-recall' ),
		'title'  => __( 'Registration confirmation by the user', 'wp-recall' ),
		'values' => [
			__( 'Not used', 'wp-recall' ),
			__( 'Used', 'wp-recall' ),
		],
	],
	[
		'type'      => 'select',
		'slug'      => 'authorize_page',
		'title'     => __( 'Redirect user after login', 'wp-recall' ),
		'values'    => [
			__( 'The user profile', 'wp-recall' ),
			__( 'Current page', 'wp-recall' ),
			__( 'Arbitrary URL', 'wp-recall' ),
		],
		'childrens' => [
			2 => [
				[
					'type'   => 'text',
					'slug'   => 'custom_authorize_page',
					'title'  => __( 'URL', 'wp-recall' ),
					'notice' => __( 'Enter your URL below, if you select an arbitrary URL after login', 'wp-recall' ),
				],
			],
		],
	],
	[
		'type'   => 'select',
		'slug'   => 'repeat_pass',
		'title'  => __( 'repeat password field', 'wp-recall' ),
		'values' => [ __( 'Disabled', 'wp-recall' ), __( 'Displaye', 'wp-recall' ) ],
	],
	[
		'type'   => 'select',
		'slug'   => 'difficulty_parole',
		'title'  => __( 'Indicator of password complexity', 'wp-recall' ),
		'values' => [ __( 'Disabled', 'wp-recall' ), __( 'Displaye', 'wp-recall' ) ],
	],
] );

$options->box( 'primary' )->add_group( 'recallbar', [
	'title' => __( 'Recallbar', 'wp-recall' ),
] )->add_options( [
	[
		'type'      => 'select',
		'slug'      => 'view_recallbar',
		'title'     => __( 'Output of recallbar panel', 'wp-recall' ),
		'help'      => __( 'Recallbar – is he top panel WP-Recall plugin through which the plugin and its add-ons can output their data and the administrator can make his menu, forming it on <a href="/wp-admin/nav-menus.php" target="_blank">page management menu of the website</a>', 'wp-recall' ),
		'values'    => [ __( 'Disabled', 'wp-recall' ), __( 'Enabled', 'wp-recall' ) ],
		'childrens' => [
			'rcb_color',
		],
	],
	[
		'parent' => [
			'id'    => 'view_recallbar',
			'value' => 1,
		],
		'type'   => 'select',
		'slug'   => 'rcb_color',
		'title'  => __( 'Color', 'wp-recall' ),
		'values' => [ __( 'Default', 'wp-recall' ), __( 'Primary colors of WP-Recall', 'wp-recall' ) ],
	],
] );

$options->box( 'primary' )->add_group( 'caching', [
	'title'  => __( 'Caching', 'wp-recall' ),
	'extend' => true,
] )->add_options( [
	[
		'type'      => 'select',
		'slug'      => 'use_cache',
		'title'     => __( 'Cache', 'wp-recall' ),
		'help'      => __( 'Use the functionality of the caching WP-Recall plugin. <a href="https://codeseller.ru/post-group/funkcional-keshirovaniya-plagina-wp-recall/" target="_blank">read More</a>', 'wp-recall' ),
		'values'    => [
			__( 'Disabled', 'wp-recall' ),
			__( 'Enabled', 'wp-recall' ),
		],
		'childrens' => [
			'cache_time',
			'cache_output',
		],
	],
	[
		'parent'     => [
			'id'    => 'use_cache',
			'value' => 1,
		],
		'type'       => 'number',
		'slug'       => 'cache_time',
		'default'    => 3600,
		'latitlebel' => __( 'Time cache (seconds)', 'wp-recall' ),
		'notice'     => __( 'Default', 'wp-recall' ) . ': 3600',
	],
	[
		'parent' => [
			'id'    => 'use_cache',
			'value' => 1,
		],
		'type'   => 'select',
		'slug'   => 'cache_output',
		'title'  => __( 'Cache output', 'wp-recall' ),
		'values' => [
			__( 'All users', 'wp-recall' ),
			__( 'Only guests', 'wp-recall' ),
		],
	],
	[
		'type'   => 'select',
		'slug'   => 'minify_css',
		'title'  => __( 'Minimization of file styles', 'wp-recall' ),
		'values' => [
			__( 'Disabled', 'wp-recall' ),
			__( 'Enabled', 'wp-recall' ),
		],
		'notice' => __( 'Minimization of file styles only works in correlation with WP-Recall style files and add-ons that support this feature', 'wp-recall' ),
	],
	[
		'type'   => 'select',
		'slug'   => 'minify_js',
		'title'  => __( 'Minimization of scripts', 'wp-recall' ),
		'values' => [
			__( 'Disabled', 'wp-recall' ),
			__( 'Enabled', 'wp-recall' ),
		],
	],
] );

$options->box( 'primary' )->add_group( 'access_console', [
	'title' => __( 'Access to the console', 'wp-recall' ),
] )->add_options( [
	[
		'type'    => 'select',
		'default' => 7,
		'slug'    => 'consol_access_rcl',
		'title'   => __( 'Access to the console is allowed', 'wp-recall' ),
		'values'  => [
			10 => __( 'only Administrators', 'wp-recall' ),
			7  => __( 'Editors and higher', 'wp-recall' ),
			2  => __( 'Authors and higher', 'wp-recall' ),
			1  => __( 'Participants and higher', 'wp-recall' ),
			0  => __( 'All users', 'wp-recall' ),
		],
	],
] );

$options->box( 'primary' )->add_group( 'logging', [
	'title'  => __( 'Logging mode', 'wp-recall' ),
	'extend' => true,
] )->add_options( [
	[
		'type'   => 'select',
		'slug'   => 'rcl-log',
		'title'  => __( 'Write background events and errors to the log-file', 'wp-recall' ),
		'values' => [
			__( 'Disabled', 'wp-recall' ),
			__( 'Enabled', 'wp-recall' ),
		],
	],
] );

/* support old options */
global $rclOldOptionData;

apply_filters( 'admin_options_wprecall', '' );

if ( $rclOldOptionData ) {

	foreach ( $rclOldOptionData as $box_id => $box ) {

		if ( ! $box['groups'] ) {
			continue;
		}

		$options->add_box( $box_id, [
			'title' => $box['title'],
		] );

		foreach ( $box['groups'] as $k => $group ) {

			$options->box( $box_id )->add_group( $k, [
				'title' => $group['title'],
			] )->add_options( $group['options'] );
		}
	}
}

unset( $rclOldOptionData );
/* * * */

$options = apply_filters( 'rcl_options', $options );

$content = '<h2>' . __( 'Configure WP-Recall plugin and add-ons', 'wp-recall' ) . '</h2>';

$content .= $options->get_content();

echo wp_kses( $content, rcl_kses_allowed_html() );
