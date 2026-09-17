<!DOCTYPE html>
<html lang="pt-BR" class="shipsync-swagger-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>ShipSync Hub — API v1</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        :root {
            color-scheme: dark;
            --ss-bg: #0d1117;
            --ss-surface: #161b22;
            --ss-surface-2: #21262d;
            --ss-border: #30363d;
            --ss-text: #e6edf3;
            --ss-muted: #8b949e;
            --ss-accent: #58a6ff;
            --ss-get: #3fb950;
            --ss-post: #58a6ff;
            --ss-code-bg: #0d1117;
        }

        html, body {
            margin: 0;
            background: var(--ss-bg);
        }

        .swagger-ui {
            color: var(--ss-text);
        }

        .swagger-ui .wrapper,
        .swagger-ui .information-container,
        .swagger-ui .scheme-container,
        .swagger-ui .servers > label,
        .swagger-ui .servers-title {
            background: var(--ss-bg);
            color: var(--ss-text);
            box-shadow: none;
        }

        .swagger-ui .scheme-container {
            background: var(--ss-surface) !important;
            border: 1px solid var(--ss-border);
            border-radius: 6px;
            box-shadow: none !important;
            margin: 0 0 16px;
            padding: 12px 16px;
        }

        .swagger-ui .servers {
            background: transparent;
        }

        .swagger-ui .servers select {
            background: var(--ss-surface-2);
            color: var(--ss-text);
            border-color: var(--ss-border);
        }

        .swagger-ui .topbar {
            background: var(--ss-surface);
            border-bottom: 1px solid var(--ss-border);
        }

        .swagger-ui .topbar .download-url-wrapper input[type=text] {
            background: var(--ss-surface-2);
            border-color: var(--ss-border);
            color: var(--ss-text);
        }

        .swagger-ui .info .title,
        .swagger-ui .info p,
        .swagger-ui .info li,
        .swagger-ui .info table,
        .swagger-ui .info a,
        .swagger-ui .opblock-tag,
        .swagger-ui .opblock .opblock-summary-path,
        .swagger-ui .opblock .opblock-summary-description,
        .swagger-ui .parameter__name,
        .swagger-ui .parameter__type,
        .swagger-ui .response-col_status,
        .swagger-ui table thead tr td,
        .swagger-ui table thead tr th,
        .swagger-ui .model-title,
        .swagger-ui section.models h4,
        .swagger-ui .btn {
            color: var(--ss-text);
        }

        .swagger-ui .info .title small.version-stamp {
            background: var(--ss-surface-2);
        }

        .swagger-ui .info .title small {
            color: var(--ss-muted);
        }

        .swagger-ui .opblock-tag {
            border-bottom-color: var(--ss-border);
        }

        .swagger-ui .opblock {
            background: var(--ss-surface);
            border-color: var(--ss-border);
            box-shadow: none;
        }

        .swagger-ui .opblock .opblock-section-header {
            background: var(--ss-surface-2);
            border-color: var(--ss-border);
        }

        .swagger-ui .opblock .opblock-section-header h4 {
            color: var(--ss-text);
        }

        .swagger-ui .opblock.opblock-get {
            background: rgba(63, 185, 80, 0.08);
            border-color: var(--ss-get);
        }

        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: var(--ss-get);
        }

        .swagger-ui .opblock.opblock-post {
            background: rgba(88, 166, 255, 0.08);
            border-color: var(--ss-post);
        }

        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: var(--ss-post);
        }

        .swagger-ui .opblock-body pre.microlight,
        .swagger-ui .model-box,
        .swagger-ui .model,
        .swagger-ui .responses-inner,
        .swagger-ui .table-container,
        .swagger-ui textarea,
        .swagger-ui input[type=text],
        .swagger-ui input[type=password],
        .swagger-ui input[type=search],
        .swagger-ui input[type=email] {
            background: var(--ss-code-bg);
            color: var(--ss-text);
            border-color: var(--ss-border);
        }

        .swagger-ui .response-col_links,
        .swagger-ui .prop-format,
        .swagger-ui .markdown p,
        .swagger-ui .markdown code {
            color: var(--ss-muted);
        }

        .swagger-ui .markdown code {
            background: var(--ss-surface-2);
            color: var(--ss-accent);
        }

        .swagger-ui section.models {
            border-color: var(--ss-border);
        }

        .swagger-ui section.models .model-container {
            background: var(--ss-surface);
        }

        .swagger-ui .btn.execute {
            background: var(--ss-accent);
            border-color: var(--ss-accent);
            color: #0d1117;
        }

        .swagger-ui .btn.cancel {
            background: var(--ss-surface-2);
            border-color: var(--ss-border);
            color: var(--ss-text);
        }

        .swagger-ui select {
            background: var(--ss-surface-2);
            color: var(--ss-text);
            border-color: var(--ss-border);
        }

        .swagger-ui .loading-container .loading::after {
            color: var(--ss-accent);
        }

        /* Textos que o swagger-ui.css deixa escuros (#3b4151, #606060, …) */
        html.shipsync-swagger-dark .swagger-ui :is(
            p, label, li, td, th,
            .renderedMarkdown,
            .renderedMarkdown p,
            .markdown,
            .markdown p,
            .info .title,
            .info .base-url,
            .info .description,
            .info li,
            .info p,
            .info table,
            .servers-title,
            .servers > label,
            .schemes-title,
            .opblock-tag,
            .opblock-tag small,
            .opblock .opblock-summary-path,
            .opblock .opblock-summary-description,
            .opblock-description-wrapper,
            .opblock-description-wrapper p,
            .opblock-external-docs-wrapper,
            .opblock-external-docs-wrapper p,
            .opblock-section-header h4,
            .parameters-col_description,
            .parameter__name,
            .parameter__type,
            .parameter__in,
            .parameter__deprecated,
            .response-col_status,
            .response-col_description,
            .response-col_links,
            .prop-name,
            .prop-type,
            .model-title,
            .model-title__text,
            .model-box,
            .model,
            section.models h4,
            .tab li,
            .tab li button,
            .curl-command,
            .copy-to-clipboard,
            .header-col
        ) {
            color: var(--ss-text) !important;
        }

        html.shipsync-swagger-dark .swagger-ui :is(
            .prop-format,
            .parameter__extension,
            .info .title small,
            .response-col_links,
            .markdown .light,
            .renderedMarkdown .light
        ) {
            color: var(--ss-muted) !important;
        }

        html.shipsync-swagger-dark .swagger-ui a {
            color: var(--ss-accent) !important;
        }

        html.shipsync-swagger-dark .swagger-ui a:hover {
            color: #79c0ff !important;
        }

        html.shipsync-swagger-dark .swagger-ui .opblock-summary-method {
            color: #fff !important;
        }

        html.shipsync-swagger-dark .swagger-ui .btn.execute {
            color: #0d1117 !important;
        }

        html.shipsync-swagger-dark .swagger-ui .info .title small.version-stamp {
            color: var(--ss-accent) !important;
        }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" charset="UTF-8" crossorigin></script>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js" charset="UTF-8" crossorigin></script>
<script>
    window.onload = () => {
        SwaggerUIBundle({
            url: @json($specUrl),
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset,
            ],
            layout: 'StandaloneLayout',
            syntaxHighlight: {
                activate: true,
                theme: 'monokai',
            },
        });
    };
</script>
</body>
</html>
