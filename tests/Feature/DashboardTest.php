<?php

beforeEach(function () {
    $this->user = createUser();
});

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($this->user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
