<?php

namespace Tests\Feature;

use Tests\TestCase;

final class ApiRootTest extends TestCase
{
    public function test_api_root_returns_service_metadata(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertExactJson([
                'name' => 'To-Do List API',
                'status' => 'ok',
            ]);
    }
}
