<?php
/**
 * @package Carve_In_My_Heart
 * @version 0.1
 */
/*
Plugin Name: Carve In My Heart
Plugin URI:
Description:
Author: Laboradian
Version: 0.1
Author URI:
*/

/*
    Copyright 2018 Laboradian (email : info@laboradian.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function carve_in_my_heart_get_sentence() {

    $sentences = get_option( 'my-data' );
    $sentences = explode( "\n", $sentences );
    $sentences = array_filter($sentences, function($var) {
       return $var != '';
    });

    return wptexturize( $sentences[ mt_rand( 0, count( $sentences ) - 1 ) ] );
}

add_action( 'admin_notices', function() {
    $chosen = carve_in_my_heart_get_sentence();
    echo "<p id='carve-in-my-heart'>" . esc_html( $chosen ) . "</p>";
} );

add_action( 'admin_head', function() {
    $x = is_rtl() ? 'left' : 'right';

    echo "
	<style type='text/css'>
	#carve-in-my-heart {
		float: $x;
		padding-$x: 15px;
		padding-top: 5px;		
		margin: 0;
		font-size: 11px;
	}
	</style>
	";
} );

add_action( 'admin_menu', function() {
    add_options_page(
        __('Carve In My Heart', 'carve-in-my-heart'),
        __('Carve In My Heart', 'carve-in-my-heart'),
        'manage_options',
        'carve-in-my-heart',
        'carve_in_my_heart'
    );
} );

function carve_in_my_heart() {
    ?>
    <div class="wrap">
        <h2>Carve in my heart</h2>

        <form id="my-submenu-form" method="post">
            <?php wp_nonce_field( 'my-nonce-key', 'carve-in-my-heart' ); ?>

            <table class="form-table">
               <tbody>
                <th scope="row"><?php _e( 'Sentences', 'my-custom-admin' ); ?></th>
                <td>
                    <fieldset><p>
                        <textarea name="my-data" rows="10" cols="50"><?php echo esc_attr( get_option( 'my-data' ) ); ?></textarea>
                    </p></fieldset>
                </td>
               </tbody>
            </table>

            <p class="submit">
                <input type="submit"
                      value="<?php esc_attr( _e( 'Save', 'carve-in-my-heart' ) ); ?>"
                      class="button button-primary button-large"></p>
        </form>

    </div>
    <?php
}

add_action( 'admin_init', function() {
    if ( isset( $_POST['carve-in-my-heart'] ) && $_POST['carve-in-my-heart'] ){
        if ( check_admin_referer( 'my-nonce-key', 'carve-in-my-heart' ) ){

            $e = new WP_Error();

            if ( isset($_POST['my-data']) && $_POST['my-data'] ) {
                if (  trim( $_POST['my-data'] ) != '' ) {
                    update_option( 'my-data', trim( $_POST['my-data'] ) );
                } else {
                    $e->add(
                        'error',
                        __( 'Please enter a valid sentences.',
                            'carve-in-my-heart' )
                    );
                    set_transient( 'carve-in-my-heart-errors',
                        $e->get_error_messages(), 10 );
                }
            } else {
                update_option( 'my-data', '' );
            }

            wp_safe_redirect( menu_page_url( 'carve-in-my-heart', false ) );
        }
    }
} );

add_action( 'admin_notices', function() {
    ?>
    <?php if ( $messages = get_transient( 'carve-in-my-heart-errors' ) ): ?>
        <div class="error">
            <ul>
                <?php foreach( $messages as $message ): ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php
} );

?>
