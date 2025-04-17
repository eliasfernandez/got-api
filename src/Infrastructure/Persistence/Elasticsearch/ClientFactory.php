<?php

namespace App\Infrastructure\Persistence\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ClientFactory
{
    public static function create(string $hosts, string $caBundle, string $password): Client
    {
        $hosts = array_map('trim', explode(',', $hosts));

        return ClientBuilder::create()
            ->setHosts($hosts)
            ->setSSLVerification()
            ->setCABundle(sprintf('%s/ca/ca.crt', $caBundle))
            ->setSSLCert(sprintf('%s/es01/es01.crt', $caBundle))
            ->setSSLKey(sprintf('%s/es01/es01.key', $caBundle))
            ->setBasicAuthentication('elastic', $password)
            ->build();
    }
}
