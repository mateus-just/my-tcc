<?php

namespace Drupal\chatgpt_paragraphs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\chatgpt_paragraphs\Service\ChatGPTService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ChatGPTController.
 */
class ChatGPTController extends ControllerBase {

  protected $chatGPTService;

  /**
   * Constructs a ChatGPTController object.
   *
   * @param \Drupal\chatgpt_paragraphs\Service\ChatGPTService $chatGPTService
   *   The ChatGPT service.
   */
  public function __construct(ChatGPTService $chatGPTService) {
    $this->chatGPTService = $chatGPTService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('chatgpt_paragraphs.chatgpt_service')
    );
  }

  /**
   * Endpoint for ChatGPT.
   */
  public function chatgpt(Request $request) {
    $content = json_decode($request->getContent(), TRUE);

    if (isset($content['prompt'])) {
      $prompt = $content['prompt'];
      try {
        $response_text = $this->chatGPTService->callChatGPT($prompt);
        return new JsonResponse(['status' => 'success', 'response' => $response_text]);
      } catch (\Exception $e) {
        return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
      }
    }

    return new JsonResponse(['status' => 'error', 'message' => 'Missing prompt parameter'], 400);
  }
}
