import type { CourseTree, LessonProgress, QuizAttempt } from './types'

const byPos = <T extends { position: number; id: number }>(a: T, b: T) => a.position - b.position || a.id - b.id

/** Sorts courses, modules, lessons and quizzes by their `position` (then id). */
export function sortTree(courses: CourseTree[]): CourseTree[] {
  return [...courses].sort(byPos).map((c) => ({
    ...c,
    modules: [...c.modules].sort(byPos).map((m) => ({
      ...m,
      lessons: [...m.lessons].sort(byPos),
      quizzes: [...m.quizzes].sort(byPos),
    })),
  }))
}

export type Step = { kind: 'lesson' | 'quiz'; id: number; title: string; moduleTitle: string; done: boolean }

/** A course as an ordered list of steps (each module's lessons, then its quizzes). */
export function courseSteps(course: CourseTree, lessons: LessonProgress[], attempts: QuizAttempt[]): Step[] {
  const doneLessons = new Set(lessons.map((p) => p.lesson_id))
  const passedQuizzes = new Set(attempts.filter((a) => a.passed).map((a) => a.quiz_id))
  return course.modules.flatMap((m) => [
    ...m.lessons.map((l) => ({ kind: 'lesson' as const, id: l.id, title: l.title, moduleTitle: m.title, done: doneLessons.has(l.id) })),
    ...m.quizzes.map((q) => ({ kind: 'quiz' as const, id: q.id, title: q.title, moduleTitle: m.title, done: passedQuizzes.has(q.id) })),
  ])
}

export function courseProgress(course: CourseTree, lessons: LessonProgress[], attempts: QuizAttempt[]) {
  const steps = courseSteps(course, lessons, attempts)
  const done = steps.filter((s) => s.done).length
  return {
    steps,
    done,
    total: steps.length,
    percent: steps.length ? Math.round((done / steps.length) * 100) : 0,
    complete: steps.length > 0 && done === steps.length,
    next: steps.find((s) => !s.done) ?? null,
    started: done > 0,
  }
}

/** Number of consecutive days (ending today or yesterday) with any learning activity. */
export function streakDays(lessons: LessonProgress[], attempts: QuizAttempt[], now = new Date()): number {
  const day = (d: Date | string) => new Date(d).toLocaleDateString('en-CA')
  const days = new Set([...lessons.map((l) => day(l.completed_at)), ...attempts.map((a) => day(a.created_at))])
  const cursor = new Date(now)
  if (!days.has(day(cursor))) cursor.setDate(cursor.getDate() - 1)
  let n = 0
  while (days.has(day(cursor))) {
    n++
    cursor.setDate(cursor.getDate() - 1)
  }
  return n
}

export const stepHref = (courseId: number, s: Pick<Step, 'kind' | 'id'>) =>
  `/courses/${courseId}/${s.kind === 'lesson' ? 'lessons' : 'quizzes'}/${s.id}`
