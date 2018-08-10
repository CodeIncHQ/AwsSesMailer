<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     10/08/2018
// Project:  AwsSesMailer
//
declare(strict_types=1);
namespace CodeInc\AwsSesMailer;
use Aws\Result;
use Aws\Ses\Exception\SesException;
use Aws\Ses\SesClient;
use CodeInc\Mailer\Interfaces\EmailInterface;
use CodeInc\Mailer\Interfaces\MailerInterface;
use CodeInc\MailerEmailToMimeMessage\EmailToMimeMessage;


/**
 * Class AwsSesMailer
 *
 * @package CodeInc\AwsSesMailer
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class AwsSesMailer implements MailerInterface
{
    /**
     * @var SesClient
     */
    private $sesClient;

    /**
     * @var Result|null
     */
    private $lastSentResult;

    /**
     * AwsSesMailer constructor.
     *
     * @param SesClient $sesClient
     */
    public function __construct(SesClient $sesClient)
    {
        $this->sesClient = $sesClient;
    }

    /**
     * @param EmailInterface $email
     * @link https://docs.aws.amazon.com/ses/latest/APIReference/API_SendRawEmail.html
     */
    public function send(EmailInterface $email):void
    {
        try {
            $this->lastSentResult = $this->sesClient->sendRawEmail([
                'RawMessage' => [
                    'Data' => (new EmailToMimeMessage($email))->getMimeMessage()->toString()
                ]
            ]);
        }
        catch (SesException $exception) {
            throw new \RuntimeException(sprintf(_("Erreur lors de l'envoi de l'email '%s' : %s"),
                $email->getSubject(), $exception->getAwsErrorMessage()),
                0, $exception);
        }
    }

    /**
     * @return Result|null
     */
    public function getLastSentResult():?Result
    {
        return $this->lastSentResult;
    }

    /**
     * @return Result|null
     */
    public function getLastSentId():?string
    {
        return $this->lastSentResult ? $this->lastSentResult->get('MessageId') : null;
    }
}