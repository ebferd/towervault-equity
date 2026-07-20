# Updating the live site (without re-uploading everything)

Your actual code is tiny — the big `vendor/` folder (Composer libraries)
almost never changes. So an update only needs to send the **few files you
changed**, never the whole project again.

## ⚠️ The golden rule — never overwrite these on the server

These three exist ONLY on the live server and hold real data/config. If an
upload replaces them you lose settings or user files:

- `.env`            → your live database + mail credentials
- `uploads/`        → investor documents, KYC files, logos
- `storage/logs/`   → error logs

Every method below is set up to leave those alone.

---

## Method A — WinSCP "Synchronize" (easiest, no command line)

Best if your host only gives you FTP / File Manager.

1. Install **WinSCP** (free) and connect to your server with your FTP details.
2. Left pane = your PC's project folder. Right pane = the server folder.
3. Menu: **Commands → Synchronize…**
   - Direction: **Remote** (local → server)
   - Target: the site's root folder on the server
4. In **Synchronize → … → Transfer settings → File mask**, add these
   exclusions so protected files/folders are never touched:
   ```
   | .env; uploads/; storage/logs/; storage/tmp/
   ```
5. Click **OK** — WinSCP compares both sides and uploads **only the changed
   files**. Review the list, confirm.

That's it. Change a file locally, run Synchronize, done in seconds.

---

## Method B — Git (cleanest, if you have cPanel "Git Version Control" or SSH)

One-time setup:

1. Put this project in a **GitHub repo** (private).
2. On the server, either use **cPanel → Git Version Control → Create** and
   point it at the repo, or over SSH:
   ```
   git clone <your-repo-url> .
   composer install --no-dev      # installs vendor/ once
   ```
3. Copy `.env.example` to `.env` and fill in live values (or keep the `.env`
   the installer already wrote).

Each update after that:

```
# on your PC
git add -A && git commit -m "what changed" && git push

# on the server (cPanel Git "Pull or Deploy", or over SSH)
git pull
```

Only changed files move. `.env`, `uploads/`, `storage/` are git-ignored, so
they're never disturbed.

> If your host has **no** composer, edit `.gitignore` and remove the
> `/vendor/` line so the libraries travel with the repo and `git pull` alone
> is enough.

---

## When a change touches the database

Editing files is enough for most changes. But if a change adds/renames a
**database column or table**, you must also run the SQL on the live database
(cPanel → phpMyAdmin → SQL tab).

Keep following the existing convention in `database/`: each schema change gets
its own numbered file (`migration_v8.sql`, `migration_v9.sql`, …). Run the new
one once on the live DB after deploying the code. `schema.sql` stays the
full fresh-install file.
