<?php
/**
 * Plugin Name: Everhour Status Slack
 * Description: Integrates Everhour with Slack to update statuses based on timer events.
 * Version: 1.2
 * Author: Catalin Ion Ene
 * Author URI: https://catalin-ene.ro
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/everhour-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/slack-api.php';

add_action('admin_menu', 'register_everhour_slack_integration_admin_page');


/** WEBHOOK RESPONSE */
add_action('rest_api_init', function () {
    register_rest_route('everhour-slack-integration/v1', '/webhook/', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'handleEverHourWebHook',
        'permission_callback' => '__return_true',
    ));
});


/**
 * PROCESS RESPONSE FROM WEBHOOK
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function handleEverHourWebHook(WP_REST_Request $request): WP_REST_Response
{
    $payload = json_decode($request->get_body(), true);
    $everhour_user_id = get_option('everhour_user_id', '');

    if($everhour_user_id) {
        if (isset($payload['event']) && $payload['event'] == 'api:timer:stopped' && $payload['payload']['data']['taskTime']['user'] == $everhour_user_id) {
            clearSlackStatus();
        }

        if (isset($payload['event']) && $payload['event'] == 'api:timer:started' && $payload['payload']['data']['user']['id'] == $everhour_user_id) {
            $status = $payload['payload']['data']['task']['number'] .':'. $payload['payload']['data']['task']['name'];
            updateSlackStatus($status);
        }
    }

    return new WP_REST_Response('Webhook processed', 200);
}


/**
 * @param string $action
 * @param string $status
 * @param string $value
 * @return void
 */
function addLog(string $action, string $status = '', string $value = ''): void
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'esi_logs';

    $wpdb->insert(
        $table_name,
        array(
            'action' => $action,
            'status' => $status,
            'value' => $value,
            'timestamp' => current_time('mysql', 1)
        )
    );
}


/** CREATE LOG FILE */
register_activation_hook(__FILE__, 'createLogTable');


/**
 * @return void
 */
function createLogTable(): void
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'esi_logs';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        action tinytext NOT NULL,
        status text,
        value text,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}