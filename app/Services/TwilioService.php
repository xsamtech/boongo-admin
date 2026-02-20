<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class TwilioService
{
    protected $twilioClient;
    protected $whatsappFrom;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');

        $this->twilioClient = new Client($sid, $token);
        $this->whatsappFrom = config('services.twilio.whatsapp_from');
    }

    /**
     * Envoie un message WhatsApp.
     * 
     * @param string $to
     * @param string $message
     * @return bool
     */
    public function sendWhatsAppMessage(string $to, string $message)
    {
        try {
            // Envoie le message via WhatsApp
            $this->twilioClient->messages->create(
                'whatsapp:' . $to, // Numéro de destinataire
                [
                    'from' => 'whatsapp:' . $this->whatsappFrom, // Ton numéro WhatsApp
                    'body' => $message
                ]
            );

            return true;
        } catch (Exception $e) {
            // Si le numéro n'est pas un numéro WhatsApp valide ou autre erreur
            return false;
        }
    }
}
