<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\Derivative\DynamicCodeField.
 */

namespace Drupal\ds\Plugin\Derivative;

/**
 * Retrieves block plugin definitions for all custom blocks.
 */
class DynamicCodeField extends DynamicField {

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return DS_FIELD_TYPE_CODE;
  }

}
