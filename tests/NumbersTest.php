<?php

use Illuminate\Http\Response;

class NumbersTest extends TestCase
{
    /**
     * 
     */
    public function testShouldReturnAllNumbers()
    {
        $response = $this->call('GET', 'api/numbers');

        $this->assertEquals(200, $response->getStatusCode());
    }
}