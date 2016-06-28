<?php
/**
 * Created by PhpStorm.
 * User: melon
 * Date: 6/28/16
 * Time: 10:34 AM
 */

namespace App\UUD\helpers;


class MailGun
{
    /**
     * @param $swift_message
     * @param $env_tags
     * @param $custom_tags
     * @return mixed
     */
    public static function generate_tagged_message($swift_message, $env_tags, $custom_tags)
    {
        $headers = $swift_message->getHeaders();
        if (!empty($env_tags)) {
            if (str_contains($env_tags, ',')) {
                foreach (explode(',', $env_tags) as $tag) {
                    $headers->addTextHeader('X-Mailgun-Tag', $tag);
                }
            } else {
                $headers->addTextHeader('X-Mailgun-Tag', $env_tags);
            }
        }
        if (!empty($custom_tags)) {
            if (is_array($custom_tags)) {
                foreach ($custom_tags as $tag) {
                    $headers->addTextHeader('X-Mailgun-Tag', $tag);
                }
            } else {
                $headers->addTextHeader('X-Mailgun-Tag', $custom_tags);
            }
        }
        return $swift_message;
    }
}