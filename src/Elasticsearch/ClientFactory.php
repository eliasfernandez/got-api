<?php

namespace App\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;

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
