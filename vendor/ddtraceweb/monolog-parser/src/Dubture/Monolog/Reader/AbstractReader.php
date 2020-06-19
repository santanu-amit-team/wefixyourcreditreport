<?php

/*
 * This file is part of the monolog-parser package.
 *
 * (c) Robert Gruendler <r.gruendler@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dubture\Monolog\Reader;

use Dubture\Monolog\Parser\LineLogParser;

/**
 * Class AbstractReader
 * @package Dubture\Monolog\Reader
 */
class AbstractReader
{

    /**
     * @param $days
     * @param $pattern
     *
     * @return LineLogParser
     */
    protected function getDefaultParser($days, $pattern)
    {
        return new LineLogParser($days, $pattern);
    }
}
