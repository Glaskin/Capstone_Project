# PAES Learn

A learning portal for the **Philippine Agricultural Engineering Standards (PAES)**: courses → modules → lessons and quizzes, with progress tracking, certificates, and teaching tools for faculty and admins.

The new app is in [`web/`](web) and its database is in [`supabase/schema.sql`](supabase/schema.sql).
The old PHP version is still in `LMS_PAES/` for reference.

## Features

**Students**
- Dashboard with "continue where you left off", day streak, stats and announcements
- Course catalog with search and progress bars
- Lessons in Markdown with tables, images, embedded YouTube/Vimeo video and downloadable attachments
- Quizzes: random question sets, optional time limit, instant grading, an explanation for every answer, unlimited retakes (best score counts), confetti 🎉
- Printable certificate when a course is complete
- Profile with quiz history; dark mode; works on phones

**Faculty and admins**
- Course builder: courses, modules, lessons and quizzes, with reordering and draft/published states
- Lesson editor with live preview and image/file uploads
- Quiz builder for multiple-choice and true/false questions, question limit, passing score and time limit
- Reports: quiz pass rates and average scores, per-student progress table, CSV export
- Announcements (pinned or normal); admins can change anyone's role

**Security**
- Quizzes are graded in the database (`submit_quiz`), so the answer key never reaches the browser and scores can't be faked
- Row-level security on every table: students can read content and only their own progress; only staff can edit
- No passwords or secrets in the code. Supabase handles login, email confirmation and password resets.

## Run it locally

You need [Node.js](https://nodejs.org) 20 or newer.

```bash
cd web
npm install
npm run dev
```

Open the address it prints. With no database configured, the app runs in **Demo mode**: everything is saved in your browser, and the login page has one-click Student / Faculty / Admin accounts.

## Connect the real database (Supabase, free)

1. Create a free project at <https://supabase.com>.
2. In the project, open **SQL Editor → New query**, paste the whole of [`supabase/schema.sql`](supabase/schema.sql), and click **Run**.
3. Open **Authentication → URL Configuration**:
   - **Site URL**: your site address, e.g. `https://glaskin.github.io/Capstone_Project/`
   - **Redirect URLs**: add the same address and `http://localhost:5173/`
4. Open **Project Settings → API**. Copy the **Project URL** and the **anon public** key into `web/.env.local`:
   ```
   VITE_SUPABASE_URL=https://xxxx.supabase.co
   VITE_SUPABASE_ANON_KEY=eyJ...
   ```
   (The anon key is designed to be public; row-level security protects the data.)
5. Register an account on the site, then make yourself admin in the **SQL Editor**:
   ```sql
   update profiles set role = 'admin' where email = 'you@example.com';
   ```
6. Sign in, go to **Manage courses → Add sample courses** to load the starter PAES content.

## Publish on GitHub Pages (free)

The workflow in `.github/workflows/deploy.yml` builds `web/` and publishes it on every push to `main`.

1. **Settings → Pages → Build and deployment → Source: GitHub Actions**.
2. Optional, to go live with the real database: **Settings → Secrets and variables → Actions → Variables** and add `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY`. Without them the site is published in Demo mode.
3. Push to `main` (or run the workflow from the **Actions** tab). The site appears at `https://<user>.github.io/<repo>/`.

> On a free GitHub plan, Pages only works for **public** repositories. GitHub Pro (free with the GitHub Student Developer Pack) allows it for private ones.

## Project layout

```
supabase/schema.sql        tables, security rules, quiz grading functions, file storage
web/src/lib/api/           data layer: supabase.ts (real) and demo.ts (in-browser), same interface
web/src/lib/seed.ts        starter PAES courses
web/src/pages/             student pages
web/src/pages/staff/       faculty/admin pages
web/src/components/        layout and shared UI
```

Built with React, TypeScript, Vite, Tailwind CSS and Supabase.
