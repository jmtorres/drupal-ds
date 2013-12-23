<?php

/**
 * @file
 * Contains \Drupal\ds_extras\Plugin\DsField\SwitchField.
 */

namespace Drupal\ds_extras\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Plugin that generates a link to switch view mode with via ajax.
 *
 * @DsField(
 *   id = "switch_field",
 *   title = @Translation("Switch field"),
 *   entity_type = "node"
 * )
 */
class SwitchField extends DsFieldBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = '';
    static $added = FALSE;

    $settings = $this->getChosenSettings();
    if (!empty($settings)) {

      $entity = $this->entity();
      $id = $entity->id();
      $url = $this->entityType() . '-' . $this->viewMode(). '-' . $id . '-';
      $switch = array();

      foreach ($settings['vms'] as $key => $value) {
        if (!empty($value)) {
          $class = 'switch-' . $key;
          if ($key == $this->viewMode()) {
            $switch[] = '<span class="' . $class . '">' . check_plain(t($value)) . '</span>';
          }
          else {
            $switch[] = '<span class="' . $class . '"><a href="" class="' . $url . $key . '">' . check_plain(t($value)) . '</a></span>';
          }
        }
      }

      $output = array();

      if (!empty($switch)) {
        if (!$added) {
          $added = TRUE;
          $output['#attached'] = array(
            'js' => array(
              drupal_get_path('module', 'ds_extras') . '/js/ds_extras.js',
            ),
          );
        }
        $output['view_mode'] = array(
          '#markup' => '<div class="switch-view-mode-field">' . implode(' ', $switch) . '</div>',
        );
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($settings) {
    $entity_type = $this->entityType();
    $bundle = $this->bundle();
    $settings = isset($settings['vms']) ? $settings['vms'] : array();
    $view_modes = entity_get_view_modes($entity_type);

    $form['info'] = array(
      '#markup' => t('Enter a label for the link for the view modes you want to switch to.<br />Leave empty to hide link. They will be localized.'),
    );

    foreach ($view_modes as $key => $value) {
      $entity_display = entity_load('entity_display', $entity_type .  '.' . $bundle . '.' . $key);
      $visible = $entity_display->status();

      if ($visible) {
        $form['vms'][$key] = array(
          '#type' => 'textfield',
          '#default_value' => isset($settings[$key]) ? $settings[$key] : '',
          '#size' => 20,
          '#title' => check_plain($value['label']),
        );
      }
    }

    return $form;
  }

  /**
   * {@inhertidoc}
   */
  public function settingsSummary($settings) {
    $entity_type = $this->entityType();
    $bundle = $this->bundle();
    $settings = isset($settings['vms']) ? $settings['vms'] : array();
    $view_modes = entity_get_view_modes($entity_type);

    $summary[] = 'View mode labels';

    foreach ($view_modes as $key => $value) {
      $entity_display = entity_load('entity_display', $entity_type .  '.' . $bundle . '.' . $key);
      $visible = $entity_display->status();

      if ($visible) {
        $label = isset($settings[$key]) ? $settings[$key] : $key;
        $summary[] = $key . ' : ' . $label;
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed() {
    if (\Drupal::config('ds.extras')->get('switch_field')) {
      return TRUE;
    }

    return FALSE;
  }

}