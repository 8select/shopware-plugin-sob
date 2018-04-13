<?php
namespace EightSelect\Components;

use Aws\S3\S3Client;

class AWSUploader
{
    /**
     * @param $filename
     * @param mixed $storage
     * @param mixed $feedId
     * @param mixed $feedType
     */
    public static function upload($filename, $storage, $feedId, $feedType)
    {
        $bucket = 'productfeed.8select.io';
        $region = 'eu-central-1';
        $prefix = $feedId . '/' . $feedType . '/' . date('Y') . '/' . date('m') . '/' . date('d');

        // Instantiate an Amazon S3 client.
        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => $region,
        ]);

        $key = $prefix . '/' . $filename;

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
