<?php

// retrieves a list of users via live search
function affwp_search_users() {

	if ( empty( $_POST['search'] ) ) {
		die( '-1' );
	}

	if ( ! current_user_can( 'manage_affiliates' ) ) {
		die( '-1' );
	}

	$search_query = htmlentities2( trim( $_POST['search'] ) );

	do_action( 'affwp_pre_search_users', $search_query );

	$args = array();

	if ( isset( $_POST['status'] ) ) {
		$status = mb_strtolower( htmlentities2( trim( $_POST['status'] ) ) );

		switch ( $status ) {
			case 'false':
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
					)
				);
				$args = array( 'exclude' => wp_list_pluck( $affiliates, 'affiliate_id' ) );
				break;
			case 'any':
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
					)
				);
				$args = array( 'include' => wp_list_pluck( $affiliates, 'affiliate_id' ) );
				break;
			default:
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
						'status' => $status,
					)
				);
				$args = array( 'include' => wp_list_pluck( $affiliates, 'affiliate_id' ) );
		}
	}

	$found_users = array_filter(
		get_users( $args ),
		function ( $user ) {
			$q = mb_strtolower( htmlentities2( trim( $_POST['search'] ) ) );

			$user_login   = mb_strtolower( $user->user_login );
			$display_name = mb_strtolower( $user->display_name );
			$user_email   = mb_strtolower( $user->user_email );

			// Detect query term matches from these user fields (in order of priority)
			return (
				false !== mb_strpos( $user_login, $q )
				||
				false !== mb_strpos( $display_name, $q )
				||
				false !== mb_strpos( $user_email, $q )
			);
		}
	);

	if ( $found_users ) {
		$user_list = '<ul>';

		foreach( $found_users as $user ) {
			$user_list .= '<li><a href="#" data-id="' . esc_attr( $user->ID ) . '" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
		}

		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list, 'id' => 'found' ) );
	} else {
		echo json_encode( array( 'results' => '<p>' . __( 'No users found', 'affiliate-wp' ) . '</p>', 'id' => 'fail' ) );
	}

	die();
}
add_action( 'wp_ajax_affwp_search_users', 'affwp_search_users' );
