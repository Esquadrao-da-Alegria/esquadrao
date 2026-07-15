<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyStaticRoutesTest extends TestCase
{
    public function test_index_html_redirects_to_home(): void
    {
        $this->get('/index.html')
            ->assertRedirect('/');
    }
}
