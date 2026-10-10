<?php

test('root menampilkan landing page publik reservasi fasilitas kampus', function () {
    $this->get('/')
        ->assertOk()
        ->assertViewIs('welcome')
        ->assertSee('Universitas Diponegoro');
});
