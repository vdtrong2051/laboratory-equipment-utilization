<?php

test('the application redirects from home into the role flow', function () {
    $response = $this->get('/');

    $response->assertRedirect();
});
