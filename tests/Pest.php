<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidationError', function () {
    return $this->toHaveKey('error');
});

expect()->extend('toHaveSuccessStructure', function () {
    return $this->toHaveKeys(['message', 'data']);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createMockRequest(array $data = [])
{
    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->shouldReceive('all')->andReturn($data);
    $request->shouldReceive('only')->andReturn($data);
    
    foreach ($data as $key => $value) {
        $request->shouldReceive('get')->with($key)->andReturn($value);
        $request->{$key} = $value;
    }
    
    return $request;
}

function createMockHttpClient($responses = [])
{
    $mock = new \GuzzleHttp\Handler\MockHandler($responses);
    $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
    return new \GuzzleHttp\Client(['handler' => $handlerStack]);
}
