<?php
/**
 * Plugin Name: WP REST Post Analytics
 * Description: Provides post analytics via a REST API and a lightweight admin UI.
 * Version: 1.1.0
 * Author: Yash V
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * --------------------------------------------------
 * REST API REGISTRATION
 * --------------------------------------------------
 */

add_action( 'rest_api_init', function () {

    register_rest_route( 'analytics/v1', '/post-stats', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'wp_rpa_get_post_stats',
        'permission_callback' => 'wp_rpa_permission_check',
    ] );

} );

/**
 * Permission check
 */
function wp_rpa_permission_check() {
    return true;
}

/**
 * REST callback
 */
function wp_rpa_get_post_stats() {
    global $wpdb;

    $counts = wp_count_posts( 'post' );

    $data = [
        'total_published' => isset( $counts->publish ) ? (int) $counts->publish : 0,
        'total_drafts'    => isset( $counts->draft ) ? (int) $counts->draft : 0,
        'author_breakdown'=> $wpdb->get_results(
            "
            SELECT post_author, COUNT(ID) AS post_count
            FROM {$wpdb->posts}
            WHERE post_type = 'post'
              AND post_status = 'publish'
            GROUP BY post_author
            ",
            ARRAY_A
        ),
    ];

    return rest_ensure_response( $data );
}

/**
 * --------------------------------------------------
 * ADMIN UI
 * --------------------------------------------------
 */

add_action( 'admin_menu', function () {

    add_management_page(
        'Post Analytics',
        'Post Analytics',
        'edit_posts',
        'wp-rpa-analytics',
        'wp_rpa_render_admin_page'
    );

} );

/**
 * Render admin page with nonce-protected REST call
 */
function wp_rpa_render_admin_page() {

    $rest_nonce = wp_create_nonce( 'wp_rest' );
    ?>
    <div class="wrap">
        <h1>Post Analytics</h1>
        <p>
            This page fetches analytics from the custom WordPress REST API endpoint.
        </p>

        <button id="wp-rpa-load" class="button button-primary">
            Load Analytics
        </button>

        <pre id="wp-rpa-output"
             style="margin-top:20px; padding:15px; background:#1e1e1e; color:#d4d4d4; max-width:600px;">
        </pre>
    </div>

    <script>
        document.getElementById('wp-rpa-load').addEventListener('click', async () => {
            const output = document.getElementById('wp-rpa-output');
            output.textContent = 'Loading...';

            try {
                const response = await fetch(
                    '<?php echo esc_url( rest_url( 'analytics/v1/post-stats' ) ); ?>',
                    {
                        method: 'GET',
                        headers: {
                            'X-WP-Nonce': '<?php echo esc_js( $rest_nonce ); ?>'
                        }
                    }
                );

                const data = await response.json();
                output.textContent = JSON.stringify(data, null, 2);

            } catch (err) {
                output.textContent = 'Error loading analytics';
            }
        });
    </script>
    <?php
}
