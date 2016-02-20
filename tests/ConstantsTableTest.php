<?php
namespace cURL\Tests;

use cURL\ConstantsTable;

class ConstantsTableTest extends \PHPUnit_Framework_TestCase
{
    public function testFindNumericValue()
    {
        $this->assertEquals(CURLOPT_TIMEOUT, ConstantsTable::findNumericValue('TiMEOUT'));
        $this->assertEquals(CURLOPT_FTP_CREATE_MISSING_DIRS, ConstantsTable::findNumericValue('FTPCreateMissingDirs'));
    }
}
