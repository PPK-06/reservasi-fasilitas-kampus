<?php

test('root mengarah ke daftar fasilitas', function () {
    $this->get('/')->assertRedirect(route('facilities.index'));
});
