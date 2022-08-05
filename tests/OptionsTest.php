<?php

namespace cURL\Tests;

use cURL\Options;
use cURL\Exception;

class OptionsTest extends TestCase
{
    public function testConstruction()
    {
        $opts = new Options();
        $this->assertInstanceOf('cURL\Options', $opts);
    }

    public function testToArray()
    {
        $opts = new Options();
        $array = $opts->toArray();

        $this->assertIsArray($array);
        $this->assertEmpty($array);
    }

    /**
     * @throws Exception
     */
    protected function assertsForSet(Options $opts)
    {
        $array = $opts->toArray();
        $this->assertCount(2, $array);

        $values = array(
            CURLOPT_TIMEOUT => 123,
            CURLOPT_USERAGENT => 'browser'
        );

        foreach ($values as $key => $value) {
            $this->assertTrue($opts->has($key));
            $this->assertEquals($value, $opts->get($key));
            $this->assertEquals($value, $array[$key]);
        }

        $this->assertFalse($opts->has(CURLOPT_RETURNTRANSFER));
    }

    public function testMissingOption()
    {
        $opts = new Options();
        $e = null;
        try {
            $opts->get(CURLOPT_ENCODING);
        } catch (Exception $e) {
        }
        $this->assertInstanceOf('cURL\Exception', $e);
    }

    /**
     * @throws Exception
     */
    public function testSingleSet()
    {
        $opts = new Options();
        $opts->set(CURLOPT_TIMEOUT, 123);
        $opts->set(CURLOPT_USERAGENT, 'browser');
        $this->assertsForSet($opts);
    }

    /**
     * @throws Exception
     */
    public function testArraySet()
    {
        $opts = new Options();
        $opts->set(
            array(
                CURLOPT_TIMEOUT => 123,
                CURLOPT_USERAGENT => 'browser'
            )
        );
        $this->assertsForSet($opts);
    }

    /**
     * @throws Exception
     */
    public function testIntelligentSet()
    {
        $opts = new Options();
        $opts->setTimeout(123);
        $opts->setUserAgent('browser');

        $e = null;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $opts->setUserAgentt('something');
        } catch (Exception $e) {
        }
        $this->assertInstanceOf('cURL\Exception', $e);
        $this->assertsForSet($opts);
    }

    /**
     * @throws Exception
     */
    public function testFluentSetters()
    {
        $opts = new Options();
        $opts->setTimeout(123)->setUserAgent('browser');
        $this->assertEquals(123, $opts->get(CURLOPT_TIMEOUT));
        $this->assertEquals('browser', $opts->get(CURLOPT_USERAGENT));

        $opts = new Options();
        $opts->set(CURLOPT_TIMEOUT, 123)->set(CURLOPT_USERAGENT, 'browser');
        $this->assertEquals(123, $opts->get(CURLOPT_TIMEOUT));
        $this->assertEquals('browser', $opts->get(CURLOPT_USERAGENT));
    }

    public function testRemove()
    {
        $opts = new Options();
        $opts->set(CURLOPT_TIMEOUT, 123);
        $opts->set(CURLOPT_USERAGENT, 'browser');

        $this->assertTrue($opts->has(CURLOPT_TIMEOUT));
        $opts->remove(CURLOPT_TIMEOUT);
        $this->assertFalse($opts->has(CURLOPT_TIMEOUT));

        $this->assertTrue($opts->has(CURLOPT_USERAGENT));
        $opts->remove(CURLOPT_USERAGENT);
        $this->assertFalse($opts->has(CURLOPT_USERAGENT));

        $this->assertEmpty($opts->toArray());
    }
}
