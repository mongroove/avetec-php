<?php

namespace Avetec;

use InvalidArgumentException;

class UrlBuilder
{
    private string $domain;
    private string $secretKey;
    private bool $useHttps;
    private bool $useBase64 = false;

    public function __construct(string $domain, string $secretKey = '', $useHttps = true)
    {
        $this->domain = $domain;
        $this->validateDomain($this->domain);
        $this->secretKey = $secretKey;
        $this->useHttps = $useHttps;
    }

    private function validateDomain(string $domain): void
    {
        $DOMAIN_PATTERN = "/^(?:[a-z\d\-_]{1,62}\.){0,125}(?:[a-z\d](?:\-(?=\-*[a-z\d])|[a-z]|\d){0,62}\.)[a-z\d]{1,63}$/";

        if (! preg_match($DOMAIN_PATTERN, $domain)) {
            throw new InvalidArgumentException('Domain must be passed in as fully-qualified '.
                'domain name and should not include a protocol or any path element, i.e. '.
                '"example.domain.com".');
        }
    }

    public function setSecretKey(string $key): void
    {
        $this->secretKey = $key;
    }

    public function setUseHttps(bool $useHttps): void
    {
        $this->useHttps = $useHttps;
    }

    public function createURL(string $fileID, string $format = 'png', array $params = [], string $slug = '') : string
    {
        $scheme = $this->useHttps ? 'https' : 'http';
        $domain = $this->domain;
        $useBase64 = $this->useBase64;

        $urlHelper = new UrlHelper($domain, $fileID, $format, $this->secretKey, $params, $slug, $scheme, $useBase64);

        return $urlHelper->getURL();
    }

    public function setUseBase64(bool $useBase64): UrlBuilder
    {
        $this->useBase64 = $useBase64;
        return $this;
    }
}