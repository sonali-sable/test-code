<?php

namespace Drupal\gazette_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GazetteApiController extends ControllerBase {

  protected $httpClient;

  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  public function content($page) {
    $notices = $this->fetchNotices($page);

    $build = [
      '#theme' => 'gazette_api_template',
      '#notices' => $notices,
    ];

    
    $build['pager'] = [
      '#markup' => theme('pager'),
    ];

    return $build;
  }

  private function fetchNotices($page) {
    $base_url = 'https://www.thegazette.co.uk/all-notices/notice/data.json';
    try {
      $response = $this->httpClient->get($base_url, ['query' => ['results-page' => $page, 'verify' => FALSE]]);
      $data = json_decode($response->getBody(), TRUE);

     
      return $data;
    } catch (\Exception $e) {
      
      \Drupal::logger('gazette_api')->error('Error fetching notices: ' . $e->getMessage());
      return [];
    }
  }
}
