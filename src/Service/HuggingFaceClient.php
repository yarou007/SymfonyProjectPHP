<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HuggingFaceClient
{
    public function __construct(
        private HttpClientInterface $http,
        private string $hfToken,
        private string $hfModel
    ) {}

    public function chat(string $prompt): string
    {
        $response = $this->http->request('POST', 'https://router.huggingface.co/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->hfToken,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => $this->hfModel,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 256,
            ],
            'timeout' => 60,
        ]);

        $data = $response->toArray(false);

        // If HF returns an error payload, show it
        if (isset($data['error'])) {
            $msg = is_array($data['error']) ? ($data['error']['message'] ?? json_encode($data['error'])) : (string) $data['error'];
            return 'HF error: '.$msg;
        }

        return $data['choices'][0]['message']['content']
            ?? 'No response';
    }
}
