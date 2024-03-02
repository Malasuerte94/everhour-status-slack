<?php

function register_everhour_slack_integration_admin_page(): void
{
    add_menu_page(
        'Everhour Slack Status - Integration Settings',
        'Everhour Slack',
        'manage_options',
        'everhour-slack-integration',
        'everhour_slack_integration_admin_page_html',
        'dashicons-admin-generic',
        20
    );
}

function everhour_slack_integration_admin_page_html(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $everhour_webhook_id = get_option('everhour_webhook_id', '');

    if (isset($_POST['action']) && $_POST['action'] == 'generate_webhook' && !$everhour_webhook_id) {
        $everhour_api_key = get_option('everhour_api_key', '');
        $target_url = home_url('/wp-json/everhour-slack-integration/v1/webhook/'); // Ensure this is your correct endpoint

        $response = create_everhour_webhook($everhour_api_key, $target_url);

        if ($response['success']) {
            echo '<div id="message" class="updated notice is-dismissible"><p>Webhook generated successfully. ID: ' . esc_html($response['webhook_id']) . '</p></div>';
        } else {
            echo '<div id="message" class="error notice is-dismissible"><p>Failed to generate webhook: ' . esc_html($response['error']) . '</p></div>';
        }
    }

    if (isset($_POST['everhour_api_key'], $_POST['slack_api_key'], $_POST['everhour_user_id'])) {
        update_option('everhour_api_key', sanitize_text_field($_POST['everhour_api_key']));
        update_option('slack_api_key', sanitize_text_field($_POST['slack_api_key']));
        update_option('everhour_user_id', sanitize_text_field($_POST['everhour_user_id']));
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Settings saved.</strong></p></div>';
    }

    $webhook_payload = get_option('everhour_webhook_payload', '');
    if (!empty($webhook_payload)) {
        echo '<h2>Last Webhook Payload</h2>';
        echo '<pre>' . esc_html(print_r($webhook_payload, true)) . '</pre>';
    }

    $slack_clear_status = get_option('slack_stop_payload', '');
    if (!empty($webhook_payload)) {
        echo '<h2>Slack Clear Status</h2>';
        echo '<pre>' . esc_html(print_r($slack_clear_status, true)) . '</pre>';
    }

    $slack_set_status = get_option('slack_set_payload', '');
    if (!empty($webhook_payload)) {
        echo '<h2>Slack Set Status</h2>';
        echo '<pre>' . esc_html(print_r($slack_set_status, true)) . '</pre>';
    }

    // Get current settings
    $everhour_api_key = get_option('everhour_api_key', '');
    $everhour_user_id = get_option('everhour_user_id', '');
    $slack_api_key = get_option('slack_api_key', '');

    // Admin page HTML
    echo '<div class="wrap">';
    echo '<h1>Everhour Slack Integration Settings</h1>';
    echo '<form method="POST">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">Everhour API Key</th>';
    echo '<td><input type="text" name="everhour_api_key" value="' . esc_attr($everhour_api_key) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Everhour User ID</th>';
    echo '<td><input type="text" name="everhour_user_id" value="' . esc_attr($everhour_user_id) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Slack API Key</th>';
    echo '<td><input type="text" name="slack_api_key" value="' . esc_attr($slack_api_key) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Everhour Webhook ID</th>';
    echo '<td><input type="text" name="everhour_webhook_id" value="' . esc_attr($everhour_webhook_id) . '" class="regular-text" readonly></td>';
    echo '</tr>';
    echo '</table>';
    echo '<input type="hidden" name="action" value="generate_webhook">';
    echo '<p class="submit"><button type="submit" class="button button-primary">Save and Generate Webhook</button></p>';
    echo '</form>';
    echo '</div>';
}
