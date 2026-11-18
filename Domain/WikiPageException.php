<?php

namespace Kanboard\Plugin\Wiki\Domain;

final class WikiPageException extends \RuntimeException
{
    private $errorCode;
    private $errorDetails;

    public function __construct($errorCode, $message, array $errorDetails = array(), ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = (string) $errorCode;
        $this->errorDetails = $errorDetails;
    }

    public function errorCode()
    {
        return $this->errorCode;
    }

    public function errorDetails()
    {
        return $this->errorDetails;
    }
}
