<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testMainPagesAreReachable()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/pos')->assertStatus(200);
        $this->get('/booking')->assertStatus(200);
        $this->get('/staff')->assertStatus(200);
    }
}
