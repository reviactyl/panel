<?php

namespace Tests\Integration\Api\Client\Server\Files;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DownloadFileTest extends ClientApiIntegrationTestCase
{
    public function test_download_token_preserves_percent_sequences_in_file_path(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $path = '/literal%2Fspace%20percent%25dot%2ename.txt';

        $response = $this->actingAs($user)
            ->getJson($this->link($server, '/files/download').'?'.http_build_query(['file' => $path], encoding_type: PHP_QUERY_RFC3986))
            ->assertOk();

        $url = $response->json('attributes.url');
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($server->node->getDecryptedKey()));
        /** @var Plain $token */
        $token = $config->parser()->parse($query['token']);

        $this->assertSame($path, $token->claims()->get('file_path'));
    }
}
