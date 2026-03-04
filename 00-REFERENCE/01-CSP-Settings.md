# Content Security Policy (CSP) Notes

## Overview: What CSP Does

- Browser-enforced policy restricting resource loading (scripts, styles, images, frames, fonts, etc.) to reduce XSS, clickjacking, and mixed-content risks.
- Declares allowed sources per resource type.
- Enforced by browser; errors break site (check console for violations).

## Policy Anatomy

- Consists of directives controlling resource types.
- Example: `Content-Security-Policy: default-src 'self'; img-src 'self' data:;`
    - `default-src 'self'`: Fallback for unset directives; same-origin only.
    - `img-src 'self' data:`: Images from same-origin or data URIs.
- Keywords:
    - `'self'`: Same origin.
    - `'unsafe-inline'`: Allow inline scripts/styles (risky).
    - `'unsafe-eval'`: Allow eval-like ops (risky).
    - `data:`: Allow data URIs.
    - `*`: Any origin (permissive).
    - `'none'`: Disallow all.

## Common Directives

- `default-src`: Fallback.
- `script-src`: JS sources (prefer nonces/hashes over `'unsafe-inline'`).
- `style-src`: CSS sources (prefer nonces/hashes).
- `img-src`: Images (includes CSS `mask-image`/`background-image` data URIs).
- `connect-src`: XHR/Fetch/WebSocket endpoints.
- `font-src`: Web fonts.
- `object-src`: `<object>`, `<embed>`, `<applet>` (often `'none'`).
- `media-src`: Audio/video.
- `frame-ancestors`: Sites allowed to frame this page (clickjacking prevention).
- `base-uri`: Allowed `<base>` tag origins.
- `form-action`: Form submission targets.
- `upgrade-insecure-requests`: Upgrade HTTP to HTTPS.
- `block-all-mixed-content`: Block HTTP subresources on HTTPS.
- Deprecated: `child-src`/`frame-src` (overlapped by others in CSP3).

## Practical Recommendations & Starter Policy

- Start conservative, iterate.
- Test in Report-Only mode first, collect violations, then enforce.
- Avoid `'unsafe-inline'`/`'unsafe-eval'`.
- Use nonces/hashes for inline scripts/styles.
- Limit `data:` to specific directives (e.g., `img-src` for icons only).
- Set `object-src 'none'`.
- Minimal policy for CSS data-URI SVG icons:
    ```
    Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self';
    ```

## Why CSS Data-URI Icons Block & Fix

- `mask-image`/`background-image` with `url("data:image/svg+xml,...")` treated as image resource.
- Blocked if `img-src` (or fallback `default-src`) excludes `data:` → Icons don't render.
- Fix: Add `data:` to `img-src` (narrow scope) or serve icons from same-origin files (safer).

## Setting CSP Examples

- **PHP** (in bootstrap):
    ```php
    <?php
    // Minimal example (allow inline data URI images for CSS icons)
    header(
        'Content-Security-Policy: default-src \'self\'; img-src \'self\' data:; object-src \'none\'; base-uri \'self\'; frame-ancestors \'none\'; form-action \'self\';',
    );
    ```
- **Nginx** (server/location block):
    ```
    add_header Content-Security-Policy "default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self';";
    ```
- **Apache** (.htaccess/config):
    ```
    Header set Content-Security-Policy "default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self';"
    ```
- **Meta Tag** (fallback if headers unavailable):
    ```
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self';">
    ```

## Allowing Inline Scripts/Styles: Nonces & Hashes

- **Nonce**: Random per-request; add to directive and tag.
    - Header: `script-src 'self' 'nonce-<RUNTIME_NONCE>';`
    - Tag: `<script nonce="<?= $nonce ?>">...</script>`
- **Hashes**: SHA256/384/512 of content.
    - Example: `script-src 'self' 'sha256-Base64Hash';`
- Prefer nonces for limited inline.
- PHP nonce example:

    ```php
    <?php
    // create and store a nonce for this request
    $nonce = base64_encode(random_bytes(16));
    // include it in your header
    header(
        "Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-$nonce'; style-src 'self' 'nonce-$nonce';",
    );

    // then use $nonce in your templates for inline tags:
    // <script nonce="<?= htmlspecialchars($nonce, ENT_QUOTES)
    ?>">console.log('allowed');</script>
    ```

- Notes: Nonces unique per response; provide to templates.

## Report-Only Mode for Testing

- Use `Content-Security-Policy-Report-Only` to log violations without blocking.
- Add `report-uri` (legacy) or `report-to` for endpoint.
- Example (report-uri):
    ```
    Content-Security-Policy-Report-Only: default-src 'self'; img-src 'self' data:; report-uri /csp-report-endpoint;
    ```
- Modern (report-to + Report-To):
    ```
    Report-To: {"group":"csp","max_age":10886400,"endpoints":[{"url":"https://your.example/csp-report"}],"include_subdomains":true}
    Content-Security-Policy-Report-Only: default-src 'self'; img-src 'self' data:; report-to csp;
    ```
- Where reports go:
    - `report-uri`: Browser POSTs JSON to path.
    - `report-to`: POSTs to endpoints in `Report-To` header.
    - DevTools: Console logs violations; Network tab shows POSTs.
- Payload (report-uri example):

    ```json
    {
        "csp-report": {
            "document-uri": "https://example.com/path",
            "referrer": "",
            "blocked-uri": "data",
            "violated-directive": "img-src",
            "original-policy": "default-src 'self'; img-src 'self' data:;"
        }
    }
    ```

    - Content-Type: `application/csp-report` (legacy) or `application/reports` (modern).

- PHP receiver example:
    ```php
    <?php
    // Very simple CSP report receiver for local testing.
    // Save as /csp-report-receiver.php and point report-uri to /csp-report-receiver.php
    // IMPORTANT: sanitize/log carefully before using in production.
    $raw = file_get_contents('php://input');
    if (!$raw) {
        http_response_code(400);
        echo "No data\n";
        exit();
    }
    // Write raw payload to a file (rotate in production) or to error log
    $logPath = __DIR__ . '/csp-reports.log';
    file_put_contents(
        $logPath,
        date('c') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . $raw . PHP_EOL,
        FILE_APPEND | LOCK_EX,
    );
    // Respond quickly
    http_response_code(204); // No Content
    exit();
    ```
- Curl test:
    ```sh
    #!/bin/sh
    # send a test CSP report to a local receiver
    curl -X POST -H "Content-Type: application/csp-report" \
      --data '{"csp-report":{"document-uri":"https://example.local/test","blocked-uri":"data","violated-directive":"img-src","original-policy":"default-src '\''self'\''"}}' \
      http://localhost/csp-report-receiver.php
    ```
- Notes: Use console for dev; server for monitoring. `report-uri` simpler; `report-to` modern. Sanitize payloads (privacy). Handle volume/abuse.

## CSP & SVG Specifics

- SVG as `data:image/svg+xml` or CSS background/mask: Governed by `img-src`.
- Inline SVG in HTML: Affected by `script-src`/`style-src`; avoid user-supplied.
- Blocking `data:` in `img-src` blocks `<img src="data:...">` or CSS usage.

## Troubleshooting Violations

- Console: Lists blocked resource & directive.
- Use Report-Only for pre-enforcement collection.
- Tools:
    - CSP Evaluator: https://csp-evaluator.withgoogle.com/
    - Report-URI: https://report-uri.com/
    - Security Headers: https://securityheaders.com/
- Common issues:
    - Missing `data:` in `img-src` → Data-URI images blocked.
    - Inline scripts → Need nonce/hash or externalize.
    - Fonts → Add `font-src`.
    - AJAX → Add `connect-src`.
- Checklist:
    1. Audit inline/external resources & CDNs.
    2. Use Report-Only, collect logs (days).
    3. Add sources (nonces/hashes for inline).
    4. Enforce once clean.
    5. Add HSTS/other headers.

## Hardened Header Baseline

- Adapt this; allows `data:` for images, tightens others:
    ```
    Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; script-src 'self' 'nonce-<RUNTIME_NONCE>'; style-src 'self' 'nonce-<RUNTIME_NONCE>';
    ```
- Generate/inject nonce per-request.

## Security Tradeoffs

- `img-src 'self' data:`: Acceptable for controlled static icons.
- Avoid `data:` in `default-src` (broadens unnecessarily).
- Skip `'unsafe-inline'`; use nonces/hashes.
- Use `object-src 'none'`, `frame-ancestors 'none'` to minimize surface.

## Further Reading

- MDN: https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
- CSP Evaluator: https://csp-evaluator.withgoogle.com/
- CSP3 Spec: https://www.w3.org/TR/CSP3/
- Reports: Search `report-uri`/`report-to` implementations.
