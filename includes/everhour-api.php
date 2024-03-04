<?php

/**
 * @param string $everhour_api_key
 * @param string $target_url
 * @return array
 */
function createEverhourWebhook(string $everhour_api_key, string $target_url): array
{
    $api_url = 'https://api.everhour.com/hooks';
    $events = ["api:timer:started", "api:timer:stopped"];
    $args = [
        'body'    => json_encode([
            'targetUrl' => $target_url,
            'events'    => $events,
        ]),
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Api-Key'    => $everhour_api_key,
        ],
        'method'  => 'POST',
    ];

    $response = wp_remote_post($api_url, $args);

    $body = json_decode($response['body'], true);
    if (isset($body['id'])) {
        update_option('everhour_webhook_id', sanitize_text_field($body['id']));
        return ["success" => true, "webhook_id" => $body['id'], 'all' => $body, 'target' => $target_url];
    } elseif (isset($body['errors'])) {
        return ["success" => false, "error" => json_encode([$body['errors'], $target_url]), 'all' => $body, 'target' =>
            $target_url];
    } else {
        return ["success" => false, "error" => "Failed to retrieve the webhook ID", 'all' => $body, 'target' => $target_url];
    }
}