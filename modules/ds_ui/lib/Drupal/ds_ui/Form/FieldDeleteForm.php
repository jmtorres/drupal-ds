<?php

/**
 * @file
 * Contains \Drupal\ds_ui\Form\FieldDeleteForm.
 */

namespace Drupal\ds_ui\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\ControllerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to delete a user's Open ID identity.
 */
class FieldDeleteForm extends ConfirmFormBase implements ControllerInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Holds the cache backend
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The field being deleted
   *
   * @var array
   */
  protected $field;

  /**
   * Constructs a FieldDeleteForm object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface
   *   The cache backend.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(CacheBackendInterface $cache_backend, ConfigFactory $config_factory) {
    $this->cacheBackend = $cache_backend;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cache.cache'), $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getQuestion() {
    return t('Are you sure you want to delete %field ?', array('%field' => $this->field['label']));
  }

  /**
   * {@inheritdoc}
   */
  protected function getCancelPath() {
    return 'admin/structure/ds/fields';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'field_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $field = '') {
    $config = $this->configFactory->get('ds.field.' . $field);
    $this->field = $config->get();

    if (empty($this->field)) {
      drupal_set_message(t('Field not found.'));
      drupal_goto('admin/structure/ds/fields');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $field = $this->field;

    // Remove field.
    $this->configFactory->get('ds.field.' . $field['field'])->delete();

    // Clear ds fields cache
    $this->cacheBackend->invalidateTags(array('ds_fields' => TRUE));

    // Redirect.
    $form_state['redirect'] = 'admin/structure/ds/fields';
    drupal_set_message(t('The field %field has been deleted.', array('%field' => $field['label'])));
  }

}