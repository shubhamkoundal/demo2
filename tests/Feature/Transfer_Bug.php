<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Actions\Tec\PrepareOrder;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransferResource;
use App\Http\Resources\TransferCollection;
use App\Http\Controllers\TransferController;


class Transfer_Bug extends TestCase
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

    $controller = new TransferController();
     $response = $controller->index($request);

    // Assert the response is an instance of Inertia\Response
    $this->assertInstanceOf(\Inertia\Response::class, $response);
}
}
