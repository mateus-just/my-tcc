<?php

namespace Drupal\api_candidates\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "api_candidate",
 *   label = @Translation("Candidates"),
 *   uri_paths = {
 *     "canonical" = "/api/candidate/{id}"
 *   }
 * )
 */
class CandidatesResource extends ResourceBase {

  /**
   * Constructs a new CandidatesResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('candidate')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param mixed $id
   *   The ID of the entity.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get($id = NULL) {
    if ($id) {
      $entity = \Drupal::entityTypeManager()->getStorage('candidate')->load($id);
      if ($entity) {
        return new ResourceResponse($entity, 200);
      }
      else {
        throw new BadRequestHttpException(t('Entity with ID @id was not found', ['@id' => $id]));
      }
    }
    else {
      throw new BadRequestHttpException(t('ID parameter is missing'));
    }
  }
}