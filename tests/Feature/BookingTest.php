<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateBooking()
    {
        // Simulate authentication
        $user = User::factory()->create();
        // Generate a Passport access token for the user
        Passport::actingAs($user);

        $bookingData = [
            'date' => '2023-06-01',
            'reason' => 'Test Booking',
        ];
        $response = $this->post('/api/bookings', $bookingData);
        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', $bookingData);
    }

    public function testGetBooking()
    {
        // Simulate authentication
        $user = User::factory()->create();
        Passport::actingAs($user);

        // Create bookings for today
        Booking::factory()->create(['date' => Carbon::today()->format('Y-m-d')]);
        Booking::factory()->create(['date' => Carbon::today()->format('Y-m-d')]);

        // Create past bookings
        Booking::factory()->create(['date' => Carbon::yesterday()->format('Y-m-d')]);
        Booking::factory()->create(['date' => Carbon::yesterday()->format('Y-m-d')]);

        // Create future bookings
        Booking::factory()->create(['date' => Carbon::tomorrow()->format('Y-m-d')]);
        Booking::factory()->create(['date' => Carbon::tomorrow()->format('Y-m-d')]);

        $response = $this->get('/api/bookings');

        $response->assertStatus(200)
            ->assertJsonCount(6) // Expecting a total of 6 bookings
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'date',
                    'reason',
                ],
            ]);

        // Test filtering for today
        $response = $this->get('/api/bookings?filter=today');

        $response->assertStatus(200)
            ->assertJsonCount(2) // Expecting 2 bookings for today
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'date',
                    'reason',
                ],
            ]);

        // Test filtering for past
        $response = $this->get('/api/bookings?filter=past');

        $response->assertStatus(200)
            ->assertJsonCount(2) // Expecting 2 past bookings
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'date',
                    'reason',
                ],
            ]);

        // Test filtering for future
        $response = $this->get('/api/bookings?filter=future');

        $response->assertStatus(200)
            ->assertJsonCount(2) // Expecting 2 future bookings
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'date',
                    'reason',
                ],
            ]);
    }
}
