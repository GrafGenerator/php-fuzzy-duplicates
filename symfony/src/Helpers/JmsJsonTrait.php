<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 22:04
 */

namespace App\Helpers;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait JmsJsonTrait
 * @package App\Helpers
 * @property ContainerInterface $container
 */
trait JmsJsonTrait
{
    /**
     * @param $result mixed
     * @return Response
     */
    protected function jmsJson($result) {
        $serializer = $this->container->get("jms_serializer");
        $response = $serializer->serialize($result, "json");

        $jsonResponse = new Response($response, Response::HTTP_OK);
        $jsonResponse->headers->set("Content-Type", "application/json");

        return $jsonResponse;
    }
}