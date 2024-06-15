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

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatgpt_paragraphs.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
