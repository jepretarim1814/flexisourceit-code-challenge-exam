<?php


namespace Customer;


use Illuminate\Support\Collection;
use Illuminate\Http\Client\Factory;
use App\Services\Customer\RandomUserClient;

class RandomUserClientTest extends \TestCase
{

    protected Factory $factory;

    protected function setUp() : void
    {
        parent::setUp();
        $this->factory = new Factory();
    }

    /** @test */
    public function get_results()
    {
        $count = 100;
        $http = $this->factory->fake([
            '*' => [
                'results' => $this->generateResults(\Faker\Factory::create(), $count)
            ]
        ]);
        $client = new RandomUserClient(
            $http->baseUrl('/'),
            [
                'url' => '/',
                'version' => '1.3',
                'nationalities' => ['au'],
                'fields' => [
                    'name'
                ],
                'count' => $count
            ]
        );
        $results = $client->results();
        $this->assertCount($count, $results);
        $this->assertInstanceOf(Collection::class, $results);
    }

    protected function generateResults(\Faker\Generator $faker = null, int $count = 1) : array
    {
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = $this->generateSampleResult($faker ?? \Faker\Factory::create());
        }

        return $results;
    }

    protected function generateSampleResult(\Faker\Generator $faker) : array
    {
        return [
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
        ];
    }
}
