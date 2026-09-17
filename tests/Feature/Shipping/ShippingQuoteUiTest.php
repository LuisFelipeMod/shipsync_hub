<?php

describe('UI web de cotações', function () {
    it('redireciona a home para /quotes', function () {
        $this->get('/')->assertRedirect('/quotes');
    });

    it('exibe formulário de cotação consumindo a API v1', function () {
        $response = $this->get('/quotes');

        $response->assertOk()
            ->assertSee('Nova cotação (API v1)', false)
            ->assertSee('/api/v1/shipping/quotes', false)
            ->assertSee('id="quote-form"', false)
            ->assertSee('Consultar cotação existente', false)
            ->assertSee('id="lookup-form"', false);
    });

    it('exibe consulta com UUID na rota /quotes/{id}', function () {
        $id = 'b6f033cd-fe8e-486e-ba84-29c989b5183e';

        $this->get("/quotes/{$id}")
            ->assertOk()
            ->assertSee($id, false)
            ->assertSee('const autoFetchQuote = true', false);
    });
});
