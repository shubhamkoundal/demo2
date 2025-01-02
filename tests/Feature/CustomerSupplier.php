<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
// use PHPUnit\Framework\TestCase;
// use Inertia\Inertia;
use App\Models\Checkin;
use App\Models\Contact;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Actions\Tec\PrepareOrder;
use App\Http\Requests\CheckinRequest;
use App\Http\Controllers\CheckinController;
use App\Http\Resources\CheckinResource;
use App\Http\Resources\CheckinCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CustomerSupplier extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
   public function test_example()
{
    // Simulate authentication if necessary
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    // Mock request with sample parameters
    $request = new \Illuminate\Http\Request([
        'draft' => true,
        'search' => 'keyword',
        'trashed' => false,
    ]);

    $controller = new CheckinController();
     $response = $controller->index($request);

    // Assert the response is an instance of Inertia\Response
    $this->assertInstanceOf(\Inertia\Response::class, $response);
}
}