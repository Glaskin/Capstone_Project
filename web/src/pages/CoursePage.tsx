import type { DependencyList } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, Award, CheckCircle2, Circle, ClipboardCheck, FileText, PencilRuler } from 'lucide-react'
import { courseProgress, stepHref } from '../lib/progress'
import { isStaff, useAuth } from '../lib/hooks'
import { ButtonLink, Card, Empty, ErrorBox, ProgressRing, Spinner, color } from '../components/ui'
import { useLearning } from './Dashboard'

/** Loads the course from the URL plus the student's progress in it. */
export function useCourse(deps: DependencyList = []) {
  const { courseId } = useParams()
  const learning = useLearning(deps)
  const [courses, mine] = learning.data ?? [[], { lessons: [], attempts: [] }]
  const course = courses.find((c) => c.id === Number(courseId))
  return { ...learning, course, mine, progress: course ? courseProgress(course, mine.lessons, mine.attempts) : null }
}

export default function CoursePage() {
  const { course, progress: p, mine, loading, error, reload, data } = useCourse()
  const { profile } = useAuth()
  if (loading && !data) return <Spinner />
  if (error) return <ErrorBox message={error} onRetry={reload} />
  if (!course || !p) return <ErrorBox message="Course not found." />
  const col = color(course.color)
  const best = (quizId: number) => Math.max(-1, ...mine.attempts.filter((a) => a.quiz_id === quizId).map((a) => a.percent))

  return (
    <div className="space-y-8">
      <Link to="/courses" className="inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
        <ArrowLeft className="size-4" /> All courses
      </Link>

      <div className={`furrows relative overflow-hidden rounded-3xl ${col.bg} p-8 text-white shadow-lg`}>
        <div className="flex flex-wrap items-center justify-between gap-6">
          <div className="max-w-2xl">
            <div className="text-sm font-bold tracking-widest uppercase opacity-90">{course.code}</div>
            <h1 className="font-display mt-1 text-3xl font-bold sm:text-4xl">{course.title}</h1>
            <p className="mt-3 text-white/85">{course.description}</p>
            <div className="mt-6 flex flex-wrap gap-3">
              {p.next && (
                <ButtonLink to={stepHref(course.id, p.next)} className="bg-white !text-stone-900 hover:bg-stone-100">
                  {p.started ? 'Continue' : 'Start course'}
                </ButtonLink>
              )}
              {p.complete && (
                <ButtonLink to={`/courses/${course.id}/certificate`} className="bg-amber-400 !text-amber-950 hover:bg-amber-300">
                  <Award className="size-4" /> View certificate
                </ButtonLink>
              )}
              {isStaff(profile) && (
                <ButtonLink to={`/manage/courses/${course.id}`} className="bg-white/10 ring-1 ring-white/40 hover:bg-white/20">
                  <PencilRuler className="size-4" /> Edit course
                </ButtonLink>
              )}
            </div>
          </div>
          <div className="rounded-2xl bg-white p-3 text-stone-900">
            <ProgressRing value={p.percent} size={96} ring={col.ring} />
          </div>
        </div>
      </div>

      {course.modules.length === 0 && <Empty icon={<FileText className="size-8" />} title="No modules yet" />}

      <div className="space-y-5">
        {course.modules.map((m, i) => {
          const steps = p.steps.filter((s) => s.moduleTitle === m.title)
          const done = steps.filter((s) => s.done).length
          return (
            <Card key={m.id} className="overflow-hidden">
              <div className="flex items-start justify-between gap-4 border-b border-stone-100 p-5 dark:border-stone-800">
                <div>
                  <div className="text-xs font-semibold tracking-wider text-stone-400 uppercase">Module {i + 1}</div>
                  <h2 className="font-display text-lg font-bold">{m.title}</h2>
                  {m.description && <p className="text-sm text-stone-500">{m.description}</p>}
                </div>
                <span className="shrink-0 text-sm font-semibold text-stone-500">{done}/{steps.length}</span>
              </div>
              <ul>
                {m.lessons.map((l) => {
                  const isDone = mine.lessons.some((x) => x.lesson_id === l.id)
                  return (
                    <li key={`l${l.id}`}>
                      <Link to={stepHref(course.id, { kind: 'lesson', id: l.id })} className="flex items-center gap-3 px-5 py-3 hover:bg-stone-50 dark:hover:bg-stone-800/50">
                        {isDone ? <CheckCircle2 className="size-5 text-emerald-600" /> : <Circle className="size-5 text-stone-300" />}
                        <FileText className="size-4 text-stone-400" />
                        <span className="flex-1 font-medium">{l.title}</span>
                        <span className="text-xs text-stone-400">{l.minutes} min</span>
                      </Link>
                    </li>
                  )
                })}
                {m.quizzes.map((q) => {
                  const b = best(q.id)
                  const passed = mine.attempts.some((a) => a.quiz_id === q.id && a.passed)
                  return (
                    <li key={`q${q.id}`}>
                      <Link to={stepHref(course.id, { kind: 'quiz', id: q.id })} className="flex items-center gap-3 px-5 py-3 hover:bg-stone-50 dark:hover:bg-stone-800/50">
                        {passed ? <CheckCircle2 className="size-5 text-emerald-600" /> : <Circle className="size-5 text-stone-300" />}
                        <ClipboardCheck className={`size-4 ${col.text}`} />
                        <span className="flex-1 font-semibold">{q.title}</span>
                        <span className="text-xs text-stone-400">{b >= 0 ? `Best ${b}%` : `Pass ${q.passing_percent}%`}</span>
                      </Link>
                    </li>
                  )
                })}
              </ul>
            </Card>
          )
        })}
      </div>
    </div>
  )
}
