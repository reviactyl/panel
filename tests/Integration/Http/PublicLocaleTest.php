<?php

namespace Tests\Integration\Http;

class PublicLocaleTest extends HttpTestCase
{
    public function test_public_translations_do_not_start_or_reissue_a_session(): void
    {
        foreach (['/locales/list.json', '/locales/locale.json?locale=en&namespace=auth'] as $path) {
            $this->getJson($path)->assertOk()->assertCookieMissing(config('session.cookie'))->assertCookieMissing('XSRF-TOKEN');
            $this->assertFalse($this->app['request']->hasSession());
        }
    }

    public function test_stateless_translation_validation_is_preserved(): void
    {
        $this->getJson('/locales/locale.json?locale=invalid&namespace=auth')->assertUnprocessable()->assertJsonPath('errors.0.meta.source_field', 'locale');
    }
}
