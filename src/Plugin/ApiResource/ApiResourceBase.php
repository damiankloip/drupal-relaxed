<?php

namespace Drupal\relaxed\Plugin\ApiResource;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\relaxed\Plugin\ApiResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Base API resource plugin.
 */
abstract class ApiResourceBase extends PluginBase implements ApiResourceInterface {

  public function isAttachment() {
    return (substr($this->getPluginId(), -strlen('attachment')) == 'attachment');
  }

  protected function validate(ContentEntityInterface $entity) {
    $violations = $entity->validate();

    // Remove violations of inaccessible fields as they cannot stem from our
    // changes.
    $violations->filterByFieldAccess();

    if (count($violations) > 0) {
      $messages = [];
      foreach ($violations as $violation) {
        $messages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
      }
      throw new BadRequestHttpException(implode('. ', $messages));
    }
  }
}