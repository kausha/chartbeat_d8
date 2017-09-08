<?php

namespace Drupal\chartbeat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
// use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
// use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Chartbeat Dashboard' block.
 *
 * @Block(
 *   id = "chartbeat_dashboard",
 *   admin_label = @Translation("Chartbeat Dashboard"),
 *   category = @Translation("Custom Blocks")
 * )
 */
class ChartbeatDashboard extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => $this->getblockContent(),
      '#cache' => array('max-age' => 0),
      '#attached' => [
        'library' => ['chartbeat/drupal_chartbeat'],
        'drupalSettings' => [
          'chartbeat' => [
            'drupal_chartbeat' => $this->getblockJs(),
          ],
        ],
      ],
    );
  }

  /**
   * Custom callback for rendering the view content.
   *
   * @return render
   *   Returns the view object.
   */
  public function getblockJs() {
    if (chartbeat_js_allowed(TRUE) && chartbeat_is_enabled()) {
      $api = \Drupal::config('chartbeat.settings')->get('chartbeat_api_key');
      $url = \Drupal::config('chartbeat.settings')->get('chartbeat_domain');
      $attach = [];
      if (!empty($api) && !empty($url)) {
        $attach['apikey'] = $api;
        $attach['base_url'] = $url;
        return $attach;
      }
    }
    
  }
  
  /**
   * Custom callback for rendering the view content.
   *
   * @return render
   *   Returns the view object.
   */
  public function getblockContent() {
    if (chartbeat_js_allowed(TRUE) && chartbeat_is_enabled()) {
      $api = \Drupal::config('chartbeat.settings')->get('chartbeat_api_key');
      $url = \Drupal::config('chartbeat.settings')->get('chartbeat_domain');

      if (!empty($api) && !empty($url)) {
        $content = '<div id="chartbeat-widget-sitetotal" class="chartbeat-dashboard-widget"></div>';
      }
    }
    else {
        $content = t('<span class="alert">@message</span>', array('@message' => 'Pages cannot be retrieved at this time.'));
    }
    return $content;
  }

}