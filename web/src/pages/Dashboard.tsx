import type { DependencyList, ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, Award, BookOpen, CheckCircle2, Flame, Megaphone, Pin, Target } from 'lucide-react'
import { api } from '../lib/api'
import { useAsync, useAuth } from '../lib/hooks'
import { courseProgress, stepHref, streakDays } from '../lib/progress'
import { ButtonLink, Card, Empty, ErrorBox, Markdown, ProgressRing, Spinner, color, timeAgo } from '../components/ui'

export function useLearning(deps: DependencyList = []) {
  return useAsync(() => Promise.all([api.listCourseTrees(), api.myProgress()]), deps)
}

function Stat({ icon, label, value, tone }: { icon: ReactNode; label: string; value: string | number; tone: string }) {
  return (
    <Card className="flex items-center gap-4 p-5">
      <div className={`grid size-11 place-items-center rounded-xl ${tone}`}>{icon}</div>
      <div>
        <div className="text-2xl font-bold">{value}</div>
        <div className="text-sm text-stone-500">{label}</div>
      </div>
    </Card>
  )
}

export default function Dashboard() {
  const { profile } = useAuth()
  const learning = useLearning()
  const news = useAsync(() => api.listAnnouncements(), [])

  if (learning.loading && !learning.data) return <Spinner />
  if (learning.error) return <ErrorBox message={learning.error} onRetry={learning.reload} />
  const [courses, mine] = learning.data!

  const withProgress = courses.map((c) => ({ course: c, p: courseProgress(c, mine.lessons, mine.attempts) }))
  const inProgress = withProgress.filter((x) => x.p.started && !x.p.complete)
  const continueWith = inProgress[0] ?? withProgress.find((x) => !x.p.complete && x.p.total > 0)
  const passed = new Set(mine.attempts.filter((a) => a.passed).map((a) => a.quiz_id)).size
  const avg = mine.attempts.length ? Math.round(mine.attempts.reduce((s, a) => s + a.percent, 0) / mine.attempts.length) : 0
  const streak = streakDays(mine.lessons, mine.attempts)
  const hour = new Date().getHours()
  const greeting = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening'

  return (
    <div className="space-y-8">
      <div>
        <p className="text-sm font-medium text-stone-500">{new Date().toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' })}</p>
        <h1 className="font-display text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
          {greeting}, {profile?.first_name || profile?.username}!
        </h1>
      </div>

      {continueWith?.p.next && (
        <Link
          to={stepHref(continueWith.course.id, continueWith.p.next)}
          className="furrows group flex flex-wrap items-center gap-6 rounded-3xl bg-linear-to-br from-emerald-800 to-emerald-600 p-6 text-white shadow-lg transition hover:shadow-xl sm:p-8"
        >
          <div className="flex-1">
            <p className="text-sm font-semibold text-emerald-100">{continueWith.p.started ? 'Continue where you left off' : 'Start learning'}</p>
            <h2 className="font-display mt-1 text-2xl font-bold">{continueWith.p.next.title}</h2>
            <p className="mt-1 text-emerald-100">
              {continueWith.course.code} · {continueWith.p.next.moduleTitle}
            </p>
          </div>
          <div className="flex items-center gap-4">
            <div className="text-right">
              <div className="text-3xl font-bold">{continueWith.p.percent}%</div>
              <div className="text-xs text-emerald-100">of {continueWith.course.title}</div>
            </div>
            <div className="grid size-12 place-items-center rounded-full bg-white text-emerald-800 transition group-hover:translate-x-1">
              <ArrowRight className="size-5" />
            </div>
          </div>
        </Link>
      )}

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <Stat icon={<Flame className="size-5" />} tone="bg-orange-100 text-orange-600 dark:bg-orange-950/50" label="Day streak" value={streak} />
        <Stat icon={<CheckCircle2 className="size-5" />} tone="bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50" label="Lessons done" value={mine.lessons.length} />
        <Stat icon={<Target className="size-5" />} tone="bg-sky-100 text-sky-700 dark:bg-sky-950/50" label="Quizzes passed" value={passed} />
        <Stat icon={<Award className="size-5" />} tone="bg-amber-100 text-amber-700 dark:bg-amber-950/50" label="Average score" value={mine.attempts.length ? `${avg}%` : '—'} />
      </div>

      <div className="grid gap-8 lg:grid-cols-3">
        <section className="lg:col-span-2">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="font-display text-xl font-bold">Your courses</h2>
            <Link to="/courses" className="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Browse all</Link>
          </div>
          {withProgress.length === 0 ? (
            <Empty icon={<BookOpen className="size-8" />} title="No courses yet">Your instructors haven’t published any courses.</Empty>
          ) : (
            <div className="space-y-3">
              {withProgress.map(({ course, p }) => (
                <Link key={course.id} to={`/courses/${course.id}`} className="block">
                  <Card className="flex items-center gap-4 p-4 transition hover:ring-emerald-400">
                    <ProgressRing value={p.percent} ring={color(course.color).ring} />
                    <div className="min-w-0 flex-1">
                      <div className={`text-xs font-bold tracking-wider uppercase ${color(course.color).text}`}>{course.code}</div>
                      <div className="truncate font-semibold">{course.title}</div>
                      <div className="text-sm text-stone-500">
                        {p.complete ? 'Completed — certificate ready' : `${p.done} of ${p.total} steps`}
                      </div>
                    </div>
                    {p.complete ? <Award className="size-6 text-amber-500" /> : <ArrowRight className="size-5 text-stone-400" />}
                  </Card>
                </Link>
              ))}
            </div>
          )}

          {mine.attempts.length > 0 && (
            <>
              <h2 className="font-display mt-8 mb-4 text-xl font-bold">Recent quiz results</h2>
              <Card className="divide-y divide-stone-100 dark:divide-stone-800">
                {mine.attempts.slice(0, 5).map((a) => {
                  const quiz = courses.flatMap((c) => c.modules.flatMap((m) => m.quizzes.map((q) => ({ q, c })))).find((x) => x.q.id === a.quiz_id)
                  return (
                    <div key={a.id} className="flex items-center justify-between gap-4 px-4 py-3 text-sm">
                      <div className="min-w-0">
                        <div className="truncate font-medium">{quiz?.q.title ?? 'Quiz'}</div>
                        <div className="text-stone-500">{timeAgo(a.created_at)}</div>
                      </div>
                      <span className={`rounded-full px-2.5 py-0.5 text-xs font-bold ${a.passed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'}`}>
                        {a.score}/{a.total} · {a.percent}%
                      </span>
                    </div>
                  )
                })}
              </Card>
            </>
          )}
        </section>

        <section>
          <div className="mb-4 flex items-center justify-between">
            <h2 className="font-display text-xl font-bold">Announcements</h2>
            <Link to="/announcements" className="text-sm font-semibold text-emerald-700 dark:text-emerald-400">All</Link>
          </div>
          {news.data?.length ? (
            <div className="space-y-3">
              {news.data.slice(0, 3).map((a) => (
                <Card key={a.id} className="p-4">
                  <div className="flex items-center gap-2 font-semibold">
                    {a.pinned && <Pin className="size-3.5 text-amber-500" />}
                    {a.title}
                  </div>
                  <div className="mb-2 text-xs text-stone-500">{timeAgo(a.created_at)}</div>
                  <Markdown source={a.body} className="prose-sm line-clamp-4" />
                </Card>
              ))}
            </div>
          ) : (
            <Empty icon={<Megaphone className="size-8" />} title="Nothing new" />
          )}
          <ButtonLink to="/courses" variant="secondary" className="mt-6 w-full">
            <BookOpen className="size-4" /> Explore courses
          </ButtonLink>
        </section>
      </div>
    </div>
  )
}
