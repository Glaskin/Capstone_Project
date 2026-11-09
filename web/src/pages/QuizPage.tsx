import { useEffect, useRef, useState, type ReactNode } from 'react'
import { Link, useParams } from 'react-router-dom'
import confetti from 'canvas-confetti'
import { ArrowLeft, ArrowRight, CheckCircle2, ClipboardCheck, Clock, History, RotateCcw, Target, Timer, XCircle } from 'lucide-react'
import { api } from '../lib/api'
import { stepHref } from '../lib/progress'
import { useAction } from '../lib/hooks'
import type { QuizPaper, QuizResult } from '../lib/types'
import { Button, ButtonLink, Card, ErrorBox, ProgressBar, ProgressRing, Spinner, timeAgo } from '../components/ui'
import { CourseOutline } from './LessonPage'
import { useCourse } from './CoursePage'

type Answers = Record<number, number | null>

export default function QuizPage() {
  const params = useParams()
  const courseId = Number(params.courseId)
  const quizId = Number(params.quizId)
  const { course, progress, mine, loading, error, reload, data } = useCourse([quizId])
  const [paper, setPaper] = useState<QuizPaper | null>(null)
  const [answers, setAnswers] = useState<Answers>({})
  const [current, setCurrent] = useState(0)
  const [result, setResult] = useState<QuizResult | null>(null)
  const [deadline, setDeadline] = useState<number | null>(null)
  const { busy, run } = useAction()

  // Reset when navigating between quizzes.
  useEffect(() => {
    setPaper(null)
    setResult(null)
    setAnswers({})
    setCurrent(0)
    setDeadline(null)
  }, [quizId])

  if (loading && !data) return <Spinner />
  if (error) return <ErrorBox message={error} onRetry={reload} />
  const quiz = course?.modules.flatMap((m) => m.quizzes).find((q) => q.id === quizId)
  if (!course || !quiz) return <ErrorBox message="Quiz not found." />

  const attempts = mine.attempts.filter((a) => a.quiz_id === quizId)
  const best = attempts.length ? Math.max(...attempts.map((a) => a.percent)) : null
  const steps = progress?.steps ?? []
  const idx = steps.findIndex((s) => s.kind === 'quiz' && s.id === quizId)
  const next = idx >= 0 && idx < steps.length - 1 ? steps[idx + 1] : null

  const start = () =>
    run(async () => {
      const p = await api.startQuiz(quizId)
      if (!p.questions.length) throw new Error('This quiz has no questions yet.')
      setPaper(p)
      setAnswers(Object.fromEntries(p.questions.map((q) => [q.id, null])))
      setCurrent(0)
      setResult(null)
      setDeadline(p.quiz.time_limit_minutes ? Date.now() + p.quiz.time_limit_minutes * 60_000 : null)
    })

  const submit = (final: Answers) =>
    run(async () => {
      const r = await api.submitQuiz(quizId, final)
      setResult(r)
      setDeadline(null)
      reload()
      if (r.passed) confetti({ particleCount: 160, spread: 80, origin: { y: 0.6 }, colors: ['#047857', '#10b981', '#fbbf24', '#fde68a'] })
    })

  const body = (() => {
    if (result) return <Results result={result} quizTitle={quiz.title} passing={quiz.passing_percent} onRetry={start} busy={busy} nextHref={next ? stepHref(courseId, next) : `/courses/${courseId}`} />
    if (paper) return <Taking paper={paper} answers={answers} setAnswers={setAnswers} current={current} setCurrent={setCurrent} deadline={deadline} onSubmit={submit} busy={busy} />
    return (
      <Card className="p-8">
        <div className="mb-6 grid size-14 place-items-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
          <ClipboardCheck className="size-7" />
        </div>
        <h1 className="font-display text-3xl font-bold">{quiz.title}</h1>
        {quiz.description && <p className="mt-2 text-stone-500">{quiz.description}</p>}
        <div className="mt-6 grid gap-3 sm:grid-cols-3">
          <Info icon={<Target className="size-4" />} label="To pass" value={`${quiz.passing_percent}%`} />
          <Info icon={<Timer className="size-4" />} label="Time limit" value={quiz.time_limit_minutes ? `${quiz.time_limit_minutes} min` : 'None'} />
          <Info icon={<History className="size-4" />} label="Best score" value={best != null ? `${best}%` : '—'} />
        </div>
        <p className="mt-6 text-sm text-stone-500">
          {quiz.question_limit ? `You'll get ${quiz.question_limit} randomly chosen questions.` : 'You’ll answer every question in this quiz.'} You can retake it as many times as you like; your best score counts.
        </p>
        <Button className="mt-6 px-6 py-3 text-base" busy={busy} onClick={start}>
          {attempts.length ? 'Retake quiz' : 'Start quiz'} <ArrowRight className="size-4" />
        </Button>

        {attempts.length > 0 && (
          <div className="mt-8 border-t border-stone-100 pt-6 dark:border-stone-800">
            <h2 className="mb-3 text-sm font-semibold text-stone-500">Your attempts</h2>
            <ul className="space-y-2 text-sm">
              {attempts.slice(0, 5).map((a) => (
                <li key={a.id} className="flex items-center justify-between">
                  <span className="text-stone-500">{timeAgo(a.created_at)}</span>
                  <span className={`font-semibold ${a.passed ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-600'}`}>
                    {a.score}/{a.total} ({a.percent}%) {a.passed ? '· Passed' : ''}
                  </span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </Card>
    )
  })()

  return (
    <div className="grid gap-8 lg:grid-cols-[1fr_18rem]">
      <div className="min-w-0">
        <Link to={`/courses/${courseId}`} className="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
          <ArrowLeft className="size-4" /> {course.title}
        </Link>
        {body}
      </div>
      <aside className="hidden lg:block">
        <CourseOutline course={course} progress={progress} current={{ kind: 'quiz', id: quizId }} />
      </aside>
    </div>
  )
}

function Info({ icon, label, value }: { icon: ReactNode; label: string; value: string }) {
  return (
    <div className="rounded-xl bg-stone-100 p-3 dark:bg-stone-800/60">
      <div className="flex items-center gap-1.5 text-xs text-stone-500">{icon}{label}</div>
      <div className="mt-0.5 text-lg font-bold">{value}</div>
    </div>
  )
}

function Countdown({ deadline, onExpire }: { deadline: number; onExpire: () => void }) {
  const [now, setNow] = useState(Date.now())
  const fired = useRef(false)
  useEffect(() => {
    const t = setInterval(() => setNow(Date.now()), 500)
    return () => clearInterval(t)
  }, [])
  const left = Math.max(0, deadline - now)
  useEffect(() => {
    if (left === 0 && !fired.current) {
      fired.current = true
      onExpire()
    }
  }, [left, onExpire])
  const m = Math.floor(left / 60000)
  const s = Math.floor((left % 60000) / 1000)
  return (
    <span className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold tabular-nums ${left < 60000 ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-stone-100 dark:bg-stone-800'}`}>
      <Clock className="size-4" /> {m}:{String(s).padStart(2, '0')}
    </span>
  )
}

function Taking({ paper, answers, setAnswers, current, setCurrent, deadline, onSubmit, busy }: {
  paper: QuizPaper; answers: Answers; setAnswers: (a: Answers) => void; current: number; setCurrent: (n: number) => void
  deadline: number | null; onSubmit: (a: Answers) => void; busy: boolean
}) {
  const q = paper.questions[current]
  const answered = Object.values(answers).filter((v) => v != null).length
  const total = paper.questions.length
  const last = current === total - 1
  const latest = useRef(answers)
  latest.current = answers

  return (
    <Card className="p-6 sm:p-8">
      <div className="mb-6 flex items-center justify-between gap-4">
        <div className="text-sm font-semibold text-stone-500">Question {current + 1} of {total}</div>
        {deadline && <Countdown deadline={deadline} onExpire={() => onSubmit(latest.current)} />}
      </div>
      <ProgressBar value={Math.round((answered / total) * 100)} className="mb-8" />

      <h2 className="font-display text-2xl leading-snug font-bold">{q.prompt}</h2>
      <div className="mt-6 space-y-3" role="radiogroup" aria-label={q.prompt}>
        {q.choices.map((c, i) => {
          const selected = answers[q.id] === c.id
          return (
            <button
              key={c.id}
              role="radio"
              aria-checked={selected}
              onClick={() => setAnswers({ ...answers, [q.id]: c.id })}
              className={`flex w-full items-center gap-4 rounded-2xl p-4 text-left font-medium ring-2 transition ${
                selected ? 'bg-emerald-50 ring-emerald-600 dark:bg-emerald-950/40' : 'ring-stone-200 hover:ring-stone-300 dark:ring-stone-700 dark:hover:ring-stone-600'
              }`}
            >
              <span className={`grid size-8 shrink-0 place-items-center rounded-lg text-sm font-bold ${selected ? 'bg-emerald-700 text-white' : 'bg-stone-100 dark:bg-stone-800'}`}>
                {String.fromCharCode(65 + i)}
              </span>
              {c.text}
            </button>
          )
        })}
      </div>

      <div className="mt-8 flex flex-wrap items-center justify-between gap-3">
        <Button variant="ghost" onClick={() => setCurrent(current - 1)} disabled={current === 0}>
          <ArrowLeft className="size-4" /> Back
        </Button>
        <div className="flex flex-wrap justify-center gap-1.5">
          {paper.questions.map((x, i) => (
            <button
              key={x.id}
              onClick={() => setCurrent(i)}
              aria-label={`Go to question ${i + 1}`}
              className={`size-3 rounded-full ${i === current ? 'bg-emerald-700 ring-2 ring-emerald-300' : answers[x.id] != null ? 'bg-emerald-500' : 'bg-stone-300 dark:bg-stone-700'}`}
            />
          ))}
        </div>
        {last ? (
          <Button busy={busy} onClick={() => (answered < total && !confirm(`You have ${total - answered} unanswered question(s). Submit anyway?`) ? null : onSubmit(answers))}>
            Submit answers
          </Button>
        ) : (
          <Button onClick={() => setCurrent(current + 1)}>
            Next <ArrowRight className="size-4" />
          </Button>
        )}
      </div>
    </Card>
  )
}

function Results({ result, quizTitle, passing, onRetry, busy, nextHref }: { result: QuizResult; quizTitle: string; passing: number; onRetry: () => void; busy: boolean; nextHref: string }) {
  return (
    <div className="space-y-6">
      <Card className="flex flex-wrap items-center gap-6 p-8">
        <ProgressRing value={result.percent} size={120} ring={result.passed ? 'stroke-emerald-600' : 'stroke-red-500'} />
        <div className="flex-1">
          <p className="text-sm font-semibold text-stone-500">{quizTitle}</p>
          <h1 className="font-display text-3xl font-bold">{result.passed ? 'You passed! 🎉' : 'Not quite yet'}</h1>
          <p className="mt-1 text-stone-500">
            You got <b>{result.score}</b> of <b>{result.total}</b> correct. {result.passed ? 'Great work.' : `You need ${passing}% to pass — review the explanations and try again.`}
          </p>
          <div className="mt-4 flex flex-wrap gap-2">
            <Button variant="secondary" busy={busy} onClick={onRetry}><RotateCcw className="size-4" /> Try again</Button>
            <ButtonLink to={nextHref}>Continue <ArrowRight className="size-4" /></ButtonLink>
          </div>
        </div>
      </Card>

      <h2 className="font-display text-xl font-bold">Review</h2>
      {result.review.map((r, i) => {
        const right = r.chosenId != null && r.chosenId === r.correctId
        return (
          <Card key={r.questionId} className="p-6">
            <div className="flex gap-3">
              {right ? <CheckCircle2 className="size-6 shrink-0 text-emerald-600" /> : <XCircle className="size-6 shrink-0 text-red-500" />}
              <div className="flex-1">
                <p className="font-semibold">{i + 1}. {r.prompt}</p>
                <ul className="mt-3 space-y-1.5 text-sm">
                  {r.choices.map((c) => (
                    <li
                      key={c.id}
                      className={`rounded-lg px-3 py-2 ${
                        c.id === r.correctId ? 'bg-emerald-50 font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                          : c.id === r.chosenId ? 'bg-red-50 text-red-700 line-through dark:bg-red-950/40 dark:text-red-300' : 'text-stone-500'
                      }`}
                    >
                      {c.text}
                      {c.id === r.chosenId && <span className="ml-2 text-xs no-underline">(your answer)</span>}
                    </li>
                  ))}
                </ul>
                {r.chosenId == null && <p className="mt-2 text-sm text-red-600">Not answered.</p>}
                {r.explanation && <p className="mt-3 rounded-lg bg-stone-100 px-3 py-2 text-sm dark:bg-stone-800/60">💡 {r.explanation}</p>}
              </div>
            </div>
          </Card>
        )
      })}
    </div>
  )
}
