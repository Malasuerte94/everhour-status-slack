<?php

/**
 * @param $status_text
 * @return void
 */
function updateSlackStatus($status_text): void
{
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
        addLog('ADD_STATUS', 'error', $error_message);
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        addLog('ADD_STATUS', 'success', ':male-technologist: : '.$status_text_trimmed);
    }
}

/**
 * @return void
 */
function clearSlackStatus(): void
{
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
        addLog('CLEAR_STATUS', 'error', $error_message);
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        addLog('CLEAR_STATUS', 'success', '');
    }
}