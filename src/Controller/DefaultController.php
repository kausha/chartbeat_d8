<?php /**
 * @file
 * Contains \Drupal\chartbeat\Controller\DefaultController.
 */

namespace Drupal\chartbeat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


/**
 * Default controller for the chartbeat module.
 */
class DefaultController extends ControllerBase {

  public function chartbeat_dashboard_page() {
    $url = \Drupal::config('chartbeat.settings')->get('chartbeat_domain');
    $key = \Drupal::config('chartbeat.settings')->get('chartbeat_api_key');
    if (!empty($url) && !empty($key)) {
      $content = "<iframe src=\"http://chartbeat.com/dashboard/?url=$url&k=$key&slim=1\" height=\"700\" width=\"100%\"></iframe>";
    }
    else {
      $content = 'Notice: You must save the domain and API key on the settings page to see the dashboard.';
    }
    $variables = [
      '#type' => 'markup',
      '#markup' => $content,
    ];
    return $variables;
  }

  /**
   * Checks if the publishing dashboard should be accessible.
   *
   * This check is performed by ensuring that the user has administer
   * chartbeat settings permissions, in addition to checking if one
   * of the two or both publishing features are enabled.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for the logged in user.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function chartbeat_publishing_dashboard_permission(AccountInterface $account) {
    $access = $account->hasPermission('administer chartbeat settings') && chartbeat_publishing_is_enabled();
    if ($access) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

  public function chartbeat_publishing_dashboard_page() {
    $url = \Drupal::config('chartbeat.settings')->get('chartbeat_domain');
    $key = \Drupal::config('chartbeat.settings')->get('chartbeat_api_key');
    if (!empty($url) && !empty($key)) {
      $content = "<iframe src=\"http://chartbeat.com/publishing/dashboard/?url=$url&k=$key&slim=1\" height=\"700\" width=\"100%\"></iframe>";
    }
    else {
      $content = 'Notice: You must save the domain and API key on the settings page to see the dashboard.';
    }
    $variables = [
      '#type' => 'markup',
      '#markup' => $content,
    ];

    return $variables;
  }



}
