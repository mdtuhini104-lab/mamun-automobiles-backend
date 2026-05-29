<?php

namespace App\Services\Communication;

interface CommunicationProviderInterface
{
    /**
     * Sends a message to a recipient.
     *
     * @param string $recipient Phone number or email address
     * @param string $message The content body to send
     * @return bool True if successful, false otherwise
     */
    public function send(string $recipient, string $message): bool;
}
