<?php

/**
 * @file
 * Contains \Drupal\chartbeat\Form\ChartbeatAdminSettings.
 */

namespace Drupal\chartbeat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\taxonomy\Entity\Vocabulary;

class ChartbeatAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chartbeat_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['chartbeat.settings'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('chartbeat.settings');
    $sections = $form_state->getValue('chartbeat_sections');

    $config->set('chartbeat_uid', $form_state->getValue('chartbeat_uid'))
           ->set('chartbeat_domain', $form_state->getValue('chartbeat_domain'))
           ->set('chartbeat_use_canonical', $form_state->getValue('chartbeat_use_canonical'))
           ->set('chartbeat_cookies', $form_state->getValue('chartbeat_cookies'))
           ->set('chartbeat_api_key', $form_state->getValue('chartbeat_api_key'))
           ->set('chartbeat_sections_enable', $form_state->getValue('chartbeat_sections_enable'))
           ->set('chartbeat_authors_enabled', $form_state->getValue('chartbeat_authors_enabled'))
           ->save();
    foreach ($sections as $key => $value) {
      $config->set('chartbeat_sections_'.$key, $form_state->getValue('chartbeat_sections')[$key])
             ->save();
    }
    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }
    parent::submitForm($form, $form_state);
  }


  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['chartbeat_uid'] = [
      '#type' => 'textfield',
      '#title' => t('Account ID'),
      '#description' => t('In order to activate Chartbeat, you must enter your Account ID. You can
find yours <a href="@chartbeat">here</a>.', [
        '@chartbeat' => 'http://chartbeat.com/drupal'
        ]),
      '#default_value' => $this->config('chartbeat.settings')->get('chartbeat_uid'),
    ];

    // @FIXED NOW
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/chartbeat.settings.yml and config/schema/chartbeat.schema.yml.
    $domain = $this->config('chartbeat.settings')->get('chartbeat_domain');
    if (empty($domain)) {
      $domain = _chartbeat_get_default_domain();
    }
    $form['chartbeat_domain'] = [
      '#type' => 'textfield',
      '#title' => t('Domain'),
      '#description' => t('The domain name of the site you want to track.'),
      '#default_value' => $domain,
    ];
    $form['chartbeat_options_advanced'] = [
      '#type' => 'details',
      '#title' => t('Advanced Settings'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['chartbeat_options_advanced']['chartbeat_use_canonical'] = [
      '#type' => 'checkbox',
      '#title' => t('Use Canonical'),
      '#description' => t('If your site defines &lt;link rel="canonical"
      .../&gt;, you can set check this value to to make Chartbeat use the
      canonical path instead of the actual URL.'),
      '#default_value' => $this->config('chartbeat.settings')->get('chartbeat_use_canonical'),
    ];
    $form['chartbeat_options_advanced']['chartbeat_cookies'] = [
      '#type' => 'checkbox',
      '#title' => t('Use Cookies'),
      '#description' => t('Customers who are subject to the EU e-Privacy Directive
      can set this variable to prevent Chartbeat from using cookies. NOTE: By
      using Chartbeat without cookies, you will be unable to see if a user is
      new or returning.'),
      '#default_value' => $this->config('chartbeat.settings')->get('chartbeat_cookies'),
    ];
    $form['chartbeat_options_live'] = [
      '#type' => 'details',
      '#title' => t('Live Stats'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['chartbeat_options_live']['chartbeat_api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Chartbeat API Key'),
      '#description' => t('To enable live stats in your Drupal dashboard, please enter your API key. You can find yours <a href="@api">here</a>.', [
        '@api' => 'http://chartbeat.com/drupal/'
        ]),
      '#default_value' => $this->config('chartbeat.settings')->get('chartbeat_api_key'),
    ];
    $form['chartbeat_publishing'] = [
      '#type' => 'details',
      '#title' => t('Chartbeat Publishing Options'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' => t('These options are only available to Chartbeat Publishing accounts. For more
      information, visit <a href=http://chartbeat.com/publishing/>Chartbeat Publishing</a>.'),
    ];

    $sections_enabled = $this->config('chartbeat.settings')->get('chartbeat_sections_enable');
    $form['chartbeat_publishing']['chartbeat_sections_enable'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable sections'),
      '#description' => t('Enables section tracking. See <a href="@groups">here</a>
      for more information.', [
        '@groups' => 'http://chartbeat.com/docs/configuration_variables/#groups'
        ]),
      '#default_value' => \Drupal::moduleHandler()->moduleExists('taxonomy') ? $sections_enabled : FALSE,
      '#disabled' => \Drupal::moduleHandler()->moduleExists('taxonomy') ? FALSE : TRUE,
    ];

    if (\Drupal::moduleHandler()->moduleExists('taxonomy')) {
      $form['chartbeat_publishing']['chartbeat_sections_enable']['#ajax'] = [
        'callback' => 'chartbeat_admin_settings_publishing_sections_callback',
        'wrapper' => 'chartbeat-sections-taxonomy-vocab-wrapper',
        'method' => 'replace',
      ];
      $form['chartbeat_publishing']['chartbeat_sections_wrapper'] = [
        '#type' => 'fieldset',
        '#title' => t('Current Vocabularies'),
        '#prefix' => '<div id="chartbeat-sections-taxonomy-vocab-wrapper">',
        '#suffix' => '</div>',
        '#collapsible' => FALSE,
        '#collapsed' => FALSE,
        '#description' => !\Drupal::moduleHandler()->moduleExists('taxonomy') ?
                           t('The taxonomy module must be enabled to use sections.') : 
            ($sections_enabled ? t('Select which taxonomy vocabularies will be tracked as sections.') : t('You must enable sections support to select taxonomy vocabularies.')),
      ];
      if ($sections_enabled || $form_state->getValue(['chartbeat_sections_enable'])) {
        $vocab = _chartbeat_vocab_array_format(Vocabulary::loadMultiple());
        foreach ($vocab as $key => $value) {
          $term_checked[] = $this->config('chartbeat.settings')->get('chartbeat_sections_'.$key);
        }
        $form['chartbeat_publishing']['chartbeat_sections_wrapper']['chartbeat_sections'] = [
          '#type' => 'checkboxes',
          '#options' => _chartbeat_vocab_array_format(Vocabulary::loadMultiple()),
          '#default_value' => $term_checked,
        ];
      }
    }

    $form['chartbeat_publishing']['chartbeat_authors_enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable authors'),
      '#description' => t('Enable author tracking. See <a href="@groups">here</a> for more information.', [
        '@groups' => 'http://chartbeat.com/docs/configuration_variables/#groups'
        ]),
      '#default_value' => $this->config('chartbeat.settings')->get('chartbeat_authors_enabled'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
