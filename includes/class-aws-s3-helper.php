<?php
namespace AuctionMarketplace;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

defined('ABSPATH') || exit;

/**
 * Uploads files to AWS S3 under the {vin}/ structure.
 */
class Aws_S3_Helper {

    private S3Client $s3;
    private string $bucket;

    public function __construct() {
        $this->bucket = defined('AUCTION_S3_BUCKET') ? AUCTION_S3_BUCKET : '';
        $this->s3 = new S3Client([
            'region'  => defined('AUCTION_S3_REGION') ? AUCTION_S3_REGION : 'eu-north-1',
            'version' => 'latest',
            'credentials' => [
                'key'    => AUCTION_S3_KEY,
                'secret' => AUCTION_S3_SECRET,
            ],
        ]);
    }

    /**
     * Uploads image content to S3
     */
    public function upload_image(string $vin, string $url): ?string {
        $image_data = wp_remote_get($url);
        if (is_wp_error($image_data)) return null;

        $body = wp_remote_retrieve_body($image_data);
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $key = "{$vin}/" . md5($url) . '.' . $ext;

        try {
            $this->s3->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $key,
                'Body'        => $body,
                // 'ACL'         => 'public-read',
                'ContentType' => 'image/jpeg'
            ]);
            return $key;
        } catch (AwsException $e) {
            log_debug('AWS Upload Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Uploads image content to S3
     */
    public function send_images_to_lambda($vin, $photos): ?array {
        if (empty($vin) || empty($photos)) {
            log_debug("Invalid VIN or photos for Lambda upload: VIN = $vin, Photos = " . print_r($photos, true));
            return null;
        }

        if (!is_array($photos)) {
            log_debug("Photos is not an array for VIN $vin: " . print_r($photos, true));
            return null;
        }

        try {
            $response = wp_remote_post('https://38gowup0sf.execute-api.eu-north-1.amazonaws.com/production/uploads', [
                'method'  => 'POST',
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => wp_json_encode([
                    'vin'           => $vin,
                    'photo_urls'    => $photos
                ]),
                'timeout' => 30
            ]);

            if (is_wp_error($response)) {
                log_debug("Lambda error for VIN $vin: " . $response->get_error_message());
                return null;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            log_debug("Lambda response for VIN $vin: " . print_r($data, true));

            $uploaded = $data['s3_keys'] ?? [];

            return !empty($uploaded) ? $uploaded : null;
        } catch (\Exception $e) {
            log_debug("Exception in send_images_to_lambda for VIN $vin: " . $e->getMessage());
            return null;
        }
    }

    public function get_s3_url(string $key): string {
        return "https://{$this->bucket}.s3.amazonaws.com/{$key}";
    }
}
