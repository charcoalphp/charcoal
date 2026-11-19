<?php

namespace Charcoal\Tests\App\Email;

use PHPMailer\PHPMailer\PHPMailer;

use Charcoal\Email\Email;
use Charcoal\Email\EmailLog;
use Charcoal\Email\Services\Tracker;
use Charcoal\Factory\FactoryInterface;
use Charcoal\Tests\AbstractTestCase;

/**
 * Test the Email::logSend() method.
 */
class EmailLogSendTest extends AbstractTestCase
{
    public function testLogSendCreatesUniqueLogForEachRecipient()
    {
        $container  = $GLOBALS['container'];
        $logFactory = new EmailLogSpyFactory();

        $obj = new Email([
            'logger'             => $container['logger'],
            'config'             => $container['email/config'],
            'view'               => $container['email/view'],
            'template_factory'   => $logFactory,
            'queue_item_factory' => $logFactory,
            'log_factory'        => $logFactory,
            'tracker'            => new Tracker('', $logFactory)
        ]);

        $obj->setTo([
            'first@example.com',
            'second@example.com',
            'third@example.com'
        ]);
        $obj->setSubject('Test');

        $method = new \ReflectionMethod(Email::class, 'logSend');
        $method->setAccessible(true);
        $method->invoke($obj, true, 'message-log-id', new PHPMailer());

        $ids = array_map(function (EmailLogSpy $log) {
            return $log->id;
        }, $logFactory->logs);

        $this->assertSame([
            'message-log-id',
            'message-log-id-1',
            'message-log-id-2'
        ], $ids);
    }
}

/**
 * Email log implementation that records the assigned ID without saving.
 */
class EmailLogSpy
{
    /**
     * @var string|null
     */
    public $id;

    /**
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Ignore the other EmailLog setters.
     *
     * @param string $name
     * @param array  $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
    }

    /**
     * @return void
     */
    public function save()
    {
    }
}

/**
 * Factory that creates EmailLog spies.
 */
class EmailLogSpyFactory implements FactoryInterface
{
    /**
     * @var EmailLogSpy[]
     */
    public $logs = [];

    /**
     * @param string        $type
     * @param array|null    $args
     * @param callable|null $cb
     * @return EmailLogSpy
     */
    public function create($type, ?array $args = null, ?callable $cb = null)
    {
        $log = new EmailLogSpy();
        $this->logs[] = $log;
        return $log;
    }

    /**
     * @return string
     */
    public function baseClass()
    {
        return EmailLog::class;
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return EmailLog::class;
    }

    /**
     * @return array
     */
    public function arguments()
    {
        return [];
    }

    /**
     * @return callable|null
     */
    public function callback()
    {
        return null;
    }
}
