<?php

namespace App\Services\Media;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CloudinaryService
{
    public function uploadImage(UploadedFile $file, string $folder): array
    {
        $config = $this->config();
        $timestamp = time();
        $paramsToSign = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];

        $signature = $this->sign($paramsToSign, $config['api_secret']);

        try {
            $response = $this->http()
                ->asMultipart()
                ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                ->post("https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/upload", [
                    'api_key' => $config['api_key'],
                    'timestamp' => $timestamp,
                    'folder' => $folder,
                    'signature' => $signature,
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException($this->connectionHelpMessage($e->getMessage()));
        }

        if (! $response->successful()) {
            throw new RuntimeException('Echec upload Cloudinary: '.$response->body());
        }

        return $response->json();
    }

    public function destroyImage(string $publicId): void
    {
        if ($publicId === '') {
            return;
        }

        $config = $this->config();
        $timestamp = time();
        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        $signature = $this->sign($paramsToSign, $config['api_secret']);

        try {
            $this->http()->asForm()->post("https://api.cloudinary.com/v1_1/{$config['cloud_name']}/image/destroy", [
                'api_key' => $config['api_key'],
                'public_id' => $publicId,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);
        } catch (ConnectionException $e) {
            Log::warning('Cloudinary destroy: echec reseau/SSL, suppression locale conservee.', [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Client HTTP vers l'API Cloudinary (verification SSL configurable pour Windows / PHP sans CA bundle).
     */
    private function http()
    {
        return Http::withOptions(['verify' => $this->httpVerifySsl()]);
    }

    private function httpVerifySsl(): bool
    {
        $raw = env('CLOUDINARY_HTTP_VERIFY');
        if ($raw === null || $raw === '') {
            return true;
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    private function connectionHelpMessage(string $detail): string
    {
        $hint = 'Souvent du a un certificat SSL non reconnu par PHP sur Windows : installez cacert.pem et renseignez curl.cainfo dans php.ini. En developpement local uniquement, CLOUDINARY_HTTP_VERIFY=false dans .env contourne la verification (deconseille en production).';

        return 'Connexion HTTPS vers Cloudinary impossible. '.$hint.' Detail technique : '.$detail;
    }

    private function config(): array
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');

        if (! $cloudName || ! $apiKey || ! $apiSecret) {
            throw new RuntimeException(
                'Configuration Cloudinary incomplete : definissez CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY et CLOUDINARY_API_SECRET dans le fichier .env du backend.'
            );
        }

        return [
            'cloud_name' => $cloudName,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
        ];
    }

    private function sign(array $params, string $apiSecret): string
    {
        ksort($params);
        $toSign = urldecode(http_build_query($params));

        return sha1($toSign.$apiSecret);
    }
}
