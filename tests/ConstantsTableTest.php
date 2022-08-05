<?php

namespace cURL\Tests;

use cURL\ConstantsTable;
use cURL\Exception;

class ConstantsTableTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testFindNumericValue()
    {
        $this->assertEquals(CURLOPT_TIMEOUT, ConstantsTable::findNumericValue('TiMEOUT'));
        $this->assertEquals(CURLOPT_FTP_CREATE_MISSING_DIRS, ConstantsTable::findNumericValue('FTPCreateMissingDirs'));
    }
}
