<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();
        $username = $this->createUsernameFromEmail($email);

        return [
            'username' => $username,
            'avatar' => 'avatars/default.png',
            'name' => fake()->name(),
            'email' => $email,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
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

    /**
     * Create username from email
     *
     * @param string $email
     * @return string
     */
    private function createUsernameFromEmail(string $email): string
    {
        // Get the username part of the email
        $username = explode('@', $email)[0];

        // Remove all special characters from the username
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);

        return $username;
    }
}
