<?php
namespace EightSelect\Components;

use Aws\S3\S3Client;

class AWSUploader
{
    /**
     * @param $filename
     * @param mixed $storage
     */
    public static function upload($filename, $storage)
    {
        $config = Shopware()->Config();

        $accessKey = $config->get('8s_aws_access_key');
        $secretKey = $config->get('8s_aws_secret_key');
        $bucket = $config->get('8s_aws_bucket');
        $region = $config->get('8s_aws_region');
        $prefix = $config->get('8s_aws_prefix');

        // Instantiate an Amazon S3 client.
        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        $key = $prefix ? $prefix . '/' . $filename : $filename;

        try {
            $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => fopen($storage . $filename, 'r'),
                'ACL'    => 'public-read',
            ]);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file.\n";
        }
    }
}
