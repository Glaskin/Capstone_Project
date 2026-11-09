-- PAES LMS database (Supabase / PostgreSQL)
-- Run this whole file once in Supabase: Dashboard -> SQL Editor -> New query -> paste -> Run.
-- It is safe to re-run: it drops and recreates the app's tables (this DELETES their data).

drop table if exists quiz_attempts, lesson_progress, choices, questions, quizzes, lessons, modules, courses, announcements, profiles cascade;
drop function if exists is_staff, is_admin, handle_new_user, protect_profile_role, start_quiz, submit_quiz cascade;

-- ---------------------------------------------------------------- tables

create table profiles (
  id uuid primary key references auth.users on delete cascade,
  username text unique not null,
  first_name text not null default '',
  last_name text not null default '',
  email text not null default '',
  role text not null default 'student' check (role in ('student', 'faculty', 'admin')),
  created_at timestamptz not null default now()
);

create table courses (
  id bigint generated always as identity primary key,
  code text not null default '',
  title text not null,
  description text not null default '',
  color text not null default 'emerald',
  published boolean not null default true,
  position int not null default 0,
  created_at timestamptz not null default now()
);

create table modules (
  id bigint generated always as identity primary key,
  course_id bigint not null references courses on delete cascade,
  title text not null,
  description text not null default '',
  position int not null default 0
);

create table lessons (
  id bigint generated always as identity primary key,
  module_id bigint not null references modules on delete cascade,
  title text not null,
  content text not null default '',          -- Markdown
  video_url text not null default '',
  attachments jsonb not null default '[]',   -- [{ "name": "...", "url": "..." }]
  minutes int not null default 10,
  position int not null default 0
);

create table quizzes (
  id bigint generated always as identity primary key,
  module_id bigint not null references modules on delete cascade,
  title text not null,
  description text not null default '',
  question_limit int check (question_limit is null or question_limit > 0), -- null = all questions
  passing_percent int not null default 70 check (passing_percent between 0 and 100),
  time_limit_minutes int check (time_limit_minutes is null or time_limit_minutes > 0),
  position int not null default 0
);

create table questions (
  id bigint generated always as identity primary key,
  quiz_id bigint not null references quizzes on delete cascade,
  prompt text not null,
  kind text not null default 'multiple_choice' check (kind in ('multiple_choice', 'true_false')),
  explanation text not null default '',
  position int not null default 0
);

create table choices (
  id bigint generated always as identity primary key,
  question_id bigint not null references questions on delete cascade,
  text text not null,
  is_correct boolean not null default false
);

create table announcements (
  id bigint generated always as identity primary key,
  title text not null,
  body text not null default '',
  pinned boolean not null default false,
  created_at timestamptz not null default now()
);

create table lesson_progress (
  user_id uuid not null references profiles on delete cascade,
  lesson_id bigint not null references lessons on delete cascade,
  completed_at timestamptz not null default now(),
  primary key (user_id, lesson_id)
);

create table quiz_attempts (
  id bigint generated always as identity primary key,
  user_id uuid not null references profiles on delete cascade,
  quiz_id bigint not null references quizzes on delete cascade,
  score int not null,
  total int not null,
  percent int not null,
  passed boolean not null,
  created_at timestamptz not null default now()
);

create index on modules (course_id);
create index on lessons (module_id);
create index on quizzes (module_id);
create index on questions (quiz_id);
create index on choices (question_id);
create index on quiz_attempts (user_id);
create index on quiz_attempts (quiz_id);

-- ---------------------------------------------------------------- helpers

create function is_staff() returns boolean language sql stable security definer set search_path = public as $$
  select exists (select 1 from profiles where id = auth.uid() and role in ('faculty', 'admin'));
$$;

create function is_admin() returns boolean language sql stable security definer set search_path = public as $$
  select exists (select 1 from profiles where id = auth.uid() and role = 'admin');
$$;

-- New sign-ups get a student profile. Username/name come from the sign-up form.
create function handle_new_user() returns trigger language plpgsql security definer set search_path = public as $$
begin
  insert into profiles (id, username, first_name, last_name, email)
  values (
    new.id,
    coalesce(nullif(new.raw_user_meta_data->>'username', ''), split_part(new.email, '@', 1) || '-' || substr(new.id::text, 1, 4)),
    coalesce(new.raw_user_meta_data->>'first_name', ''),
    coalesce(new.raw_user_meta_data->>'last_name', ''),
    new.email
  );
  return new;
end $$;

create trigger on_auth_user_created after insert on auth.users
  for each row execute function handle_new_user();

-- Only admins may change anyone's role (users can still edit their own name).
-- auth.uid() is null when run from the SQL Editor, so you can promote the first admin there.
create function protect_profile_role() returns trigger language plpgsql security definer set search_path = public as $$
begin
  if new.role is distinct from old.role and auth.uid() is not null and not is_admin() then
    raise exception 'Only admins can change roles';
  end if;
  return new;
end $$;

create trigger profiles_protect_role before update on profiles
  for each row execute function protect_profile_role();

-- ---------------------------------------------------------------- row level security

alter table profiles enable row level security;
alter table courses enable row level security;
alter table modules enable row level security;
alter table lessons enable row level security;
alter table quizzes enable row level security;
alter table questions enable row level security;
alter table choices enable row level security;
alter table announcements enable row level security;
alter table lesson_progress enable row level security;
alter table quiz_attempts enable row level security;

create policy "read own or staff" on profiles for select to authenticated using (id = auth.uid() or is_staff());
create policy "update own" on profiles for update to authenticated using (id = auth.uid()) with check (id = auth.uid());
create policy "admin updates" on profiles for update to authenticated using (is_admin());
create policy "admin deletes" on profiles for delete to authenticated using (is_admin());

-- Course content: every signed-in user reads (students only published courses); staff write.
create policy "read" on courses for select to authenticated using (published or is_staff());
create policy "staff write" on courses for all to authenticated using (is_staff()) with check (is_staff());
create policy "read" on modules for select to authenticated using (true);
create policy "staff write" on modules for all to authenticated using (is_staff()) with check (is_staff());
create policy "read" on lessons for select to authenticated using (true);
create policy "staff write" on lessons for all to authenticated using (is_staff()) with check (is_staff());
create policy "read" on quizzes for select to authenticated using (true);
create policy "staff write" on quizzes for all to authenticated using (is_staff()) with check (is_staff());
create policy "read" on announcements for select to authenticated using (true);
create policy "staff write" on announcements for all to authenticated using (is_staff()) with check (is_staff());

-- Questions and answer keys: staff only. Students get them through start_quiz() without the answers.
create policy "staff" on questions for all to authenticated using (is_staff()) with check (is_staff());
create policy "staff" on choices for all to authenticated using (is_staff()) with check (is_staff());

create policy "own" on lesson_progress for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());
create policy "staff read" on lesson_progress for select to authenticated using (is_staff());

-- Attempts are only created by submit_quiz(), so scores can't be faked.
create policy "read own or staff" on quiz_attempts for select to authenticated using (user_id = auth.uid() or is_staff());

-- ---------------------------------------------------------------- quiz functions

-- Returns a random set of questions (no answer key) for a student to take.
create function start_quiz(p_quiz_id bigint) returns jsonb language plpgsql security definer set search_path = public as $$
declare
  q quizzes;
  result jsonb;
begin
  if auth.uid() is null then raise exception 'Not signed in'; end if;
  select * into q from quizzes where id = p_quiz_id;
  if not found then raise exception 'Quiz not found'; end if;

  select coalesce(jsonb_agg(jsonb_build_object(
           'id', x.id, 'prompt', x.prompt, 'kind', x.kind,
           'choices', (select coalesce(jsonb_agg(jsonb_build_object('id', c.id, 'text', c.text)
                         order by case when x.kind = 'true_false' then c.id end, random()), '[]')
                       from choices c where c.question_id = x.id))), '[]')
    into result
    from (select * from questions where quiz_id = p_quiz_id order by random() limit q.question_limit) x;

  return jsonb_build_object('quiz', to_jsonb(q), 'questions', result);
end $$;

-- Grades a submission on the server. p_answers = { "<question_id>": <choice_id>, ... }
create function submit_quiz(p_quiz_id bigint, p_answers jsonb) returns jsonb language plpgsql security definer set search_path = public as $$
declare
  q quizzes;
  expected int;
  answered int;
  correct int;
  pct int;
  review jsonb;
begin
  if auth.uid() is null then raise exception 'Not signed in'; end if;
  select * into q from quizzes where id = p_quiz_id;
  if not found then raise exception 'Quiz not found'; end if;

  select count(*) into expected from questions where quiz_id = p_quiz_id;
  expected := least(expected, coalesce(q.question_limit, expected));

  -- Every submitted question must belong to this quiz, and the count must match what start_quiz handed out.
  select count(*) into answered from questions
   where quiz_id = p_quiz_id and id::text in (select jsonb_object_keys(p_answers));
  if answered <> expected or answered <> (select count(*) from jsonb_object_keys(p_answers)) then
    raise exception 'Answers do not match this quiz';
  end if;

  select count(*) into correct
    from questions x join choices c on c.question_id = x.id and c.is_correct
   where x.quiz_id = p_quiz_id and (p_answers->>x.id::text)::bigint = c.id;

  pct := case when expected = 0 then 0 else round(correct * 100.0 / expected) end;

  insert into quiz_attempts (user_id, quiz_id, score, total, percent, passed)
  values (auth.uid(), p_quiz_id, correct, expected, pct, pct >= q.passing_percent);

  select jsonb_agg(jsonb_build_object(
           'questionId', x.id, 'prompt', x.prompt, 'explanation', x.explanation,
           'chosenId', (p_answers->>x.id::text)::bigint,
           'correctId', (select c.id from choices c where c.question_id = x.id and c.is_correct limit 1),
           'choices', (select jsonb_agg(jsonb_build_object('id', c.id, 'text', c.text)) from choices c where c.question_id = x.id)))
    into review
    from questions x where x.quiz_id = p_quiz_id and x.id::text in (select jsonb_object_keys(p_answers));

  return jsonb_build_object('score', correct, 'total', expected, 'percent', pct,
                            'passed', pct >= q.passing_percent, 'review', coalesce(review, '[]'));
end $$;

revoke execute on function start_quiz, submit_quiz from anon;

-- ---------------------------------------------------------------- file storage (lesson attachments, images, videos)

insert into storage.buckets (id, name, public, file_size_limit)
values ('lesson-files', 'lesson-files', true, 52428800)
on conflict (id) do nothing;

drop policy if exists "staff upload lesson files" on storage.objects;
drop policy if exists "staff delete lesson files" on storage.objects;
create policy "staff upload lesson files" on storage.objects for insert to authenticated
  with check (bucket_id = 'lesson-files' and public.is_staff());
create policy "staff delete lesson files" on storage.objects for delete to authenticated
  using (bucket_id = 'lesson-files' and public.is_staff());
