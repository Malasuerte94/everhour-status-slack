<?php

function update_slack_status($status_text) {
    $slack_api_key = get_option('slack_api_key', '');

    $api_url = 'https://slack.com/api/users.profile.set';
    $status_text_trimmed = mb_substr($status_text, 0, 99);

    $args = array(
        'body' => json_encode([
            'profile' => [
                'status_text' => $status_text_trimmed,
                'status_emoji' => ':male-technologist:',
                'status_expiration' => 0
            ]
        ]),
        'headers' => array(
            'Authorization' => 'Bearer ' . $slack_api_key,
            'Content-Type' => 'application/json; charset=utf-8',
        ),
        'method'      => 'POST',
        'data_format' => 'body',
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        update_option('slack_set_payload', ['SET ERROR', $error_message, 'Catalin']);
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        update_option('slack_set_payload', ['SET', $response_code]);
    }
}

function clear_slack_status() {
    $slack_api_key = get_option('slack_api_key', '');

    $api_url = 'https://slack.com/api/users.profile.set';

    $args = array(
        'body' => json_encode([
            'profile' => [
                'status_text' => '',
                'status_emoji' => '',
                'status_expiration' => 0
            ]
        ]),
        'headers' => array(
            'Authorization' => 'Bearer ' . $slack_api_key,
            'Content-Type' => 'application/json; charset=utf-8',
        ),
        'method'      => 'POST',
        'data_format' => 'body',
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        update_option('slack_stop_payload', ['CLEAR ERROR', $error_message, 'Catalin']);
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        update_option('slack_stop_payload', ['CLEAR', $response_code]);
    }
}