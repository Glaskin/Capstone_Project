import type {
  Announcement, Api, Choice, Course, CourseTree, Lesson, LessonProgress, Module, Profile, Question, Quiz, QuizAttempt,
} from '../types'
import { importSeed } from '../seed'
import { sortTree } from '../progress'

// Demo backend: the whole database lives in this browser's localStorage.
// Same rules as the Supabase version (staff-only edits, server-style grading) so the UI behaves identically.
// shortcut: no passwords — any password signs in to a demo account; use Supabase for anything real.

const KEY = 'paes-demo-v1'

interface State {
  seq: number
  currentUserId: string | null
  profiles: Profile[]
  courses: Course[]
  modules: Module[]
  lessons: Lesson[]
  quizzes: Quiz[]
  questions: Omit<Question, 'choices'>[]
  choices: Choice[]
  announcements: Announcement[]
  lesson_progress: LessonProgress[]
  quiz_attempts: QuizAttempt[]
}

const DEFAULTS = {
  course: { code: '', title: '', description: '', color: 'emerald', published: true, position: 0 },
  module: { course_id: 0, title: '', description: '', position: 0 },
  lesson: { module_id: 0, title: '', content: '', video_url: '', attachments: [], minutes: 10, position: 0 },
  quiz: { module_id: 0, title: '', description: '', question_limit: null, passing_percent: 70, time_limit_minutes: null, position: 0 },
  question: { quiz_id: 0, prompt: '', kind: 'multiple_choice' as const, explanation: '', position: 0 },
}

const empty = (): State => ({
  seq: 1, currentUserId: null, profiles: [], courses: [], modules: [], lessons: [], quizzes: [],
  questions: [], choices: [], announcements: [], lesson_progress: [], quiz_attempts: [],
})

export const DEMO_ACCOUNTS = [
  { email: 'student@demo.paes', label: 'Student', role: 'student' as const },
  { email: 'faculty@demo.paes', label: 'Faculty', role: 'faculty' as const },
  { email: 'admin@demo.paes', label: 'Admin', role: 'admin' as const },
]

export function resetDemo() {
  localStorage.removeItem(KEY)
  location.reload()
}

export function createDemoApi(): Api {
  let s: State = load()
  const listeners = new Set<() => void>()
  const ready = s.profiles.length ? Promise.resolve() : Promise.resolve().then(seed) // deferred: seed() uses `api` below

  function load(): State {
    try {
      return { ...empty(), ...JSON.parse(localStorage.getItem(KEY) ?? '{}') }
    } catch {
      return empty()
    }
  }
  function commit() {
    try {
      localStorage.setItem(KEY, JSON.stringify(s))
    } catch {
      throw new Error('Browser storage is full. Remove large uploaded files or reset the demo.')
    }
  }
  const now = () => new Date().toISOString()
  const id = () => s.seq++
  const clone = <T,>(x: T): T => structuredClone(x)
  const delay = () => new Promise((r) => setTimeout(r, 120)) // feel like a network

  function me(): Profile {
    const p = s.profiles.find((p) => p.id === s.currentUserId)
    if (!p) throw new Error('Not signed in')
    return p
  }
  function staff() {
    if (!['faculty', 'admin'].includes(me().role)) throw new Error('Only faculty and admins can do that')
  }
  async function op<T>(fn: () => T): Promise<T> {
    await ready
    await delay()
    const result = fn()
    commit()
    return clone(result)
  }

  // Insert when there's no id, otherwise update.
  function save<T extends { id: number }>(table: T[], row: Partial<T>, defaults: Omit<T, 'id'>): T {
    if (row.id) {
      const existing = table.find((r) => r.id === row.id)
      if (!existing) throw new Error('Not found')
      Object.assign(existing, row)
      return existing
    }
    const created = { ...clone(defaults), ...row, id: id() } as T
    table.push(created)
    return created
  }
  function cascadeDelete(kind: 'course' | 'module' | 'lesson' | 'quiz' | 'question', target: number) {
    if (kind === 'course') {
      s.modules.filter((m) => m.course_id === target).forEach((m) => cascadeDelete('module', m.id))
      s.courses = s.courses.filter((c) => c.id !== target)
    } else if (kind === 'module') {
      s.lessons.filter((l) => l.module_id === target).forEach((l) => cascadeDelete('lesson', l.id))
      s.quizzes.filter((q) => q.module_id === target).forEach((q) => cascadeDelete('quiz', q.id))
      s.modules = s.modules.filter((m) => m.id !== target)
    } else if (kind === 'lesson') {
      s.lessons = s.lessons.filter((l) => l.id !== target)
      s.lesson_progress = s.lesson_progress.filter((p) => p.lesson_id !== target)
    } else if (kind === 'quiz') {
      s.questions.filter((q) => q.quiz_id === target).forEach((q) => cascadeDelete('question', q.id))
      s.quizzes = s.quizzes.filter((q) => q.id !== target)
      s.quiz_attempts = s.quiz_attempts.filter((a) => a.quiz_id !== target)
    } else {
      s.choices = s.choices.filter((c) => c.question_id !== target)
      s.questions = s.questions.filter((q) => q.id !== target)
    }
  }
  const shuffle = <T,>(xs: T[]) => {
    const a = [...xs]
    for (let i = a.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1))
      ;[a[i], a[j]] = [a[j], a[i]]
    }
    return a
  }
  const emit = () => listeners.forEach((l) => l())

  const api: Api = {
    mode: 'demo',

    async currentProfile() {
      await ready
      return clone(s.profiles.find((p) => p.id === s.currentUserId) ?? null)
    },
    onAuthChange(cb) {
      listeners.add(cb)
      return () => listeners.delete(cb)
    },
    async signIn(email) {
      await op(() => {
        const p = s.profiles.find((p) => p.email.toLowerCase() === email.trim().toLowerCase())
        if (!p) throw new Error('No demo account with that email. Use a demo button or create an account.')
        s.currentUserId = p.id
      })
      emit()
    },
    async signUp({ email, username, first_name, last_name }) {
      await op(() => {
        if (s.profiles.some((p) => p.email.toLowerCase() === email.toLowerCase())) throw new Error('That email is already registered.')
        if (s.profiles.some((p) => p.username.toLowerCase() === username.toLowerCase())) throw new Error('That username is taken.')
        const p: Profile = { id: crypto.randomUUID(), email, username, first_name, last_name, role: 'student', created_at: now() }
        s.profiles.push(p)
        s.currentUserId = p.id
      })
      emit()
      return { needsEmailConfirmation: false }
    },
    async signOut() {
      s.currentUserId = null
      commit()
      emit()
    },
    async sendPasswordReset() {
      await delay()
    },
    async updatePassword() {
      await delay()
    },
    updateProfile: (patch) =>
      op(() => {
        if (s.profiles.some((p) => p.id !== me().id && p.username.toLowerCase() === patch.username.toLowerCase()))
          throw new Error('That username is taken.')
        Object.assign(me(), patch)
      }),

    listCourseTrees: () =>
      op(() => {
        const isStaff = ['faculty', 'admin'].includes(me().role)
        const trees: CourseTree[] = s.courses
          .filter((c) => c.published || isStaff)
          .map((c) => ({
            ...c,
            modules: s.modules
              .filter((m) => m.course_id === c.id)
              .map((m) => ({
                ...m,
                lessons: s.lessons.filter((l) => l.module_id === m.id).map((l) => ({ ...l, content: '' })),
                quizzes: s.quizzes.filter((q) => q.module_id === m.id),
              })),
          }))
        return sortTree(trees)
      }),
    getLesson: (lessonId) =>
      op(() => {
        me()
        const l = s.lessons.find((l) => l.id === lessonId)
        if (!l) throw new Error('Lesson not found')
        return l
      }),
    saveCourse: (c) => op(() => (staff(), save(s.courses, c, DEFAULTS.course))),
    deleteCourse: (cid) => op(() => (staff(), cascadeDelete('course', cid))),
    saveModule: (m) => op(() => (staff(), save(s.modules, m, DEFAULTS.module))),
    deleteModule: (mid) => op(() => (staff(), cascadeDelete('module', mid))),
    saveLesson: (l) =>
      op(() => (staff(), save(s.lessons, l, DEFAULTS.lesson))),
    deleteLesson: (lid) => op(() => (staff(), cascadeDelete('lesson', lid))),
    saveQuiz: (q) =>
      op(() => (staff(), save(s.quizzes, q, DEFAULTS.quiz))),
    deleteQuiz: (qid) => op(() => (staff(), cascadeDelete('quiz', qid))),
    listQuestions: (quizId) =>
      op(() => {
        staff()
        return s.questions
          .filter((q) => q.quiz_id === quizId)
          .sort((a, b) => a.position - b.position || a.id - b.id)
          .map((q) => ({ ...q, choices: s.choices.filter((c) => c.question_id === q.id) }))
      }),
    saveQuestion: ({ choices, ...q }) =>
      op(() => {
        staff()
        const saved = save(s.questions, q, DEFAULTS.question)
        s.choices = s.choices.filter((c) => c.question_id !== saved.id)
        for (const c of choices) s.choices.push({ id: id(), question_id: saved.id, text: c.text ?? '', is_correct: !!c.is_correct })
      }),
    deleteQuestion: (qid) => op(() => (staff(), cascadeDelete('question', qid))),
    async uploadFile(file) {
      staff()
      if (file.size > 2 * 1024 * 1024) throw new Error('Demo mode stores files in the browser, so the limit is 2 MB. Connect Supabase for large files.')
      return new Promise<string>((resolve, reject) => {
        const r = new FileReader()
        r.onload = () => resolve(r.result as string)
        r.onerror = () => reject(new Error('Could not read file'))
        r.readAsDataURL(file)
      })
    },

    startQuiz: (quizId) =>
      op(() => {
        me()
        const quiz = s.quizzes.find((q) => q.id === quizId)
        if (!quiz) throw new Error('Quiz not found')
        const picked = shuffle(s.questions.filter((q) => q.quiz_id === quizId)).slice(0, quiz.question_limit ?? undefined)
        return {
          quiz,
          questions: picked.map((q) => {
            const cs = s.choices.filter((c) => c.question_id === q.id).map(({ id, text }) => ({ id, text }))
            return { id: q.id, prompt: q.prompt, kind: q.kind, choices: q.kind === 'true_false' ? cs : shuffle(cs) }
          }),
        }
      }),
    submitQuiz: (quizId, answers) =>
      op(() => {
        const user = me()
        const quiz = s.quizzes.find((q) => q.id === quizId)
        if (!quiz) throw new Error('Quiz not found')
        const all = s.questions.filter((q) => q.quiz_id === quizId)
        const expected = Math.min(all.length, quiz.question_limit ?? all.length)
        const asked = all.filter((q) => String(q.id) in answers)
        if (asked.length !== expected || Object.keys(answers).length !== expected) throw new Error('Answers do not match this quiz')
        const review = asked.map((q) => {
          const cs = s.choices.filter((c) => c.question_id === q.id)
          return {
            questionId: q.id, prompt: q.prompt, explanation: q.explanation,
            chosenId: answers[q.id] ?? null,
            correctId: cs.find((c) => c.is_correct)?.id ?? null,
            choices: cs.map(({ id, text }) => ({ id, text })),
          }
        })
        const score = review.filter((r) => r.chosenId != null && r.chosenId === r.correctId).length
        const percent = expected ? Math.round((score * 100) / expected) : 0
        const passed = percent >= quiz.passing_percent
        s.quiz_attempts.push({ id: id(), user_id: user.id, quiz_id: quizId, score, total: expected, percent, passed, created_at: now() })
        return { score, total: expected, percent, passed, review }
      }),
    myProgress: () =>
      op(() => {
        const uid = me().id
        return {
          lessons: s.lesson_progress.filter((p) => p.user_id === uid),
          attempts: s.quiz_attempts.filter((a) => a.user_id === uid).sort((a, b) => b.created_at.localeCompare(a.created_at)),
        }
      }),
    setLessonComplete: (lessonId, done) =>
      op(() => {
        const uid = me().id
        s.lesson_progress = s.lesson_progress.filter((p) => !(p.user_id === uid && p.lesson_id === lessonId))
        if (done) s.lesson_progress.push({ user_id: uid, lesson_id: lessonId, completed_at: now() })
      }),

    listAnnouncements: () =>
      op(() => (me(), [...s.announcements].sort((a, b) => Number(b.pinned) - Number(a.pinned) || b.created_at.localeCompare(a.created_at)))),
    saveAnnouncement: (a) => op(() => (staff(), void save(s.announcements, a, { title: '', body: '', pinned: false, created_at: now() }))),
    deleteAnnouncement: (aid) => op(() => (staff(), void (s.announcements = s.announcements.filter((a) => a.id !== aid)))),

    listProfiles: () => op(() => (staff(), s.profiles)),
    setRole: (userId, role) =>
      op(() => {
        if (me().role !== 'admin') throw new Error('Only admins can change roles')
        const p = s.profiles.find((p) => p.id === userId)
        if (!p) throw new Error('User not found')
        p.role = role
      }),
    allProgress: () => op(() => (staff(), { lessons: s.lesson_progress, attempts: s.quiz_attempts })),
  }

  // First visit: build the sample school.
  async function seed() {
    const daysAgo = (n: number) => new Date(Date.now() - n * 86400000).toISOString()
    const person = (email: string, username: string, first_name: string, last_name: string, role: Profile['role'], joined: number): Profile =>
      ({ id: crypto.randomUUID(), email, username, first_name, last_name, role, created_at: daysAgo(joined) })
    const admin = person('admin@demo.paes', 'admin', 'Alex', 'Reyes', 'admin', 30)
    const juan = person('student@demo.paes', 'juan', 'Juan', 'Dela Cruz', 'student', 14)
    const ana = person('ana@demo.paes', 'ana', 'Ana', 'Lopez', 'student', 12)
    const mark = person('mark@demo.paes', 'mark', 'Mark', 'Villanueva', 'student', 9)
    s.profiles = [admin, person('faculty@demo.paes', 'msantos', 'Maria', 'Santos', 'faculty', 28), juan, ana, mark]
    s.currentUserId = admin.id

    await importSeedDirect()

    s.announcements = [
      { id: id(), title: 'Welcome to PAES Learn!', body: 'Start with **PAES 101 – Agricultural Machinery Safety**. Finish every lesson and pass each quiz to earn your certificate.', pinned: true, created_at: daysAgo(10) },
      { id: id(), title: 'New course: Farm Power and Tractor Basics', body: 'Learn the main tractor systems and how to stay safe around a PTO.', pinned: false, created_at: daysAgo(3) },
    ]

    // Some history so dashboards and analytics have something to show.
    const lessonsOf = (code: string) => {
      const course = s.courses.find((c) => c.code === code)!
      const mods = s.modules.filter((m) => m.course_id === course.id).map((m) => m.id)
      return { lessons: s.lessons.filter((l) => mods.includes(l.module_id)), quizzes: s.quizzes.filter((q) => mods.includes(q.module_id)) }
    }
    const p101 = lessonsOf('PAES 101')
    const p102 = lessonsOf('PAES 102')
    const done = (u: Profile, ls: Lesson[], ago: number) => ls.forEach((l, i) => s.lesson_progress.push({ user_id: u.id, lesson_id: l.id, completed_at: daysAgo(ago - i * 0.3) }))
    const attempt = (u: Profile, q: Quiz, score: number, total: number, ago: number) => {
      const percent = Math.round((score * 100) / total)
      s.quiz_attempts.push({ id: id(), user_id: u.id, quiz_id: q.id, score, total, percent, passed: percent >= q.passing_percent, created_at: daysAgo(ago) })
    }
    done(juan, p101.lessons.slice(0, 3), 2)
    attempt(juan, p101.quizzes[0], 3, 5, 2)
    attempt(juan, p101.quizzes[0], 4, 5, 1)
    done(ana, p101.lessons, 8)
    const size = (q: Quiz) => Math.min(q.question_limit ?? Infinity, s.questions.filter((x) => x.quiz_id === q.id).length)
    p101.quizzes.forEach((q) => attempt(ana, q, size(q), size(q), 6))
    done(ana, p102.lessons.slice(0, 1), 4)
    done(mark, p101.lessons.slice(0, 1), 5)

    s.currentUserId = null
    commit()
  }

  // Seed through the public API, skipping `ready` (it is what we're building) and the fake delay.
  async function importSeedDirect() {
    const raw: Api = {
      ...api,
      saveCourse: async (c) => save(s.courses, c, DEFAULTS.course),
      saveModule: async (m) => save(s.modules, m, DEFAULTS.module),
      saveLesson: async (l) => save(s.lessons, l, DEFAULTS.lesson),
      saveQuiz: async (q) => save(s.quizzes, q, DEFAULTS.quiz),
      saveQuestion: async ({ choices, ...q }) => {
        const saved = save(s.questions, q, DEFAULTS.question)
        for (const c of choices) s.choices.push({ id: id(), question_id: saved.id, text: c.text ?? '', is_correct: !!c.is_correct })
      },
    }
    await importSeed(raw)
  }

  return api
}
