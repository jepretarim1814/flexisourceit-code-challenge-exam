<?php

namespace Stubs;

use Faker\Factory;
use Faker\Generator;

class JsonGeneratorDataClassStub
{
    /**
     * @param Generator|null $faker
     * @param int $count
     * @return array
     */
    public function generateJsonResults(Generator $faker = null, int $count = 1) : array
    {
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = $this->generateJsonResult($faker ?? Factory::create());
        }

        return $results;
    }

    /**
     * @param Generator $faker
     * @return array
     */
    protected function generateJsonResult(Generator $faker) : array
    {
        return array(
            'gender' => $faker->randomElement(['male', 'female']),
            'name' => [
                'title' => $faker->title,
                'first' => $faker->firstName,
                'last' => $faker->lastName,
            ],
            'location' => [
                'street' => [
                    'number' => $faker->randomNumber(4),
                    'name' => $faker->streetName,
                ],
                'city' => $faker->city,
                'state' => $faker->state,
                'country' => $faker->country,
                'postcode' => $faker->postcode,
                'coordinates' => [
                    'latitude' => $faker->latitude,
                    'longitude' => $faker->longitude,
                ],
                'timezone' => [
                    'offset' => $faker->dateTime->format('P'),
                    'description' => $faker->timezone,
                ],
            ],
            'email' => $faker->unique()->email,
            'login' => [
                'uuid' => $faker->uuid,
                'username' => $faker->userName,
                'password' => $faker->password,
                'salt' => $faker->word,
                'md5' => $faker->md5,
                'sha1' => $faker->sha1,
                'sha256' => $faker->sha256,
            ],
            'dob' => [
                'date' => $faker->iso8601,
                'age' => $faker->randomNumber(2),
            ],
            'registered' => [
                'date' => $faker->iso8601,
                'age' => $faker->randomNumber(2),
            ],
            'phone' => $faker->phoneNumber,
            'cell' => $faker->phoneNumber,
            'id' => [
                'name' => $faker->word,
                'value' => $faker->word,
            ],
            'picture' => [
                'large' => $faker->imageUrl(),
                'medium' => $faker->imageUrl(),
                'thumbnail' => $faker->imageUrl(),
            ],
            'nat' => $faker->randomElement([
                'AU',
                'BR',
                'CA',
                'CH',
                'DE',
                'DK',
                'ES',
                'FI',
                'FR',
                'GB',
                'IE',
                'IR',
                'NO',
                'NL',
                'NZ',
                'TR',
                'US',
            ]),
        );
    }
}
