<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders documentation page', function () {
    $this->get('/documentation')
        ->assertSuccessful()
        ->assertSee('Documentation')
        ->assertSee('Project overview')
        ->assertSee('On this page');
});

it('renders changelog page', function () {
    $this->get('/changelog')
        ->assertSuccessful()
        ->assertSee('Changelog')
        ->assertSee('[4.2.1]')
        ->assertSee('[1.2.1]');
});

it('renders lessons learned page', function () {
    $this->get('/what-i-learned')
        ->assertSuccessful()
        ->assertSee('What I Learned')
        ->assertSee('Lessons Learned');
});

it('shows docs links on home page', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('/documentation')
        ->assertSee('/changelog')
        ->assertSee('/what-i-learned');
});
