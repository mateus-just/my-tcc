<?php

namespace Drupal\chatgpt_paragraphs\Service;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class ChatGPTService {

  protected $httpClient;
  protected $configFactory;

  /**
   * ChatGPTService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * Calls the ChatGPT API with the given prompt.
   *
   * @param string $prompt
   *   The prompt to send to the ChatGPT API.
   *
   * @return string
   *   The response text from the ChatGPT API.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function callChatGPT($prompt) {
    // Defina sua chave de API aqui.
    $api_key = $this->configFactory->get('chatgpt_paragraphs.settings')->get('api_key');

    try {
      // Faz uma requisição POST para a API do ChatGPT.
      $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'model' => 'gpt-4o',
          'messages' => [
            [
              'role' => 'user',
              'content' => $prompt,
            ],
          ],
          'max_tokens' => 150,
        ],
      ]);

      // Decodifica a resposta JSON e retorna o texto gerado.
      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data['choices'][0]['message']['content'];
    } catch (\Exception $e) {
      // Lança uma exceção com a mensagem de erro.
      throw new \Exception($e->getMessage());
    }
  }
}
