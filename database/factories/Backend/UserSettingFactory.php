<?php

namespace Database\Factories\backend;

use App\Models\backend\UserSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserSettingFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = UserSetting::class;
  // Define your theme and language options
  public  $themes = ['light', 'dark'];
  public  $languages = ['es-ES', 'fr-FR', 'en-EN'];
  // Define your device options
  public $devices = [
    'Desktop',
    'Mobile',
    'Tablet',
    'Laptop',
  ];
  /**
   * Define the model's default state.
   */
  public function definition(): array
  {

    return [
      // 'user_id' => User::factory(),
      'theme' =>  $this->themes[array_rand($this->themes)],
      'language' => $this->languages[array_rand($this->languages)],
      'autologin' => $this->faker->numberBetween(0, 1),
      'ipVisitor' => $this->faker->ipv4, // Use faker's ipv4 method
      'options' => null,
      'device' => $this->devices[array_rand($this->devices)], // Randomly choose a device
    ];
  }
}
