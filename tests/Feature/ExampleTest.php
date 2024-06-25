<?php declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Class ExampleTest.
 */
final class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(Response::HTTP_OK);
    }
}
