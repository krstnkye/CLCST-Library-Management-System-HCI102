<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patron_id' => fake()->unique()->bothify('ID-####-??'),
            'name' => fake()->name(),
            'email' => fake()->unique()->userName().'@clcst.edu.ph',
            'email_verified_at' => now(),
            'role' => 'student',
            'status' => 'active',
            'program_or_department' => 'General Studies',
            'mobile_number' => '09171234567',
            'approved_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function role(string $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'approved_at' => null]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
