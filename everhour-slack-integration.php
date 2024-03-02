<?php
/**
 * Plugin Name: Everhour Slack Integration
 * Description: Integrates Everhour with Slack to update statuses based on timer events.
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/everhour-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/slack-api.php';

add_action('admin_menu', 'register_everhour_slack_integration_admin_page');

add_action('rest_api_init', function () {
    register_rest_route('everhour-slack-integration/v1', '/webhook/', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'esi_handle_everhour_webhook',
        'permission_callback' => '__return_true',
    ));
});


function esi_handle_everhour_webhook(WP_REST_Request $request): WP_REST_Response
{
    $payload = json_decode($request->get_body(), true);
    $everhour_user_id = get_option('everhour_user_id', '');

    if($everhour_user_id) {
        if (isset($payload['event']) && $payload['event'] == 'api:timer:stopped' && $payload['payload']['data']['taskTime']['user'] == $everhour_user_id) {
            update_option('everhour_webhook_payload', ['STOP', $payload['payload']['data']['taskTime']['user'], 'Catalin']);
            clear_slack_status();
        }

        if (isset($payload['event']) && $payload['event'] == 'api:timer:started' && $payload['payload']['data']['user']['id'] == $everhour_user_id) {
            $status = $payload['payload']['data']['task']['number'] .':'. $payload['payload']['data']['task']['name'];
            update_option('everhour_webhook_payload', ['START', $payload['payload']['data']['user']['email'], $status]);
            update_slack_status($status);
        }
    }

    return new WP_REST_Response('Webhook processed', 200);
}