<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
class RedisCacheTest extends TestCase
{
    public function setUp(): void {
        parent::setUp();
        config(['cache.default' => 'redis']);
        Cache::flush();
        Redis::connection()->flushdb();
    }

    /**
     * Test setting, getting, and removing a cache item in Redis.
     *
     * @return void
     */
    public function testCanSetAndGetAndRemoveCache() {
        $key = 'test_key';
        $value = 'test_value';

        // Set cache
        Cache::put($key, $value, 10);
        $cachedValue = Cache::get($key);

        $this->assertEquals($value, $cachedValue);

        // Remove cache
        Cache::forget($key);
        $cachedValueAfterForget = Cache::get($key);
        $this->assertNull($cachedValueAfterForget);
    }
}
