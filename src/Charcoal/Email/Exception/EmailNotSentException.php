<?php

namespace Charcoal\Email\Exception;

use RuntimeException;

/**
 * Email was not sent
 *
 * This exception is thrown when sending an email fails and there is absolutely no doubt no email has been sent.
 */
class EmailNotSentException extends RuntimeException implements ExceptionInterface
{
}
