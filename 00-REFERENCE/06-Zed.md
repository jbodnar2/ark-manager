# Zed Code Editor

**Official site & docs:** https://zed.dev  
**Download:** https://zed.dev/download  
**In-app help:** Press `Cmd/Ctrl + Shift + P` → type "zed:" to see Zed-specific commands

## Quick Start

1. Download & install Zed (macOS, Linux, Windows preview available).
2. On first launch: Choose base keymap (VS Code recommended if coming from VS Code).
3. Open folder/project: `Cmd/Ctrl + O` or drag folder into Zed.
4. Most important shortcut: **Command Palette** → `Cmd/Ctrl + Shift + P`  
   → Search anything (commands, settings, files, symbols).

Auto-save is on by default — no need to hit save!

## Essential Navigation & File Management

| Keys (macOS / Linux+Windows) | Action                                |
| ---------------------------- | ------------------------------------- |
| `Cmd/Ctrl + P`               | Go to file (fuzzy search)             |
| `Cmd/Ctrl + Shift + O`       | Go to symbol in current file          |
| `Cmd/Ctrl + Shift + F`       | Find in project (search across files) |
| `Cmd/Ctrl + T`               | Toggle file tree sidebar              |
| `Cmd/Ctrl + \`               | Toggle terminal panel                 |
| `Cmd/Ctrl + W`               | Close current tab                     |
| `Cmd/Ctrl + Shift + N`       | New window                            |

**Pro tip:** Zed has no "open editors" sidebar like VS Code — use `Cmd/Ctrl + P` to jump between files.

## Editing Basics

| Keys                         | Action                                 |
| ---------------------------- | -------------------------------------- |
| `Cmd/Ctrl + Z` / `Shift + Z` | Undo / Redo                            |
| `Cmd/Ctrl + D`               | Select next occurrence of word         |
| `Cmd/Ctrl + Shift + L`       | Select all occurrences                 |
| `Alt + Up / Down`            | Grow / shrink selection (syntax-aware) |
| `Cmd/Ctrl + M`               | Jump to matching bracket               |
| `Cmd/Ctrl + /`               | Toggle line comment                    |
| `Alt + Cmd/Ctrl + Up/Down`   | Add cursor above/below (multi-cursor)  |
| `Cmd/Ctrl + Enter`           | Insert line below                      |

## Views & Panels

| Keys                               | Action                                |
| ---------------------------------- | ------------------------------------- |
| `Cmd/Ctrl + ,`                     | Open settings                         |
| `Cmd/Ctrl + K Cmd/Ctrl + T`        | Change theme                          |
| `Cmd/Ctrl + K Cmd/Ctrl + S`        | Open keymap editor (custom shortcuts) |
| `F8` / `Shift + F8`                | Next / previous error or diagnostic   |
| `Cmd/Ctrl + Shift + P` → "Outline" | Show file outline (symbols tree)      |

## Terminal & Git

- Toggle integrated terminal: `Ctrl + `` (backtick)
- Git changes show in gutter; stage/commit via status bar or command palette (`git:` commands)
- Collaboration: Sign in with GitHub → status bar people icon → create/join channels (real-time multiplayer editing)

## AI Features (built-in, optional)

- Inline AI: Select code → `Cmd/Ctrl + .` → ask to edit/explain/refactor
- Agent mode: Chat with codebase (add context via drag files or @-mentions)
- Toggle AI panel or use command palette: "zed: open assistant"

## Customization Basics

Edit `~/.config/zed/settings.json` (or via `Cmd/Ctrl + ,`):

```json
{
    "theme": "One Dark",
    "ui_font_size": 16,
    "buffer_font_size": 15,
    "vim_mode": false, // set true if you want Vim keys
    "base_keymap": "VSCode", // or "JetBrains", "None", etc.
    "autosave": "on_window_change"
}
```
