<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KK Wholesalers ERP — Swagger Route & API Documentation</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Swagger UI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui.css">

    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --navy-dark: #0f172a;
            --navy-light: #1e293b;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }

        /* Top Brand Header */
        .docs-header {
            background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy-light) 100%);
            color: #ffffff;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .docs-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
        }

        .brand-text h1 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .brand-text p {
            margin: 0;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.825rem;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-yaml {
            background-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-yaml:hover {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .btn-dashboard {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: 1px solid rgba(2, 132, 199, 0.3);
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
        }

        .btn-dashboard:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        }

        /* Swagger Container Customization */
        #swagger-ui {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem 2rem 4rem 2rem;
        }

        .swagger-ui .topbar {
            display: none !important;
        }

        .swagger-ui .info {
            margin: 1.5rem 0 2rem 0 !important;
            padding: 1.75rem !important;
            background: #ffffff !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03) !important;
            border: 1px solid #e2e8f0 !important;
        }

        .swagger-ui .info .title {
            font-family: 'Inter', sans-serif !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            font-size: 1.85rem !important;
        }

        .swagger-ui .opblock {
            border-radius: 8px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            margin-bottom: 0.75rem !important;
            border-width: 1px !important;
        }

        .swagger-ui .opblock-tag {
            font-family: 'Inter', sans-serif !important;
            font-size: 1.2rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 0.75rem 0 !important;
            margin: 2rem 0 1rem 0 !important;
        }

        .swagger-ui code, .swagger-ui pre {
            font-family: 'Fira Code', monospace !important;
        }

        .swagger-ui .btn.execute {
            background-color: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
        }

        .swagger-ui .btn.execute:hover {
            background-color: #0369a1 !important;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="docs-header">
        <a href="{{ route('dashboard') }}" class="docs-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="brand-text">
                <h1>KK Wholesalers ERP</h1>
                <p>Route & API Specification (OpenAPI 3.0 / Swagger UI)</p>
            </div>
        </a>

        <div class="header-actions">
            <a href="{{ url('/docs/openapi.yaml') }}" target="_blank" download="kk-erp-openapi.yaml" class="btn-action btn-yaml">
                <i class="fa-solid fa-file-code"></i>
                <span>Download OpenAPI Spec</span>
            </a>
            <a href="{{ route('dashboard') }}" class="btn-action btn-dashboard">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to ERP</span>
            </a>
        </div>
    </header>

    <!-- Main Swagger UI Container -->
    <div id="swagger-ui"></div>

    <!-- Swagger UI JS Bundles -->
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.18.2/swagger-ui-standalone-preset.js"></script>

    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "{{ url('/docs/openapi.yaml') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                filter: true,
                tryItOutEnabled: true,
                persistAuthorization: true,
                displayRequestDuration: true,
                docExpansion: "list",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1
            });
        };
    </script>
</body>
</html>
