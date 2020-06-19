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

use Dubture\Monolog\Reader\ReverseLogReader;
use Dubture\Monolog\Reader\LogReader;


/**
 * @author Robert Gruendler <r.gruendler@gmail.com>
 */
class LogReaderTest extends \PHPUnit_Framework_TestCase
{
    private $reader;

    public function setUp()
    {
        $file = __DIR__ . '/../../../../files/test.log';
        $this->reader = new LogReader($file, 0);
    }

    public function testReader()
    {
        $log = $this->reader[0];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('test', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('foobar', $log['message']);
        $this->assertArrayHasKey('foo', $log['context']);

        $log = $this->reader[1];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('aha', $log['logger']);
        $this->assertEquals('DEBUG', $log['level']);
        $this->assertEquals('foobar', $log['message']);
        $this->assertArrayNotHasKey('foo', $log['context']);

        $log = $this->reader[2];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('context', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('multicontext', $log['message']);
        $this->assertArrayHasKey('foo', $log['context'][0]);
        $this->assertArrayHasKey('bat', $log['context'][1]);

        $log = $this->reader[3];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('context', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('multicontext', $log['message']);
        $this->assertArrayHasKey('foo', $log['context'][0]);
        $this->assertArrayHasKey('stuff', $log['context'][0]);
        $this->assertArrayHasKey('bat', $log['context'][1]);

        $log = $this->reader[4];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('context', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('multicontext with empty', $log['message']);
        $this->assertArrayHasKey('foo', $log['context'][0]);
        $this->assertArrayHasKey('stuff', $log['context'][0]);
        $this->assertEmpty($log['context'][1]);

        $log = $this->reader[5];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('context', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('multicontext with spaces', $log['message']);
        $this->assertArrayHasKey('foo', $log['context'][0]);
        $this->assertArrayHasKey('stuff', $log['context'][0]);
        $this->assertArrayHasKey('bat', $log['context'][1]);

        $log = $this->reader[6];

        $this->assertInstanceOf('\DateTime', $log['date']);
        $this->assertEquals('extra', $log['logger']);
        $this->assertEquals('INFO', $log['level']);
        $this->assertEquals('context and extra', $log['message']);
        $this->assertArrayHasKey('foo', $log['context'][0]);
        $this->assertArrayHasKey('stuff', $log['context'][0]);
        $this->assertArrayHasKey('bat', $log['context'][1]);
        $this->assertArrayHasKey('weebl', $log['extra'][0]);
        $this->assertArrayHasKey('lobob', $log['extra'][1]);
        
    }

    public function testIterator()
    {
        $lines = array();
        $keys = array();

        $this->assertEquals(7, count($this->reader));

        foreach ($this->reader as $i => $log) {
            $test = $this->reader[0];
            $lines[] = $log;
            $keys[] = $i;
        }

        $this->assertEquals(array(0, 1, 2, 3, 4, 5, 6), $keys);
        $this->assertEquals('test', $lines[0]['logger']);
        $this->assertEquals('aha', $lines[1]['logger']);
        $this->assertEquals('context', $lines[2]['logger']);
        $this->assertEquals('context', $lines[3]['logger']);
        $this->assertEquals('context', $lines[4]['logger']);
        $this->assertEquals('context', $lines[5]['logger']);
        $this->assertEquals('extra', $lines[6]['logger']);

    }

    /**
     * @expectedException RuntimeException
     */
    public function testException()
    {
        $this->reader[9] = 'foo';

    }
}
