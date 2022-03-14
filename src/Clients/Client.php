<?php

namespace Shl\RoundTable\Clients;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Utils;
use Illuminate\Config\Repository;
use Shl\RoundTable\Endpoints\CustomerStocksEndpoint;
use Shl\RoundTable\Entity\RoundtablePayloadEntity;
use Shl\RoundTable\Enums\RoundtableUri;
use Shl\RoundTable\Exceptions\RoundtableMalformedPayloadException;
use Shl\RoundTable\Exceptions\RoundtableNoResponseException;
use Shl\RoundTable\Exceptions\RoundtableWrongResponseException;

class Client
{
    private const CIPHER = 'aes-256-cbc-hmac-sha256';

    private $client;
    private $config;
    private $secretKey;
    private $publicKey;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function obtainTokenUrl(string $email, string $name): ?string
    {
        $this->init();

        $payload = [
            'email' => $email,
            'name' => $name
        ];

        $signature = $this->getSignature(Utils::jsonEncode($payload));

        $response = $this->sendRequest(
            'POST',
            RoundtableUri::OBTAIN_TOKEN_URI,
            $payload,
            [
                'X-APP-KEY' => $this->publicKey,
                'X-APP-SIGNATURE' => $signature
            ]);

        return $response->get('link');
    }

    public function decryptRoundtablePayload(string $payload): RoundtablePayloadEntity
    {
        $this->init();

        try {
            $ivlen = openssl_cipher_iv_length(self::CIPHER);
            $encodedString = base64_decode($payload);
            $iv = substr($encodedString, 0, $ivlen);
            $encodedJson = substr($encodedString, $ivlen);
            $json = openssl_decrypt($encodedJson, self::CIPHER, $this->secretKey, OPENSSL_RAW_DATA, $iv);

            return new RoundtablePayloadEntity(json_decode($json, true));
        } catch (\Throwable $exception) {
            throw new RoundtableMalformedPayloadException($payload, 400, $exception);
        }
    }

    public function validateUserSignature(string $userId = null, ?string $signature = null): bool
    {
        if (!$userId || !$signature) {
            throw new RoundtableMalformedPayloadException();
        }

        $localSignature = $this->getSignature($userId);

        return $signature === $localSignature;
    }

    public function customerStocks(): CustomerStocksEndpoint
    {
        return new CustomerStocksEndpoint($this);
    }

    public function post(string $uri, array $payload, array $headers = []): Response
    {
        $this->init();

        $signature = $this->getSignature(Utils::jsonEncode($payload));

        $headers += [
            'X-APP-SIGNATURE' => $signature
        ];

        return $this->sendRequest('POST', $uri, $payload, $headers);
    }

    protected function sendRequest(string $method, string $uri, array $body, array $headers = []): Response
    {
        $this->init();

        $headers = \array_merge([
            'Content-Type' => 'application/json',
            'X-APP-KEY' => $this->publicKey
        ], $headers);

        $json = Utils::jsonEncode($body);

        if (!\App::isProduction()) {
            logger()->debug('Roundtable API Request', [
                'request_uri' => $uri,
                'request_method' => $method,
                'request_body' => $json,
            ]);
        }

        try {
            $request = new Request($method, $uri, $headers, $json);

            $response = $this->client->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
        } finally {
            if (null === $response) {
                \Log::error('Roundtable request does not contain response.', [
                    'uri' => $uri,
                    'headers' => $headers,
                    'json' => $json,
                    'exception' => $exception ?? null,
                ]);
                throw new RoundtableNoResponseException();
            }

            $content = $response->getBody()->getContents();

            if (!\App::isProduction()) {
                logger()->debug('Roundtable API Response', [
                    'request_uri' => $uri,
                    'request_method' => $method,
                    'request_body' => $json,
                    'response_code' => $response->getStatusCode(),
                    'response_body' => $content
                ]);
            }
        }

        if ($response->getStatusCode() !== 200) {
            throw new RoundtableWrongResponseException(
                sprintf('Expected http response code is 200 but actual is %s.', $response->getStatusCode())
            );
        }

        return new Response(Utils::jsonDecode($content, true));
    }

    protected function getSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secretKey);
    }

    private function init(): void
    {
        if (null === $this->client) {
            $apiUrl = $this->config->get('roundtable.api_url');
            $timeout = $this->config->get('roundtable.timeout');
            $this->secretKey = $this->config->get('roundtable.secret_key');
            $this->publicKey = $this->config->get('roundtable.public_key');

            assert(null !== $apiUrl, 'Roundtable API URL is not set.');
            assert(null !== $timeout, 'Roundtable timeout is not set.');
            assert(null !== $this->secretKey, 'Roundtable Secret Key is not set.');
            assert(null !== $this->publicKey, 'Roundtable Public Key is not set.');

            $this->client = new \GuzzleHttp\Client([
                'base_uri' => $apiUrl,
                'timeout' => $timeout,
            ]);
        }
    }
}
