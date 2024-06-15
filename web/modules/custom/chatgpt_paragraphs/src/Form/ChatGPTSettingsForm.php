<?php

namespace Drupal\chatgpt_paragraphs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ChatGPTSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['chatgpt_paragraphs.settings'];
  }

  public function getFormId() {
    return 'chatgpt_paragraphs_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('chatgpt_paragraphs.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Enter the API Key for ChatGPT.'),
      '#default_value' => $config->get('api_key'),
    ];

    $form['gpt_options'] = array(
        '#type' => 'value',
        '#value' => array('gpt-4o' => t('gpt-4o'),
                          'gpt-4-turbo' => t('gpt-4-turbo'),
                          'gpt-4' => t('gpt-4'),
                          'gpt-3.5-turbo-16k' => t('gpt-3.5-turbo-16k'),
                          'gpt-3.5-turbo-0125' => t('gpt-3.5-turbo-0125'),
                          'gpt-3.5-turbo' => t('gpt-3.5-turbo'))
      );

    $form['gpt_model'] = [
        '#type' => 'select',
        '#title' => $this->t('GPT Model'),
        '#description' => $this->t('Select a GPT Model'),
        '#options' =>  $form['gpt_options']['#value'],
        '#default_value' => $config->get('gpt_model'),
      ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatgpt_paragraphs.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('gpt_model', $form_state->getValue('gpt_model'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
