<?php
namespace CseEightselectBasic\Components;

use Aws\S3\S3Client;

class AWSUploader
{
    /**
     * @param  string $filename
     * @param  string $storage
     * @param  string $feedId
     * @param  string $feedType
     * @throws \Enlight_Exception
     */
    public static function upload($filename, $storage, $feedId, $feedType)
    {
        // needs to be loaded here, because Shopware and AWS use different versions of Guzzle
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        $bucket = '__SUBDOMAIN__.8select.io';
        $region = 'eu-central-1';
        $prefix = $feedId . '/' . $feedType . '/' . date('Y') . '/' . date('m') . '/' . date('d');

        $s3 = new S3Client([
            'version'     => '2006-03-01',
            'region'      => $region,
            'credentials' => array(
                'key' => '__S3_PLUGIN_USER_ACCESS_KEY__',
                'secret' => '__S3_PLUGIN_USER_ACCESS_KEY_SECRET__',
            ),
        ]);

        $key = $prefix . '/' . $filename;

        try {
            $s3->putObject([
                'ACL'    => 'bucket-owner-full-control',
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => fopen($storage . $filename, 'r'),
            ]);
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error('Upload des Export auf AWS S3 fehlgeschlagen.');
            Shopware()->PluginLogger()->error($exception);

            throw $exception;
        }
    }
}
