<?php

test('returns a successful response', function () {
    // Akses halaman login atau halaman depan '/'
    $response = $this->get('/blog');
    // atau: $this->get(route('login'));

    $response->assertOk();
});
