<?php

namespace App\Services;

use Infobip\Api\SmsApi;
use Infobip\ApiException;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class InfobipService
{
    public function sendMessage($to, $messageText)
    {
        $configuration = new Configuration(
            host: config('services.infobip.base_url'),
            apiKey: config('services.infobip.api_key')
        );

        $sendSmsApi = new SmsApi(config: $configuration);

        $message = new SmsTextualMessage(
            destinations: [
                new SmsDestination(to: $to)
            ],
            from: 'Boongo',
            text: __('notifications.token_label_sms', ['token' => $messageText]),
        );

        $request = new SmsAdvancedTextualRequest(messages: [$message]);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);

            return [
                'success' => true,
                'message' => __('notifications.sms_sent_successfully'),
                'data'    => $smsResponse,
            ];
        } catch (ApiException $apiException) {
            return [
                'success' => false,
                'message' => __('notifications.create_user_SMS_failed'),
                'error'   => $apiException->getMessage(),
            ];
        }
    }
}
