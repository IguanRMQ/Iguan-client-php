<?php

namespace Test\Integrated;

use Iguan\Common\Data\JsonDataDecoder;
use Iguan\Event\Common\CommonAuth;
use Iguan\Event\Dispatcher\EventDispatcher;
use Iguan\Event\Subscriber\AuthException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Exception;
use Test\Integrated\src\MyEvent;

class SubscribeTest extends TestCase
{
    /**
     * @throws \Iguan\Common\Data\JsonException
     */
    public function testInvoking()
    {
        $strategy = new CliTestCommunicateStrategy();
        $dispatcher = new EventDispatcher('tag', $strategy);
        $event = new MyEvent();
        $event->setToken("some.event");
        $event->setPayload(['test' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();
        echo $output;
        $decoder = new JsonDataDecoder(true);
        $bundle = $decoder->decode($output);

        $this->assertEquals($event->pack()->asArray(), $bundle);
    }

    public function testEmptyOut()
    {
        $strategy = new CliTestCommunicateStrategy();
        $dispatcher = new EventDispatcher('tag', $strategy);
        $event = new MyEvent();
        $event->setToken("broken.event");
        $event->setPayload(['test' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();

        $this->assertEmpty($output);
    }

    public function testFailedAuth()
    {
        $strategy = new CliTestCommunicateStrategy();
        $strategy->setAuth(new CommonAuth('token'));
        $dispatcher = new EventDispatcher('tag', $strategy);
        $event = new MyEvent();
        $event->setToken("some.event");
        $event->setPayload(['test' => 'payload']);
        $dispatcher->dispatch($event);
        $output = $strategy->getLastRunOutput();
        $this->assertContains('Uncaught Iguan\Event\Subscriber\AuthException: Incoming auth does not match with configured value.', $output);
    }
}