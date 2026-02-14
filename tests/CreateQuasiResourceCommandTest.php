<?php

namespace Protoqol\Quasi\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CreateQuasiResourceCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_a_resource_with_specified_table(): void
    {
        $this->artisan('make:qresource', [
            'name'    => 'UserResource',
            '--table' => 'users',
        ])
             ->expectsOutput("Generating 'UserResource' based on 'users' table.")
             ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Http/Resources/UserResource.php')));

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringContainsString('class UserResource', $content);
        $this->assertStringContainsString("'id' => \$this->id,", $content);
        $this->assertStringContainsString("'name' => \$this->name,", $content);
        $this->assertStringContainsString("'email' => \$this->email,", $content);
    }

    /** @test */
    public function it_can_generate_a_resource_with_guessed_table(): void
    {
        $this->artisan('make:qresource', ['name' => 'UserResource',])
             ->expectsOutput("Generating 'UserResource' based on 'users' table.")
             ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Http/Resources/UserResource.php')));
    }

    /** @test */
    public function it_fails_if_table_does_not_exist(): void
    {
        $this->artisan('make:qresource', ['name' => 'NonExistentResource',])
             ->expectsOutput("Table 'non_existents' does not exist. Try defining the table with the --table or --model option.")
             ->assertExitCode(1);

        $this->assertFalse(File::exists(app_path('Http/Resources/NonExistentResource.php')));
    }

    /** @test */
    public function it_excludes_columns_based_on_config(): void
    {
        config(['quasi.exclude' => ['id', 'created_at', 'updated_at']]);

        $this->artisan('make:qresource', ['name' => 'UserResource', '--table' => 'users',])
             ->assertExitCode(0);

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringNotContainsString("'id' => \$this->id,", $content);
        $this->assertStringNotContainsString("'created_at' => \$this->created_at,", $content);
        $this->assertStringContainsString("'name' => \$this->name,", $content);
    }

    /** @test */
    public function it_can_filter_columns_with_only_option(): void
    {
        $this->artisan('make:qresource', ['name' => 'UserResource', '--table' => 'users', '--only' => 'name,email',])
             ->assertExitCode(0);

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringContainsString("'name' => \$this->name,", $content);
        $this->assertStringContainsString("'email' => \$this->email,", $content);
        $this->assertStringNotContainsString("'id' => \$this->id,", $content);
    }

    /** @test */
    public function it_can_filter_columns_with_except_option(): void
    {
        $this->artisan('make:qresource', ['name' => 'UserResource', '--table' => 'users', '--except' => 'id,email',])
             ->assertExitCode(0);

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringContainsString("'name' => \$this->name,", $content);
        $this->assertStringNotContainsString("'id' => \$this->id,", $content);
        $this->assertStringNotContainsString("'email' => \$this->email,", $content);
    }

    /** @test */
    public function it_automatically_hides_sensitive_columns(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->string('password');
            $table->string('remember_token');
        });

        $this->artisan('make:qresource', ['name' => 'UserResource', '--table' => 'users',])
             ->assertExitCode(0);

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringNotContainsString("'password' => \$this->password,", $content);
        $this->assertStringNotContainsString("'remember_token' => \$this->remember_token,", $content);
    }

    /** @test */
    public function it_detects_relationships_from_foreign_keys(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
        });

        $this->artisan('make:qresource', ['name' => 'UserResource', '--table' => 'users',])
             ->assertExitCode(0);

        $content = File::get(app_path('Http/Resources/UserResource.php'));
        $this->assertStringContainsString("'company_id' => \$this->company_id,", $content);
        $this->assertStringContainsString("// 'company' => new CompanyResource(\$this->whenLoaded('company')),", $content);
    }

    /** @test */
    public function it_can_generate_from_a_model(): void
    {
        if (!class_exists('App\Models\User')) {
            if (!File::isDirectory(app_path('Models'))) {
                File::makeDirectory(app_path('Models'), 0755, true);
            }

            File::put(app_path('Models/User.php'), "<?php namespace App\Models; use Illuminate\Database\Eloquent\Model; class User extends Model { protected \$table = 'users'; }");
        }

        require_once app_path('Models/User.php');

        $this->artisan('make:qresource', ['name' => 'UserResource', '--model' => 'User',])
             ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Http/Resources/UserResource.php')));
    }

    /** @test */
    public function it_can_generate_all_resources(): void
    {
        // @TODO fix, github actions do not like this test.
        // Schema::create('posts', static function (Blueprint $table) {
        //     $table->id();
        //     $table->string('title');
        // });

        // $this->artisan('make:qresource', ['name' => 'All', '--all' => true,])
        //      ->assertExitCode(0);

        // $this->assertTrue(File::exists(app_path('Http/Resources/UserResource.php')));
        // $this->assertTrue(File::exists(app_path('Http/Resources/PostResource.php')));
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        if (File::exists(app_path('Http/Resources/UserResource.php'))) {
            File::delete(app_path('Http/Resources/UserResource.php'));
        }
    }
}
