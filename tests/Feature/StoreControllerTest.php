<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_list_of_stores_with_pagination()
    {
        Store::factory(6)->create();
        // User::factory()->count(10)->withStore()->create();

        $response = $this->getJson(route('stores.index', ['page' => 1, 'items' => 5]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'successful',
                'message',
                'data' => [
                    'stores',
                    'hasMorePages',
                ],
                'status_code',
            ]);
    }

    public function test_there_are_no_stores_response_message()
    {

        $response = $this->getJson(route('stores.index', ['page' => 1, 'items' => 5]));

        $response->assertStatus(404)
            ->assertJson([
                'successful' => false,
                'message' => 'There are no stores available',
                'status_code' => 404,
            ]);
    }

    public function test_that_6th_product_does_not_exist_in_first_page()
    {
        Store::factory(6)->create();

        $response = $this->getJson(route('stores.index', ['page' => 1, 'items' => 5]));

        $response->assertStatus(200);

        $response->assertJsonCount(5, 'data.stores');

        $firstPageStoresIds = collect($response->json('data.stores'))->pluck('id')->toArray();

        $this->assertNotContains(6, $firstPageStoresIds);
    }

    public function test_it_can_create_store()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // $store = Store::factory()->create();

        $data = [
            'user_id' => $user->id,
            'name' => 'test store',
            'logo' => UploadedFile::fake()->image('logo.jpg'),
            'location' => 'test location',
            'description' => 'test description',
        ];

        $response = $this->postJson(route('stores.store'), $data);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'successful',
            'message',
            'data' => [
                'id',
                'name',
                'image_url',
                'location',
                'description',
            ],
            'status_code',
        ]);

        $this->assertDatabaseHas('stores', [
            'user_id' => $user->id,
            'name' => 'test store',
            'location' => 'test location',
            'description' => 'test description',
        ]);

        $uploadedFilePath = $response->json('data.image_url');

        $relativeFilePath = str_replace('storage/', '', parse_url($uploadedFilePath, PHP_URL_PATH));

        $this->assertTrue(Storage::disk('public')->exists($relativeFilePath));
    }

    public function test_it_fails_validation_when_required_fields_are_missing()
    {

        $response = $this->postJson(route('stores.store'), []);

        $response->assertStatus(400);

        $response->assertJsonValidationErrors(['name', 'logo', 'user_id']);

    }
}
