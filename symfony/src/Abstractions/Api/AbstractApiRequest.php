<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 21:07
 */

namespace App\Abstractions\Api;


use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractApiRequest
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function setRequestStack(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    protected function getField(string $key, $defaultValue = null) {
        return $this->requestStack->getCurrentRequest()->get($key) ?? $defaultValue;
    }
}