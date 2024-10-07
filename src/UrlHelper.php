<?php

namespace Avetec;

use InvalidArgumentException;

class UrlHelper
{
    private string $apiVersion = "v1";
    private string $domain;
    private string $fileID;
    private string $format;
    private string $scheme;
    private string $secretKey;
    private array $params;
    private string $slug;
    private string $useBase64;

    public function __construct(string $domain, string $fileID, string $format, string $secretKey = '', array $params = [], string $slug = '', string $scheme = 'http', $useBase64 = false)
    {
        $this->domain = $domain;
        $this->fileID = $this->validateFileID($fileID) ;
        $this->format = $this->validateFormat($format);
        $this->secretKey = $secretKey;
        $this->params = $params;
        $this->slug = $this->validateSlug($slug);
        $this->scheme = $scheme;
        $this->useBase64 = $useBase64;
    }

    public function validateFileID(string $uuid) : string
    {
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $uuid) === 0) {
            throw new InvalidArgumentException('fileID must be a valid UUID');
        }
        return $uuid;
    }

    public function validateSlug(string $text) : string
    {
        $slug = strtolower($text);
        $slug = trim($slug);
        $slug = preg_replace('/[^\w\s-]/', '', $slug);
        $slug = preg_replace('/[\s_-]+/', '-', $slug);
        $slug = trim($slug, "-");

        if ($slug !== $text) {
            throw new InvalidArgumentException('slug is not valid');
        }
        return $text;
    }

    public function validateFormat(string $format) : string
    {
        if (!preg_match('/^[a-z]{3,4}$/', $format)) {
            throw new InvalidArgumentException('format is not valid.');
        }
        return $format;
    }

    public function setParameter(string $key, mixed $value): void
    {
        if ($key && ($value || $value === 0)) {
            $this->params[$key] = $value;
        } else {
            if (array_key_exists($key, $this->params)) {
                unset($this->params[$key]);
            }
        }
    }

    public function deleteParameter(string $key): void
    {
        unset($this->params[$key]);
    }

    /**
     * @throws \JsonException
     */
    public function getURL(): string
    {
        $query = "";

        if ($this->params) {

            $params = $this->params;

            if (isset($params['s'])) {
                throw new InvalidArgumentException('Parameter `s` (seal) is not allowed.');
            }

            if ($this->secretKey) {
                $params['s'] = self::getSeal($params, $this->secretKey);
            }

            if ($this->useBase64) {
                $params = ['bc' => self::base64urlEncode(json_encode($params, JSON_THROW_ON_ERROR))];
            }

            $query = '?' . http_build_query($params);
        }

        $url_parts = [
            'scheme' => $this->scheme,
            'host' => $this->domain,
            'path' => '/' . $this->apiVersion . ($this->slug ? '/' . $this->slug : '') . '/' . $this->fileID . '.' . $this->format,
            'query' => $query
        ];

        return self::joinURL($url_parts);
    }

    private static function joinURL(array $parts): string
    {
        return $parts['scheme'].'://'.$parts['host'].$parts['path'].$parts['query'];
    }

    public static function getSeal($params, $byteSecret): string
    {
        ksort($params);

        $bytesToSeal = mb_convert_encoding(implode('', array_map(
            static function ($key, $value) {
                if (gettype($value) === 'array') {
                    $value = json_encode((array)$value);
                }
                return $key . '=' . $value;
            },
            array_keys($params),
            $params
        )), 'UTF-8', 'ISO-8859-1');

        return hash_hmac('sha256', $bytesToSeal, $byteSecret);
    }

    public static function base64urlEncode(string $data, bool $usePadding = false): string
    {
        $encoded = strtr(base64_encode($data), '+/', '-_');

        return true === $usePadding ? $encoded : rtrim($encoded, '=');
    }

    public static function base64urlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if (false === $decoded) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        return $decoded;
    }

}