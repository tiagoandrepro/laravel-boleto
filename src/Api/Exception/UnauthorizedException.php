<?php

namespace Eduardokum\LaravelBoleto\Api\Exception;

use Eduardokum\LaravelBoleto\Exception\BaseException;

/**
 * Não armazena certificado, chave nem senha: esses valores podem conter
 * material PEM sensível e exceptions são rotineiramente serializadas por
 * handlers de erro (Sentry, logs estruturados).
 */
class UnauthorizedException extends BaseException
{
    private $baseUrl;

    private $conta;

    public function __construct($baseUrl, $conta)
    {
        parent::__construct('Unauthorized', 401);
        $this->baseUrl = $baseUrl;
        $this->conta = $conta;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     *
     * @return UnauthorizedException
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * @param mixed $conta
     *
     * @return UnauthorizedException
     */
    public function setConta($conta)
    {
        $this->conta = $conta;

        return $this;
    }
}
