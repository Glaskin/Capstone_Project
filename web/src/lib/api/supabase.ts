import { createClient } from '@supabase/supabase-js'
import type { Api, Course, CourseTree, Lesson, Module, Profile, Question, Quiz } from '../types'
import { sortTree } from '../progress'

export function createSupabaseApi(url: string, anonKey: string): Api {
  // PKCE puts the login code in ?code=… instead of the #hash, which the app's HashRouter owns.
  const sb = createClient(url, anonKey, { auth: { flowType: 'pkce' } })

  // Supabase returns { data, error }; turn errors into exceptions the UI can show.
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  async function must<T = any>(p: PromiseLike<{ data: any; error: { message: string } | null }>): Promise<T> {
    const { data, error } = await p
    if (error) throw new Error(error.message)
    return data as T
  }

  async function uid() {
    const { data } = await sb.auth.getSession()
    if (!data.session) throw new Error('Not signed in')
    return data.session.user.id
  }

  // Insert when there's no id, otherwise update; returns the saved row.
  function save(table: string, row: { id?: number }) {
    const { id, ...rest } = row
    const q = id ? sb.from(table).update(rest).eq('id', id) : sb.from(table).insert(rest)
    return must(q.select().single())
  }
  const del = (table: string, id: number) => must(sb.from(table).delete().eq('id', id)).then(() => {})

  const redirect = () => `${location.origin}${location.pathname}`

  return {
    mode: 'supabase',

    async currentProfile() {
      const { data } = await sb.auth.getSession()
      if (!data.session) return null
      return must(sb.from('profiles').select('*').eq('id', data.session.user.id).single()) as Promise<Profile>
    },
    onAuthChange(cb) {
      const { data } = sb.auth.onAuthStateChange((event) => {
        if (event === 'PASSWORD_RECOVERY') location.hash = '#/reset-password'
        if (event === 'SIGNED_IN' && location.search) history.replaceState(null, '', location.pathname + location.hash) // drop ?code=
        // Defer: Supabase forbids awaiting its own calls inside this callback.
        setTimeout(cb, 0)
      })
      return () => data.subscription.unsubscribe()
    },
    async signIn(email, password) {
      await must(sb.auth.signInWithPassword({ email, password }))
    },
    async signUp({ email, password, ...meta }) {
      const data = await must(sb.auth.signUp({ email, password, options: { data: meta, emailRedirectTo: redirect() } }))
      return { needsEmailConfirmation: !data.session }
    },
    async signOut() {
      await sb.auth.signOut()
    },
    async sendPasswordReset(email) {
      await must(sb.auth.resetPasswordForEmail(email, { redirectTo: `${redirect()}?reset=1` }))
    },
    async updatePassword(password) {
      await must(sb.auth.updateUser({ password }))
    },
    async updateProfile(patch) {
      await must(sb.from('profiles').update(patch).eq('id', await uid()))
    },

    async listCourseTrees() {
      const rows = await must(
        sb.from('courses').select(
          '*, modules(*, lessons(id, module_id, title, video_url, attachments, minutes, position), quizzes(*))',
        ),
      )
      return sortTree(rows as CourseTree[])
    },
    async getLesson(id) {
      return must(sb.from('lessons').select('*').eq('id', id).single()) as Promise<Lesson>
    },
    saveCourse: (c) => save('courses', c) as Promise<Course>,
    deleteCourse: (id) => del('courses', id),
    saveModule: (m) => save('modules', m) as Promise<Module>,
    deleteModule: (id) => del('modules', id),
    saveLesson: (l) => save('lessons', l) as Promise<Lesson>,
    deleteLesson: (id) => del('lessons', id),
    saveQuiz: (q) => save('quizzes', q) as Promise<Quiz>,
    deleteQuiz: (id) => del('quizzes', id),
    async listQuestions(quizId) {
      const rows = await must(sb.from('questions').select('*, choices(*)').eq('quiz_id', quizId).order('position').order('id'))
      return (rows as Question[]).map((q) => ({ ...q, choices: q.choices.sort((a, b) => a.id - b.id) }))
    },
    async saveQuestion({ choices, ...q }) {
      const saved = await save('questions', q)
      // Replace the choices wholesale; attempts store scores, not choice ids, so this is safe.
      await must(sb.from('choices').delete().eq('question_id', saved.id))
      await must(
        sb.from('choices').insert(choices.map((c) => ({ question_id: saved.id, text: c.text, is_correct: !!c.is_correct }))),
      )
    },
    deleteQuestion: (id) => del('questions', id),
    async uploadFile(file) {
      const path = `${crypto.randomUUID()}-${file.name.replace(/[^\w.-]+/g, '_')}`
      await must(sb.storage.from('lesson-files').upload(path, file))
      return sb.storage.from('lesson-files').getPublicUrl(path).data.publicUrl
    },

    startQuiz: (quizId) => must(sb.rpc('start_quiz', { p_quiz_id: quizId })),
    submitQuiz: (quizId, answers) => must(sb.rpc('submit_quiz', { p_quiz_id: quizId, p_answers: answers })),
    async myProgress() {
      const id = await uid()
      const [lessons, attempts] = await Promise.all([
        must(sb.from('lesson_progress').select('*').eq('user_id', id)),
        must(sb.from('quiz_attempts').select('*').eq('user_id', id).order('created_at', { ascending: false })),
      ])
      return { lessons, attempts }
    },
    async setLessonComplete(lessonId, done) {
      const user_id = await uid()
      if (done) await must(sb.from('lesson_progress').upsert({ user_id, lesson_id: lessonId }))
      else await must(sb.from('lesson_progress').delete().match({ user_id, lesson_id: lessonId }))
    },

    listAnnouncements: () =>
      must(sb.from('announcements').select('*').order('pinned', { ascending: false }).order('created_at', { ascending: false })),
    async saveAnnouncement(a) {
      await save('announcements', a)
    },
    deleteAnnouncement: (id) => del('announcements', id),

    listProfiles: () => must(sb.from('profiles').select('*').order('created_at')),
    async setRole(userId, role) {
      await must(sb.from('profiles').update({ role }).eq('id', userId))
    },
    async allProgress() {
      const [lessons, attempts] = await Promise.all([
        must(sb.from('lesson_progress').select('*')),
        must(sb.from('quiz_attempts').select('*').order('created_at', { ascending: false })),
      ])
      return { lessons, attempts }
    },
  }
}
