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
 *     "string",
 *     "string_long"
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
    $field_name = $items->getFieldDefinition()->getName();
    $field_label = $items->getFieldDefinition()->getLabel();
    $field_max_length = $items->getFieldDefinition()->getFieldStorageDefinition()->getSetting('max_length');
    $field_type = $items->getFieldDefinition()->getType();
    $value_type='';
    $wrapper_id = $this->getWrapperId($field_name, $delta);

    if($field_type == 'string'){
      $value_type = 'textfield';
    }
    if($field_type == 'string_long'){
      $value_type = 'textarea';
    }

    $element['prompt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prompt'),
      '#description' => $this->t('Enter a prompt to generate text from ChatGPT.'),
    ];

    $element['value'] = [
      '#type' => $value_type,
      '#title' => $field_label,
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : '',
      '#attributes' => ['readonly' => 'readonly'],
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $element['actions']['#type'] = 'actions';

    $element['actions']['generate'] = [
      '#type' => 'button',
      '#value' => $this->t('Generate'),
      '#ajax' => [
        'callback' => [$this, 'generateText'],
        'event' => 'click',
        'wrapper' => $wrapper_id,
      ],
      '#name' => $field_name . '_' . $delta . '_generate',
      '#attributes' => [
        'data-field-name' => $field_name,
        'data-delta' => $delta,
        'data-max-length' => $field_max_length, // Pass the max length as a data attribute
      ],
    ];

    return $element;
  }

  /**
   * Get the wrapper ID for the AJAX callback.
   */
  protected function getWrapperId($field_name, $delta)
  {
    return 'field-' . str_replace('_', '-', $field_name) . '-' . $delta . '-wrapper';
  }

  /**
   * AJAX callback to generate text from ChatGPT.
   */
  public function generateText(array &$form, FormStateInterface $form_state)
  {
    // Log to verify the callback is called.
    \Drupal::logger('chatgpt_paragraphs')->debug('generateText function called');

    // Get the triggering element and its parents.
    $triggering_element = $form_state->getTriggeringElement();
    $parents = $triggering_element['#array_parents'];
    array_pop($parents); // Remove 'generate'
    $field_name = $parents[0];
    $delta = $parents[2];
    $max_length = $triggering_element['#attributes']['data-max-length'];

    if($max_length == null){
      $prompt = $form_state->getValue([$field_name, $delta, 'prompt']) . ', responda com no máximo 8000 caracteres';
      $response_text = $this->callChatGPTWithLimit($prompt, 8000);
    }
    else{
      $prompt = $form_state->getValue([$field_name, $delta, 'prompt']) . ', responda com no máximo ' . $max_length . ' caracteres';
      $response_text = $this->callChatGPTWithLimit($prompt, $max_length);
    }

    \Drupal::logger('chatgpt_paragraphs')->debug('Prompt: @prompt', ['@prompt' => $prompt]);

    // Update the value of the field.
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand('#edit-' . str_replace('_', '-', $field_name) . '-' . $delta . '-value', 'val', [$response_text]));
    return $response;
  }

  protected function callChatGPTWithLimit($prompt, $max_length)
  {
    $response_text = '';
    $attempts = 0;
    $max_attempts = 10; // Limit the number of attempts to prevent infinite loops

    if($max_length < 8000){
      while (strlen($response_text) > $max_length || $response_text === '') {
        $response_text = \Drupal::service('chatgpt_paragraphs.chatgpt_service')->callChatGPT($prompt);
        $attempts++;

        if ($attempts >= $max_attempts) {
          break;
        }
      }

      if (strlen($response_text) > $max_length) {
        $response_text = substr($response_text, 0, $max_length);
      }
    }
    else{
      $response_text = \Drupal::service('chatgpt_paragraphs.chatgpt_service')->callChatGPT($prompt);
    }

    // If the response is still too long, truncate it.

    return $response_text;
  }
}
