<?php

namespace Drupal\chatgpt_paragraphs\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\chatgpt_paragraphs\Service\ChatGPTService;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get responses from ChatGPT.
 *
 * @RestResource(
 *   id = "chatgpt_resource",
 *   label = @Translation("ChatGPT Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/chatgpt",
 *     "create" = "/api/chatgpt"
 *   }
 * )
 */
class ChatGPTResource extends ResourceBase {

  /**
   * The ChatGPT service.
   *
   * @var \Drupal\chatgpt_paragraphs\Service\ChatGPTService
   */
  protected $chatGPTService;

  /**
   * Constructs a new ChatGPTResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\chatgpt_paragraphs\Service\ChatGPTService $chatgpt_service
   *   The ChatGPT service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, ChatGPTService $chatgpt_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->chatGPTService = $chatgpt_service;
  }

  /**
   * Creates a new instance of the resource.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   A new instance of the resource.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('chatgpt_paragraphs.chatgpt_service')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function post(Request $request): JsonResponse {
    try {
      // Obtém o conteúdo da requisição.
      $content = $request->getContent();
      $params = json_decode($content, TRUE);
      $prompt = $params['prompt'];

      // Chama o serviço ChatGPT com o prompt fornecido.
      $response_text = $this->chatGPTService->callChatGPT($prompt);

      // Retorna a resposta em formato JSON.
      return new JsonResponse(['status' => 'success', 'response' => $response_text], 200);
    }
    catch (\Exception $e) {
      // Em caso de exceção, retorna uma resposta de erro com a mensagem da exceção.
      return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
