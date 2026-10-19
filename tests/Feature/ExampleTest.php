<?php

it('returns a successful response for the home page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns a 404 response for unknown routes', function () {
    $response = $this->get('/non-existent-route');

    $response->assertStatus(404);
});
