# lnav Reference

**Official docs:** https://docs.lnav.org  
**In-app help:** Press `?` or `F1` (best reference!)

### Quick Start

- Open: `lnav app.log` or `lnav *.log`
- Watch a folder live (new files added automatically): `lnav /path/to/logs/`  
  (or `lnav -R /path/to/logs/` for recursive)
- Pipe output with timestamps: `command | lnav -t`
- Quit: `q`

### Essential Navigation

| Keys          | Action                        |
| ------------- | ----------------------------- |
| `j` / `k`     | Down / Up one line            |
| `Space` / `b` | Page Down / Page Up           |
| `g` / `G`     | Jump to top / bottom          |
| `e` / `E`     | Next / previous **error**     |
| `n` / `N`     | Next / previous search result |

### Search

- `/regex` → Search forward
- `n` / `N` → Jump to next/prev match
- `;` → Open SQL query mode

### Most Useful SQL Queries (press `;`)

- ` SELECT log_level, COUNT(\*) FROM all_logs GROUP BY log_level;`
- `SELECT \* FROM all_logs WHERE log_level = 'ERROR' LIMIT 20;`
- `SELECT MIN(log_time), MAX(log_time) FROM all_logs;`

### Common Filters & Commands (press `:` first)

- `:filter-in regex` → Show only matching lines
- `:filter-out regex` → Hide matching lines
- `:set-min-log-level error` → Show ERROR+ only
- `:rebuild` → Refresh/rebuild all files
- `Ctrl + f` → Toggle all filters on/off
- `Ctrl + R` → Reset everything (filters, marks, etc.)

### Views & Display

| Key         | Action                                     |
| ----------- | ------------------------------------------ |
| `p`         | Toggle parser fields view                  |
| `Shift + p` | Pretty-print JSON/XML                      |
| `i`         | Toggle histogram (log volume over time)    |
| `t`         | Toggle plain text view                     |
| `Ctrl + l`  | Lo-fi mode (no colors — great for copying) |
| `Ctrl + w`  | Toggle word wrap                           |

### Live Monitoring (Tailing)

- **Automatically follows** new lines — no extra flag needed.
- Scroll to the **bottom** → it follows live (like `tail -f`).
- Scroll **up** → following pauses (so you can read old logs).
- `=` → Pause / resume loading new data
- `:rebuild` → Force full refresh

### Marking Lines (super useful!)

- `m` → Mark / unmark current line
- `Shift + m` → Mark a range
- `c` → Copy all marked lines
- `Shift + c` → Clear all marks

### Handy Regex Examples

- `(?i)error|exception|fail` → Any error (case-insensitive)
- `(?i)timeout|refused|connection` → Network issues
- `\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b` → IP addresses

Watch the bottom-right corner — it shows context-specific hints while you use lnav.

## Multi-File Viewing & Navigation

| Action                        | Keys / Command                   |
| ----------------------------- | -------------------------------- |
| Next file                     | `Shift + j`                      |
| Previous file                 | `Shift + k`                      |
| Jump to specific file (fuzzy) | `Ctrl + t` then type filename    |
| Show only current file        | `:hide-other-files`              |
| Show all files again          | `:show-all-files`                |
| See list of loaded files      | `Tab` (opens config/files panel) |

The bottom status bar always shows the current file name (and how many files are loaded total). Watch that while pressing `Shift + j/k` to know when you've reached the one you want.

Try `Shift + j` first
