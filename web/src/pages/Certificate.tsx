import { Link } from 'react-router-dom'
import { ArrowLeft, Award, Lock, Printer } from 'lucide-react'
import { useAuth } from '../lib/hooks'
import { Button, ButtonLink, Card, ErrorBox, Spinner, fullName } from '../components/ui'
import { useCourse } from './CoursePage'

export default function Certificate() {
  const { course, progress, mine, loading, error, reload, data } = useCourse()
  const { profile } = useAuth()
  if (loading && !data) return <Spinner />
  if (error) return <ErrorBox message={error} onRetry={reload} />
  if (!course || !progress || !profile) return <ErrorBox message="Course not found." />

  if (!progress.complete)
    return (
      <Card className="mx-auto max-w-lg p-10 text-center">
        <Lock className="mx-auto size-10 text-stone-400" />
        <h1 className="font-display mt-4 text-2xl font-bold">Certificate locked</h1>
        <p className="mt-2 text-stone-500">Finish every lesson and pass every quiz in {course.title} to unlock it. You’re {progress.percent}% there.</p>
        <ButtonLink to={`/courses/${course.id}`} className="mt-6">Back to course</ButtonLink>
      </Card>
    )

  // Completion date = the latest lesson/passed-quiz in this course.
  const ids = new Set(progress.steps.map((s) => `${s.kind}${s.id}`))
  const dates = [
    ...mine.lessons.filter((l) => ids.has(`lesson${l.lesson_id}`)).map((l) => l.completed_at),
    ...mine.attempts.filter((a) => a.passed && ids.has(`quiz${a.quiz_id}`)).map((a) => a.created_at),
  ].sort()
  const completed = new Date(dates.at(-1) ?? Date.now())
  const quizScores = mine.attempts.filter((a) => ids.has(`quiz${a.quiz_id}`))
  const best = new Map<number, number>()
  quizScores.forEach((a) => best.set(a.quiz_id, Math.max(best.get(a.quiz_id) ?? 0, a.percent)))
  const avg = best.size ? Math.round([...best.values()].reduce((s, x) => s + x, 0) / best.size) : null
  const certId = `PAES-${course.id}-${profile.id.slice(0, 6).toUpperCase()}`

  return (
    <div className="space-y-6">
      <div className="no-print flex flex-wrap items-center justify-between gap-3">
        <Link to={`/courses/${course.id}`} className="inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
          <ArrowLeft className="size-4" /> {course.title}
        </Link>
        <Button onClick={() => window.print()}><Printer className="size-4" /> Print / Save as PDF</Button>
      </div>

      <div className="mx-auto max-w-4xl bg-white p-3 text-stone-900 shadow-2xl print:shadow-none">
        <div className="border-[6px] border-double border-emerald-800 p-10 text-center sm:p-16">
          <Award className="mx-auto size-16 text-amber-500" />
          <p className="mt-4 text-sm font-semibold tracking-[0.3em] text-emerald-800 uppercase">Certificate of Completion</p>
          <p className="mt-8 text-stone-500">This certifies that</p>
          <p className="font-display mt-2 text-4xl font-bold sm:text-5xl">{fullName(profile)}</p>
          <p className="mt-6 text-stone-500">has successfully completed</p>
          <p className="font-display mt-2 text-2xl font-bold text-emerald-800">{course.code} — {course.title}</p>
          {avg != null && <p className="mt-3 text-sm text-stone-500">with an average best quiz score of <b>{avg}%</b></p>}
          <div className="mt-12 grid grid-cols-2 gap-8 text-sm">
            <div>
              <div className="border-t border-stone-400 pt-2 font-semibold">{completed.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })}</div>
              <div className="text-stone-500">Date completed</div>
            </div>
            <div>
              <div className="border-t border-stone-400 pt-2 font-mono font-semibold">{certId}</div>
              <div className="text-stone-500">Certificate ID</div>
            </div>
          </div>
          <p className="mt-10 font-display text-lg font-bold">PAES <span className="text-emerald-700">Learn</span></p>
        </div>
      </div>
    </div>
  )
}
