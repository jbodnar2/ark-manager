# Caddy Reference

**Caddy** is an extensible web server platform written in Go. At its core, Caddy manages configuration. Functionality is provided by **modules** that are plugged in statically at compile time. The standard distribution includes common modules for serving HTTP, TLS, and PKI (including automatic HTTPS certificate management).

## Running Caddy

- **`caddy run`** → Starts Caddy in the **foreground** (recommended for most interactive use; blocks the terminal until stopped with Ctrl+C).
- **`caddy start`** → Starts Caddy in the **background** (terminal returns immediately; only use if you'll manually run `caddy stop` later before closing the terminal).
- **`caddy stop`** → Gracefully stops a background Caddy process (via the admin API).

> **Note:** `caddy start`/`stop` are convenient for quick testing but discouraged for production or system services (use `systemd`, Docker, etc., instead). Prefer `caddy run` + `caddy reload` for graceful config changes without downtime.

When Caddy starts, it opens a locally-bound **administrative socket** (default: `http://localhost:2019`) for RESTful configuration via the HTTP API. See: [https://caddyserver.com/docs/api](https://caddyserver.com/docs/api)

## Configuration Formats

- **Native format**: JSON (full control, used internally).
- **Config adapters** convert other formats to JSON on load.
    - Built-in: **Caddyfile** — popular for hand-written configs due to its simple, readable syntax.  
      See: [https://caddyserver.com/docs/caddyfile](https://caddyserver.com/docs/caddyfile)
    - Many third-party adapters available: [https://caddyserver.com/docs/config-adapters](https://caddyserver.com/docs/config-adapters)

Use **`caddy adapt`** to see how a config (e.g., Caddyfile) translates to JSON:

```bash
caddy adapt --config Caddyfile --pretty
```

## Loading Configuration via CLI

The CLI can act as an HTTP client to POST config to the admin API.

- If a file named **`Caddyfile`** exists in the current directory, `caddy run` loads it automatically.
- Otherwise, specify with `--config`:

```bash
caddy run --config /path/to/caddy.json
# or
caddy run --config Caddyfile
```

Special-purpose subcommands build/load config directly from CLI flags (admin API is disabled for safety):

```bash
caddy file-server          # static file server
caddy reverse-proxy        # quick reverse proxy
caddy respond              # hard-coded HTTP responses (great for testing)
```

## Most Common Ways to Run Caddy

```bash
# Simple (auto-loads Caddyfile if present)
caddy run

# With explicit config
caddy run --config caddy.json

# Interactive/background (terminal free for other commands)
caddy start
# ... do other work ...
caddy stop               # Don't forget this before closing terminal!
```

## Permissions (Low Ports on Linux)

Caddy needs permission to bind ports < 1024 (e.g., 80/443). One common solution:

```bash
sudo setcap 'cap_net_bind_service=+ep' $(which caddy)
```

Run this again after replacing the binary.

## Custom Builds & Installation

- **Custom builds** (add/remove modules): Use `xcaddy` → [https://github.com/caddyserver/xcaddy](https://github.com/caddyserver/xcaddy)
- **Download page** (pre-built binaries): [https://caddyserver.com/download](https://caddyserver.com/download)
- **Recommended**: Use official package installers → [https://caddyserver.com/docs/install](https://caddyserver.com/docs/install)
- **Production running guide**: [https://caddyserver.com/docs/running](https://caddyserver.com/docs/running)

## CLI Commands Overview

```bash
caddy [command]
```

**Examples**:

```bash
caddy run
caddy run --config caddy.json
caddy reload --config caddy.json
caddy stop
```

**Available Commands** (selected common ones):

- `adapt` — Convert config to JSON
- `file-server` — Production-ready static file server
- `fmt` — Format a Caddyfile
- `hash-password` — Hash passwords for config
- `help` — Show help
- `reload` — Gracefully reload config
- `respond` — Simple HTTP responses for testing
- `reverse-proxy` — Quick reverse proxy
- `run` — Start in foreground
- `start` — Start in background
- `stop` — Gracefully stop
- `validate` — Test config validity
- `version` — Show version

Use `caddy [command] --help` for details on any command.

**Full documentation**: [https://caddyserver.com/docs/command-line](https://caddyserver.com/docs/command-line)  
**Main site & tutorials**: [https://caddyserver.com/docs/](https://caddyserver.com/docs/)
