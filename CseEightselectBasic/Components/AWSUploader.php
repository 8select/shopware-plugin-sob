<?php
namespace CseEightselectBasic\Components;

use Aws\S3\S3Client;

class AWSUploader
{
    /**
     * @param string $filename
     * @param string $feedId
     * @param string $feedType
     */
    public static function createStreamInstance($filename, $feedId, $feedType) {
        $bucket = '__SUBDOMAIN__.8select.io';
        $region = 'eu-central-1';
        $prefix = $feedId . '/' . $feedType . '/' . date('Y') . '/' . date('m') . '/' . date('d');
        $key = $prefix . '/' . $filename;

        $s3 = new S3Client([
            'version'     => '2006-03-01',
            'region'      => $region,
            'credentials' => array(
                'key' => '__S3_PLUGIN_USER_ACCESS_KEY__',
                'secret' => '__S3_PLUGIN_USER_ACCESS_KEY_SECRET__',
            ),
        ]);

        $context = stream_context_create([
            's3' => ['seekable' => true]
        ]);

        $s3->registerStreamWrapper();
        $stream = fopen('s3://' . $bucket . '/' . $key, 'w', false, $context);

        return $stream;
    }

    /**
     * @param string $message - logfile body
     * @param string $type - type of logfile, i.e. install, update, uninstall
     */
    public static function uploadLog($message, $type = 'install') {
        try {
            // needs to be loaded here, because Shopware and AWS use different versions of Guzzle
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }
            $bucket = '__SUBDOMAIN__.8select.io';
            $region = 'eu-central-1';
            $s3 = new S3Client([
                'version'     => '2006-03-01',
                'region'      => $region,
                'credentials' => array(
                    'key' => '__S3_PLUGIN_USER_ACCESS_KEY__',
                    'secret' => '__S3_PLUGIN_USER_ACCESS_KEY_SECRET__',
                ),
            ]);
            $timestampInMillis = round(microtime(true) * 1000);
            $key = 'shopware-plugin-log/' . $type . '/' . $timestampInMillis . '.log';
            $s3->putObject([
                'ACL'    => 'bucket-owner-full-control',
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => $message,
            ]);
        } catch (\Exception $ignore) {}
    }
}
