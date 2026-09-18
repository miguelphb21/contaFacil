<?php

it('redirects the root to the dashboard', function () {
    $this->get('/')->assertRedirect('/dashboard');
});
