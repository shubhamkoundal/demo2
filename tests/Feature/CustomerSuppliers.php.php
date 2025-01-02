<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;

class CustomerSuppliers extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
     public function setup(): void
    {
        parent::setup();
        $this->account = Account::factory()->create();
        $this->roles   = Role::factory(4)->create(['account_id' => $this->account->id]);
    }
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
