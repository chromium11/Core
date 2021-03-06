<?php

use App\Owner;
use App\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\RoleManager\app\Models\Role;
use LaravelEnso\TestHelper\app\Traits\SignIn;
use LaravelEnso\TestHelper\app\Traits\TestCreateForm;
use LaravelEnso\TestHelper\app\Traits\TestDataTable;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    use RefreshDatabase, SignIn, TestDataTable, TestCreateForm;

    private $role;
    private $faker;
    private $prefix = 'administration.owners';

    protected function setUp()
    {
        parent::setUp();

        // $this->withoutExceptionHandling();
        $this->signIn(User::first());
        $this->role = Role::first(['id']);
        $this->faker = Factory::create();
    }

    /** @test */
    public function store()
    {
        $postParams = $this->postParams();
        $response = $this->post(route('administration.owners.store', [], false), $postParams);
        $owner = Owner::whereName($postParams['name'])->first();

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'The entity was created!',
                'redirect' => 'administration.owners.edit',
                'id' => $owner->id,
            ]);
    }

    /** @test */
    public function edit()
    {
        $postParams = $this->postParams();
        $owner = Owner::create($postParams);

        $this->get(route('administration.owners.edit', $owner->id, false))
            ->assertStatus(200)
            ->assertJsonStructure(['form' => []]);
    }

    /** @test */
    public function update()
    {
        $postParams = $this->postParams();
        $owner = Owner::create($postParams)->append(['roleList']);
        $owner->name = 'edited';

        $this->patch(route('administration.owners.update', $owner->id, false), $owner->toArray())
            ->assertStatus(200)
            ->assertJson(['message' => __(config('enso.labels.savedChanges'))]);

        $this->assertEquals('edited', $owner->fresh()->name);
    }

    /** @test */
    public function destroy()
    {
        $postParams = $this->postParams();
        $owner = Owner::create($postParams);

        $this->delete(route('administration.owners.destroy', $owner->id, false))
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'redirect']);

        $this->assertNull($owner->fresh());
    }

    /** @test */
    public function cant_destroy_if_has_users_attached()
    {
        $postParams = $this->postParams();
        $owner = Owner::create($postParams);
        $this->attachUser($owner);

        $this->delete(route('administration.owners.destroy', $owner->id, false))
            ->assertStatus(409);

        $this->assertNotNull($owner->fresh());
    }

    private function attachUser($owner)
    {
        $user = new User([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'is_active' => 1,
        ]);
        $user->email = $this->faker->email;
        $user->owner_id = $owner->id;
        $user->role_id = $this->role->id;
        $user->save();
    }

    private function postParams()
    {
        return [
            'name' => $this->faker->firstName,
            'description' => $this->faker->sentence,
            'is_active' => 1,
            '_method' => 'POST',
            'roleList' => []
        ];
    }
}
