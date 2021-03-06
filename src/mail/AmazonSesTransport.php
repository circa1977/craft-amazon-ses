<?php
/**
 * @link      https://github.com/putyourlightson/craft-amazon-ses
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\amazonses\mail;

use Aws\Ses\SesClient;

/**
 * Amazon SES Transport
 *
 * @author    PutYourLightsOn
 * @package   Amazon SES
 * @since     1.0.0
 */

class AmazonSesTransport extends Transport
{
    // Properties
    // =========================================================================

    /**
     * @var SesClient
     */
    private $_client;

    // Public Methods
    // =========================================================================

    /**
     * Constructor
     *
     * @param SesClient $client
     */
    public function __construct(SesClient $client)
    {
        $this->_client = $client;
    }

    /**
     * @inheritdoc
     */
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null): int
    {
        $data = $this->_formatMessage($message);

        try {
            $this->_client->sendRawEmail($data);
        }
        catch (AwsException $e) {
            return 0;
        }

        return count($message->getTo());
    }

    // Private Methods
    // =========================================================================

    /**
     * @param \Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function _formatMessage(\Swift_Mime_SimpleMessage $message): array
    {
        // Get from as string
        $from = is_array($message->getFrom()) ? key($message->getFrom()) : $message->getFrom();

        $data = [
            'Source' => $from,
            'ReturnPath' => $from,
            'ReplyToAddresses' => is_array($message->getReplyTo()) ? array_keys($message->getReplyTo()) : $from,
            'Destination' => [
                'ToAddresses' => array_keys($message->getTo()),
                'CcAddresses' => is_array($message->getCc()) ? array_keys($message->getCc()) : [],
                'BccAddresses' => is_array($message->getBcc()) ? array_keys($message->getBcc()) : [],
            ],
            'RawMessage' => [
                'Data' => $message->toString()
            ],
        ];

        return $data;
    }
}
