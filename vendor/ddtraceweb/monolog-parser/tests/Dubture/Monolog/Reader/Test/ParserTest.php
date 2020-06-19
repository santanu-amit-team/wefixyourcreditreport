<?php

/*
 * This file is part of the monolog-parser package.
 *
 * (c) Robert Gruendler <r.gruendler@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dubture\Monolog\Reader\Test;

use Dubture\Monolog\Parser\LineLogParser;

/**
 * @author Robert Gruendler <r.gruendler@gmail.com>
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser  = new LineLogParser();
    }

    public function logLineProvider()
    {
        return array(
            'simple' => array(
                'aha',
                'DEBUG',
                'foobar',
                array(),
                array(),
                '[%s] aha.DEBUG: foobar [] []'
            ),
            'default' => array(
                'test',
                'INFO',
                'foobar',
                array('foo' => 'bar'),
                array(),
                '[%s] test.INFO: foobar {"foo":"bar"} []'
            ),
            'multi_context' => array(
                'context',
                'INFO',
                'multicontext',
                array(array('foo' => 'bar'), array('bat' => 'baz')),
                array(),
                '[%s] context.INFO: multicontext [{"foo":"bar"},{"bat":"baz"}] []'
            ),
            'multi_context_empty' => array(
                'context',
                'INFO',
                'multicontext',
                array(array('foo' => 'bar'), array()),
                array(),
                '[%s] context.INFO: multicontext [{"foo":"bar"},[]] []'
            ),
            'multi_context_spaces' => array(
                'context',
                'INFO',
                'multicontext',
                array(array('foo' => 'bar', 'stuff' => 'and things'), array('bat' => 'baz')),
                array(),
                '[%s] context.INFO: multicontext [{"foo":"bar","stuff":"and things"},{"bat":"baz"}] []'
            ),
            'multi_context_message_spaces' => array(
                'context',
                'INFO',
                'multicontext with spaces',
                array(array('foo' => 'bar', 'stuff' => 'and things'), array('bat' => 'baz')),
                array(),
                '[%s] context.INFO: multicontext with spaces [{"foo":"bar","stuff":"and things"},{"bat":"baz"}] []'
            ),
            'extra_context' => array(
                'extra',
                'INFO',
                'context and extra',
                array(array('foo' => 'bar', 'stuff' => 'and things'), array('bat' => 'baz')),
                array(array('weebl' => 'bob'), array('lobob' => 'lo')),
                '[%s] extra.INFO: context and extra [{"foo":"bar","stuff":"and things"},{"bat":"baz"}] [{"weebl":"bob"},{"lobob":"lo"}]'
            ),
        );
    }

    public function daysLineProvider()
    {
        return array(
            array (
                'yesterday',
                '[%s] days.DEBUG: %s [] []',
            ),
            array (
                '2 days ago',
                '[%s] days.DEBUG: %s [] []',
            ),
            array (
                '3 days ago',
                '[%s] days.DEBUG: %s [] []',
            ),
            array (
                '1 week ago',
                '[%s] days.DEBUG: %s [] []',
            ),
            array (
                '1 month ago',
                '[%s] days.DEBUG: %s [] []',
            ),
            array (
                '1 year ago',
                '[%s] days.DEBUG: %s [] []',
            ),
        );
    }

    public function datedLineProvider()
    {
        $dynamic = $this->daysLineProvider();

        $array = array(
            array (
                false,
                '[1970-01-01 00:00:00] dates.DEBUG: epoch [] []'
            ),
            array (
                false,
                '[1955-11-05 19:00:00] dates.DEBUG: flux capacitor [] []'
            )
        );

        return array_merge($dynamic, $array);
    }

    /**
     * @dataProvider logLineProvider
     */
    public function testLineFormatter($logger, $level, $message, $context, $extra, $line)
    {
        $now  = new \DateTime();
        $line = sprintf($line, $now->format('Y-m-d H:i:s'));

        $log  = $this->parser->parse($line);

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals($logger, $log['logger']);
        $this->assertEquals($level, $log['level']);
        $this->assertEquals($message, $log['message']);
        $this->assertEquals($context, $log['context']);
        $this->assertEquals($extra, $log['extra']);
    }

    /**
     * @dataProvider daysLineProvider
     */
    public function testDaysFilter($time, $line)
    {
        $now  = new \DateTime();
        $date = new \DateTime($time);
        $line = sprintf($line, $date->format('Y-m-d H:i:s'), $time);

        $days = 1 + $date->diff($now)->days;
        $log  = $this->parser->parse($line, $days);

        $this->assertNotEmpty($log);
    }

    /**
     * @dataProvider datedLineProvider
     */
    public function testZeroForDaysReturnsAllLines($time, $line)
    {
        if (false !== $time) {
            $date = new \DateTime($time);
            $line = sprintf($line, $date->format('Y-m-d H:i:s'), $time);
        }

        $log = $this->parser->parse($line, 0);

        $this->assertNotEmpty($log);
    }
}
