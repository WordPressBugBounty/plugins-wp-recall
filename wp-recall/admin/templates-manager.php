<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

add_action( 'rcl_init_themes_manager', 'rcl_init_upload_template' );

class Rcl_Templates_Manager extends WP_List_Table {

	var $addon = [];
	var $template_number;
	var $addons_data = [];
	var $need_update = [];
	var $column_info = [];

	function __construct() {
		global $status, $page, $active_addons;

		parent::__construct( [
			'singular' => __( 'add-on', 'wp-recall' ),
			'plural'   => __( 'add-ons', 'wp-recall' ),
			'ajax'     => false,
		] );

		$this->need_update = get_site_option( 'rcl_addons_need_update' );
		$this->column_info = $this->get_column_info();

		add_action( 'admin_head', [ &$this, 'admin_header' ] );
	}

	function get_templates_data() {
		$paths = [ RCL_PATH . 'add-on', RCL_TAKEPATH . 'add-on' ];

		$add_ons = [];
		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				$addons = scandir( $path, 1 );

				foreach ( ( array ) $addons as $namedir ) {
					$addon_dir = $path . '/' . $namedir;
					$index_src = $addon_dir . '/index.php';
					if ( ! is_dir( $addon_dir ) || ! file_exists( $index_src ) ) {
						continue;
					}
					$info_src = $addon_dir . '/info.txt';
					if ( file_exists( $info_src ) ) {
						$info = file( $info_src );
						$data = rcl_parse_addon_info( $info );
						if ( ! isset( $data['template'] ) ) {
							continue;
						}
						if ( ! empty( $_POST['s'] ) ) {
							if ( strpos( strtolower( trim( $data['name'] ) ), trim( strtolower( sanitize_text_field( wp_unslash( $_POST['s'] ) ) ) ) ) !== false ) {
								$this->addons_data[ $namedir ]         = $data;
								$this->addons_data[ $namedir ]['path'] = $addon_dir;
							}
							continue;
						}
						$this->addons_data[ $namedir ]         = $data;
						$this->addons_data[ $namedir ]['path'] = $addon_dir;
					}
				}
			}
		}

		$this->template_number = count( $this->addons_data );
	}

	function get_addons_content() {
		global $active_addons;
		$add_ons = [];
		foreach ( $this->addons_data as $namedir => $data ) {
			$desc                      = $this->get_description_column( $data );
			$add_ons[ $namedir ]['ID'] = $namedir;
			if ( isset( $data['template'] ) ) {
				$add_ons[ $namedir ]['template'] = $data['template'];
			}
			$add_ons[ $namedir ]['addon_name']        = $data['name'];
			$add_ons[ $namedir ]['addon_path']        = $data['path'];
			$add_ons[ $namedir ]['addon_status']      = ( $active_addons && isset( $active_addons[ $namedir ] ) ) ? 1 : 0;
			$add_ons[ $namedir ]['addon_description'] = $desc;
		}

		return $add_ons;
	}

	function admin_header() {

		$page = ( isset( $_GET['page'] ) ) ? sanitize_key( $_GET['page'] ) : false;
		if ( 'manage-templates-recall' != $page ) {
			return;
		}

		echo '<style type="text/css">';
		echo '.wp-list-table .column-addon_screen { width: 200px; }';
		echo '.wp-list-table .column-addon_name { width: 15%; }';
		echo '.wp-list-table .column-addon_status { width: 10%; }';
		echo '.wp-list-table .column-addon_description { width: 70%;}';
		echo '</style>';
	}

	function no_items() {
		esc_html_e( 'No addons found.', 'wp-recall' );
	}

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'addon_screen':
				if ( file_exists( $item['addon_path'] . '/screenshot.jpg' ) ) {
					return '<img src="' . rcl_path_to_url( $item['addon_path'] . '/screenshot.jpg' ) . '">';
				}
				break;
			case 'addon_name':
				$name = ( isset( $item['template'] ) ) ? $item['addon_name'] : $item['addon_name'];

				return '<strong>' . $name . '</strong>';
			case 'addon_status':
				if ( $item[ $column_name ] ) {
					return __( 'Active', 'wp-recall' );
				} else {
					return __( 'Inactive', 'wp-recall' );
				}
			case 'addon_description':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function get_sortable_columns() {
		$sortable_columns = [
			'addon_name'   => [ 'addon_name', false ],
			'addon_status' => [ 'addon_status', false ],
		];

		return $sortable_columns;
	}

	function get_columns() {
		$columns = [
			'addon_screen'      => '',
			'addon_name'        => __( 'Templates', 'wp-recall' ),
			'addon_status'      => __( 'Status', 'wp-recall' ),
			'addon_description' => __( 'Description', 'wp-recall' ),
		];

		return $columns;
	}

	function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_key( $_GET['orderby'] ) : 'addon_name';
		$order   = ( ! empty( $_GET['order'] ) ) ? sanitize_key( $_GET['order'] ) : 'asc';
		$result  = strcmp( $a[ $orderby ], $b[ $orderby ] );

		return ( $order === 'asc' ) ? $result : - $result;
	}

	function column_addon_name( $item ) {

		$actions = [];

		if ( $item['addon_status'] != 1 ) {
			$page               = isset( $_REQUEST['page'] ) ? sanitize_key( $_REQUEST['page'] ) : '';
			$actions['delete']  = sprintf( '<a href="?page=%s&action=%s&template=%s">' . esc_html__( 'Delete', 'wp-recall' ) . '</a>', esc_attr( $page ), 'delete', esc_attr( $item['ID'] ) );
			$actions['connect'] = sprintf( '<a href="?page=%s&action=%s&template=%s">' . esc_html__( 'connect', 'wp-recall' ) . '</a>', esc_attr( $page ), 'connect', esc_attr( $item['ID'] ) );
		}

		return sprintf( '%1$s %2$s', '<strong>' . $item['addon_name'] . '</strong>', $this->row_actions( $actions ) );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="addons[]" value="%s" />', esc_attr( $item['ID'] )
		);
	}

	function get_description_column( $data ) {
		$content = '<div class="plugin-description">
                <p>' . $data['description'] . '</p>
            </div>
            <div class="active second plugin-version-author-uri">
            ' . esc_html__( 'Version', 'wp-recall' ) . ' ' . esc_attr( $data['version'] );
		if ( isset( $data['author-uri'] ) ) {
			$content .= ' | ' . esc_html__( 'Author', 'wp-recall' ) . ': <a title="' . esc_attr__( 'Visit the author’s page', 'wp-recall' ) . '" href="' . esc_attr( $data['author-uri'] ) . '" target="_blank">' . esc_attr( $data['author'] ) . '</a>';
		}
		if ( isset( $data['add-on-uri'] ) ) {
			$content .= ' | <a title="' . esc_attr__( 'Visit the add-on page', 'wp-recall' ) . '" href="' . esc_url( $data['add-on-uri'] ) . '" target="_blank">' . esc_html__( 'Add-on page', 'wp-recall' ) . '</a>';
		}
		$content .= '</div>';

		return $content;
	}

	function get_table_classes() {
		return [ 'widefat', 'fixed', 'striped', 'plugins', $this->_args['plural'] ];
	}

	function single_row( $item ) {

		$this->addon = $this->addons_data[ esc_attr( $item['ID'] ) ];
		$status      = ( $item['addon_status'] ) ? 'active' : 'inactive';
		$ver         = ( isset( $this->need_update[ esc_attr( $item['ID'] ) ] ) ) ? version_compare( $this->need_update[ esc_attr( $item['ID'] ) ]['new-version'], $this->addon['version'] ) : 0;
		$class       = $status;
		$class       .= ( $ver > 0 ) ? ' update' : '';

		echo '<tr class="' . esc_attr( $class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';

		if ( $ver > 0 ) {
			$colspan = ( $hidden = count( $this->column_info[1] ) ) ? 4 - $hidden : 4;

			echo '<tr class="plugin-update-tr ' . esc_attr( $status ) . '" id="' . esc_attr( $item['ID'] ) . '-update" data-slug="' . esc_attr( $item['ID'] ) . '">'
			     . '<td colspan="' . esc_attr( $colspan ) . '" class="plugin-update colspanchange">'
			     . '<div class="update-message notice inline notice-warning notice-alt">'
			     . '<p>'
			     . esc_html__( 'New version available', 'wp-recall' ) . ' ' . esc_html( $this->addon['name'] ) . ' ' . esc_html( $this->need_update[ $item['ID'] ]['new-version'] ) . '. ';
			echo ' <a href="#"  onclick=\'rcl_get_details_addon(' . json_encode( [ 'slug' => esc_attr( $item['ID'] ) ] ) . ',this);return false;\' title="' . esc_attr( $this->addon['name'] ) . '">' . esc_html__( 'view information about the version', 'wp-recall' ) . '</a> ' . esc_html__( 'or', 'wp-recall' );
			echo esc_html__( 'or', 'wp-recall' ) . ' <a class="update-add-on" data-addon="' . esc_attr( $item['ID'] ) . '" href="#">' . esc_html__( 'update automatically', 'wp-recall' ) . '</a>'
			     . '</p>'
			     . '</div>'
			     . '</td>'
			     . '</tr>';
		}
	}

	function prepare_items() {

		$addons = $this->get_addons_content();

		$this->_column_headers = $this->get_column_info();
		usort( $addons, [ &$this, 'usort_reorder' ] );

		$per_page     = $this->get_items_per_page( 'templates_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = count( $addons );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
		] );

		$this->items = array_slice( $addons, ( ( $current_page - 1 ) * $per_page ), $per_page );
	}

}

//class
function rcl_init_upload_template() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	if ( isset( $_POST['install-template-submit'], $_POST['_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'install-template-rcl' ) ) {
			return false;
		}
		rcl_upload_template();
	}
}

function rcl_upload_template() {

	$paths = rcl_get_addon_paths();

	if ( empty( $_FILES['addonzip']['tmp_name'] ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=manage-templates-recall&update-template=error-info' ) );
		exit;
	}

	$filename = sanitize_text_field( wp_unslash( $_FILES['addonzip']['tmp_name'] ) );
	$arch     = current( wp_upload_dir() ) . "/" . basename( $filename );
	copy( $filename, $arch );

	$zip = new ZipArchive();

	$res = $zip->open( $arch );

	if ( $res === true ) {

		for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
			//echo $zip->getNameIndex($i).'<br>';
			if ( $i == 0 ) {
				$dirzip = $zip->getNameIndex( $i );
			}

			if ( $zip->getNameIndex( $i ) == $dirzip . 'info.txt' ) {
				$info = true;
			}
		}

		if ( ! $info ) {
			$zip->close();
			wp_safe_redirect( admin_url( 'admin.php?page=manage-templates-recall&update-template=error-info' ) );
			exit;
		}

		foreach ( $paths as $path ) {
			if ( file_exists( $path . '/' ) ) {
				$rs = $zip->extractTo( $path . '/' );
				break;
			}
		}

		$zip->close();
		unlink( $arch );
		if ( $rs ) {
			wp_safe_redirect( admin_url( 'admin.php?page=manage-templates-recall&update-template=upload' ) );
			exit;
		} else {
			wp_die( esc_html__( 'Unpacking of archive failed.', 'wp-recall' ) );
		}
	} else {
		wp_die( esc_html__( 'ZIP archive not found.', 'wp-recall' ) );
	}
}
