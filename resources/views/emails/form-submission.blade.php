<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva respuesta — {{ $form->name ?? 'Formulario' }}</title>
    <style>
        body        { margin:0; padding:0; background:#f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color:#1e293b; }
        .wrap       { max-width:600px; margin:32px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.12); }
        .header     { background:#0f1e4a; padding:28px 32px; }
        .header h1  { margin:0; color:#ffffff; font-size:18px; font-weight:600; }
        .header p   { margin:4px 0 0; color:#93c5fd; font-size:13px; }
        .body       { padding:32px; }
        .badge      { display:inline-block; background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; padding:3px 10px; border-radius:100px; margin-bottom:24px; }
        .field      { margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:20px; }
        .field:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
        .field-label { font-size:11px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#64748b; margin:0 0 4px; }
        .field-value { font-size:15px; color:#0f172a; margin:0; white-space:pre-wrap; word-break:break-word; }
        .meta       { background:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 32px; font-size:12px; color:#94a3b8; }
        .meta a     { color:#3b82f6; text-decoration:none; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>📩 Nueva respuesta recibida</h1>
        <p>{{ $siteName }} · {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="body">
        <span class="badge">{{ $form->name ?? 'Formulario' }}</span>

        @php
            $fieldMap = $form?->fields->keyBy('field_key') ?? collect();
        @endphp

        @foreach($submission->data as $key => $value)
            @if($value !== '' && $value !== null)
                @php
                    $field = $fieldMap->get($key);
                    $label = $field?->label ?? $key;
                    $displayValue = is_bool($value) ? ($value ? 'Sí' : 'No') : $value;
                @endphp
                <div class="field">
                    <p class="field-label">{{ $label }}</p>
                    <p class="field-value">{{ $displayValue }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="meta">
        IP: {{ $submission->ip_address ?? '—' }} &nbsp;·&nbsp;
        Recibido: {{ $submission->created_at->format('d/m/Y H:i:s') }} &nbsp;·&nbsp;
        <a href="{{ config('app.url') }}/admin/form-submissions/{{ $submission->id }}">Ver en el panel</a>
    </div>
</div>
</body>
</html>
