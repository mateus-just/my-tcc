<?php

namespace Drupal\chatgpt_paragraphs\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Plugin implementation of the 'chatgpt_paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "chatgpt_paragraphs",
 *   label = @Translation("ChatGPT Paragraphs"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class ChatGPTParagraphsWidget extends WidgetBase
{

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state)
  {
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text'),
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : '',
      '#attributes' => ['readonly' => 'readonly'], // Tornar o campo somente leitura.
      '#prefix' => '<div id="field-city-wrapper">', // Definir o ID do wrapper
      '#suffix' => '</div>',
    ];

    $element['prompt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prompt'),
      '#description' => $this->t('Enter a prompt to generate text from ChatGPT.'),
    ];

    $element['actions']['#type'] = 'actions';

    $element['actions']['generate'] = [
      '#type' => 'button',
      '#value' => $this->t('Generate'),
      '#ajax' => [
        'callback' => [$this, 'generateText'],
        'event' => 'click',
        'wrapper' => 'field-city-wrapper', // Usar o wrapper ID definido
      ],
    ];

    return $element;
  }

  /**
   * AJAX callback to generate text from ChatGPT.
   */
  public function generateText(array &$form, FormStateInterface $form_state)
  {
    // Log to verify the callback is called.
    \Drupal::logger('chatgpt_paragraphs')->debug('generateText function called');

    // Obter o valor do prompt do formulário.
    $prompt = $form_state->getValue(['field_city', 0, 'prompt']);

    \Drupal::logger('chatgpt_paragraphs')->debug('Prompt: @prompt', ['@prompt' => $prompt]);

    // Chamar o serviço ChatGPT para obter a resposta.
    $response_text = \Drupal::service('chatgpt_paragraphs.chatgpt_service')->callChatGPT($prompt);

    // Atualizar o valor do campo 'value'.
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('#edit-field-city-0-value', 'val', [$response_text]));
    return $response;
  }
}
