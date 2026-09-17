@extends('layouts.shipsync')

@section('title', 'Cotação de frete — '.config('app.name'))

@section('content')
    <div class="panel">
        <h2 style="margin-top:0;font-size:1.15rem;">Nova cotação (API v1)</h2>
        <p class="status">Envia <code>POST /api/v1/shipping/quotes</code> e exibe o resultado aqui embaixo assim que ficar pronta.</p>

        <form id="quote-form">
            <div class="grid" style="margin-top:1rem;">
                <div>
                    <label for="origin">CEP origem</label>
                    <input id="origin" name="origin" required value="01310-100" autocomplete="postal-code">
                </div>
                <div>
                    <label for="destination">CEP destino</label>
                    <input id="destination" name="destination" required value="20040-020" autocomplete="postal-code">
                </div>
            </div>
            <div class="grid" style="margin-top:1rem;">
                <div>
                    <label for="weight_grams">Peso (g)</label>
                    <input id="weight_grams" name="weight_grams" type="number" min="1" required value="1000">
                </div>
                <div>
                    <label for="length_cm">Comprimento (cm)</label>
                    <input id="length_cm" name="length_cm" type="number" min="1" required value="20">
                </div>
                <div>
                    <label for="width_cm">Largura (cm)</label>
                    <input id="width_cm" name="width_cm" type="number" min="1" required value="15">
                </div>
                <div>
                    <label for="height_cm">Altura (cm)</label>
                    <input id="height_cm" name="height_cm" type="number" min="1" required value="10">
                </div>
            </div>
            <button type="submit" id="submit-btn">Solicitar cotação</button>
        </form>

        <p id="create-status-line" class="status" style="margin-top:1rem;" aria-live="polite"></p>
        <pre id="create-result-json" hidden></pre>
    </div>

    <div class="panel" style="margin-top:1.25rem;">
        <h2 style="margin-top:0;font-size:1.15rem;">Consultar cotação existente</h2>
        <p class="status">Informe um UUID e use <code>GET /api/v1/shipping/quotes/{id}</code>. Também funciona em <code>/quotes/{id}</code>.</p>

        <form id="lookup-form">
            <div style="margin-top:1rem;">
                <label for="quote_id">ID da cotação</label>
                <input
                    id="quote_id"
                    name="quote_id"
                    required
                    placeholder="ex.: b6f033cd-fe8e-486e-ba84-29c989b5183e"
                    value="{{ $prefillQuoteId ?? '' }}"
                    autocomplete="off"
                    spellcheck="false"
                >
            </div>
            <button type="submit" id="lookup-btn">Consultar</button>
            <label style="display:flex;align-items:center;gap:0.5rem;margin-top:0.75rem;font-size:0.85rem;color:var(--muted);">
                <input type="checkbox" id="lookup_poll" checked>
                Aguardar processamento (polling) se ainda não estiver pronta
            </label>
        </form>

        <p id="lookup-status-line" class="status" style="margin-top:1rem;" aria-live="polite"></p>
        <pre id="lookup-result-json" hidden></pre>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('quote-form');
    const lookupForm = document.getElementById('lookup-form');
    const createStatus = document.getElementById('create-status-line');
    const createResult = document.getElementById('create-result-json');
    const lookupStatus = document.getElementById('lookup-status-line');
    const lookupResult = document.getElementById('lookup-result-json');
    const submitBtn = document.getElementById('submit-btn');
    const lookupBtn = document.getElementById('lookup-btn');
    const lookupPoll = document.getElementById('lookup_poll');
    const apiBase = @json(url('/api/v1/shipping/quotes'));
    const showBase = @json(url('/quotes'));
    const autoFetchQuote = @json($autoFetchQuote ?? false);

    function showInBlock(statusEl, resultEl, body, labelReady) {
        statusEl.textContent = body.status === 'ready'
            ? `${labelReady} (${body.id}).`
            : `Resposta recebida (${body.id ?? '—'}).`;
        statusEl.className = 'status ok';
        resultEl.hidden = false;
        resultEl.textContent = JSON.stringify(body, null, 2);
        resultEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function setCreateBusy(busy) {
        submitBtn.disabled = busy;
    }

    function setLookupBusy(busy) {
        lookupBtn.disabled = busy;
    }

    async function fetchQuoteOnce(id) {
        const response = await fetch(`${apiBase}/${encodeURIComponent(id)}`, {
            headers: { 'Accept': 'application/json' },
        });
        if (response.status === 404) {
            return { kind: 'missing' };
        }
        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            throw new Error(err.message || `GET retornou ${response.status}`);
        }
        const body = await response.json();
        if (body.status === 'ready') {
            return { kind: 'ready', body };
        }
        return { kind: 'processing', body };
    }

    async function pollQuote(id, statusEl) {
        const maxAttempts = 30;
        for (let attempt = 0; attempt < maxAttempts; attempt++) {
            const result = await fetchQuoteOnce(id);
            if (result.kind === 'missing') {
                await new Promise(r => setTimeout(r, 400));
                continue;
            }
            if (result.kind === 'ready') {
                return result.body;
            }
            statusEl.textContent = `Processando… (tentativa ${attempt + 1}/${maxAttempts})`;
            statusEl.className = 'status warn';
            await new Promise(r => setTimeout(r, 500));
        }
        throw new Error('Tempo esgotado aguardando cotação.');
    }

    async function consultQuote(id, { pollIfProcessing, statusEl, resultEl }) {
        const trimmed = id.trim();
        if (!trimmed) {
            throw new Error('Informe o ID da cotação.');
        }

        history.replaceState(null, '', `${showBase}/${trimmed}`);

        const first = await fetchQuoteOnce(trimmed);
        if (first.kind === 'missing') {
            throw new Error('Cotação não encontrada. Verifique o ID ou aguarde o processamento.');
        }
        if (first.kind === 'ready') {
            return first.body;
        }
        if (!pollIfProcessing) {
            statusEl.textContent = 'Cotação ainda em processamento.';
            statusEl.className = 'status warn';
            resultEl.hidden = false;
            resultEl.textContent = JSON.stringify(first.body, null, 2);
            return null;
        }
        return pollQuote(trimmed, statusEl);
    }

    lookupForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setLookupBusy(true);
        lookupResult.hidden = true;
        lookupStatus.textContent = 'Consultando…';
        lookupStatus.className = 'status';

        try {
            const body = await consultQuote(lookupForm.quote_id.value, {
                pollIfProcessing: lookupPoll.checked,
                statusEl: lookupStatus,
                resultEl: lookupResult,
            });
            if (body) {
                showInBlock(lookupStatus, lookupResult, body, 'Cotação encontrada');
            }
        } catch (error) {
            lookupStatus.textContent = error.message || 'Erro inesperado.';
            lookupStatus.className = 'status warn';
        } finally {
            setLookupBusy(false);
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setCreateBusy(true);
        createResult.hidden = true;
        createStatus.textContent = 'Enviando pedido…';
        createStatus.className = 'status';

        const payload = {
            origin: form.origin.value.trim(),
            destination: form.destination.value.trim(),
            packages: [{
                weight_grams: Number(form.weight_grams.value),
                length_cm: Number(form.length_cm.value),
                width_cm: Number(form.width_cm.value),
                height_cm: Number(form.height_cm.value),
            }],
        };

        try {
            const create = await fetch(apiBase, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            if (!create.ok) {
                const err = await create.json().catch(() => ({}));
                throw new Error(err.message || `POST retornou ${create.status}`);
            }
            const created = await create.json();
            createStatus.textContent = `Pedido ${created.id} — aguardando resultado…`;
            createStatus.className = 'status';
            const result = await pollQuote(created.id, createStatus);
            showInBlock(createStatus, createResult, result, 'Cotação pronta');
        } catch (error) {
            createStatus.textContent = error.message || 'Erro inesperado.';
            createStatus.className = 'status warn';
        } finally {
            setCreateBusy(false);
        }
    });

    if (autoFetchQuote && lookupForm.quote_id.value.trim() !== '') {
        lookupForm.requestSubmit();
    }
})();
</script>
@endpush
