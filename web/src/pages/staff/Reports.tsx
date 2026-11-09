import { useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { Activity, BookOpen, Download, GraduationCap, Megaphone, PencilRuler, Target, Users } from 'lucide-react'
import { api } from '../../lib/api'
import { useAsync, useAuth } from '../../lib/hooks'
import { courseProgress } from '../../lib/progress'
import { ButtonLink, Card, Empty, ErrorBox, PageHeader, ProgressBar, Select, Spinner, fullName, timeAgo } from '../../components/ui'

function Stat({ icon, label, value, sub }: { icon: ReactNode; label: string; value: string | number; sub?: string }) {
  return (
    <Card className="p-5">
      <div className="flex items-center gap-2 text-sm text-stone-500">{icon}{label}</div>
      <div className="mt-2 text-3xl font-bold">{value}</div>
      {sub && <div className="text-xs text-stone-500">{sub}</div>}
    </Card>
  )
}

function downloadCsv(name: string, rows: (string | number)[][]) {
  const csv = rows.map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n')
  const a = document.createElement('a')
  a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  a.download = name
  a.click()
  URL.revokeObjectURL(a.href)
}

export default function Reports({ overview }: { overview?: boolean }) {
  const { profile } = useAuth()
  const data = useAsync(() => Promise.all([api.listCourseTrees(), api.listProfiles(), api.allProgress()]), [])
  const [courseFilter, setCourseFilter] = useState<number | 'all'>('all')

  if (data.loading && !data.data) return <Spinner />
  if (data.error) return <ErrorBox message={data.error} onRetry={data.reload} />
  const [courses, profiles, all] = data.data!
  const students = profiles.filter((p) => p.role === 'student')
  const weekAgo = Date.now() - 7 * 86400000
  const recentAttempts = all.attempts.filter((a) => new Date(a.created_at).getTime() > weekAgo)
  const activeIds = new Set([...recentAttempts.map((a) => a.user_id), ...all.lessons.filter((l) => new Date(l.completed_at).getTime() > weekAgo).map((l) => l.user_id)])
  const avg = all.attempts.length ? Math.round(all.attempts.reduce((s, a) => s + a.percent, 0) / all.attempts.length) : 0

  const quizRows = courses.flatMap((c) =>
    c.modules.flatMap((m) =>
      m.quizzes.map((q) => {
        const at = all.attempts.filter((a) => a.quiz_id === q.id)
        const takers = new Set(at.map((a) => a.user_id))
        const passers = new Set(at.filter((a) => a.passed).map((a) => a.user_id))
        return {
          course: c, quiz: q, attempts: at.length, students: takers.size,
          avg: at.length ? Math.round(at.reduce((s, a) => s + a.percent, 0) / at.length) : null,
          passRate: takers.size ? Math.round((passers.size / takers.size) * 100) : null,
        }
      }),
    ),
  )

  const shownCourses = courses.filter((c) => courseFilter === 'all' || c.id === courseFilter)
  const studentRows = students.map((s) => {
    const lessons = all.lessons.filter((l) => l.user_id === s.id)
    const attempts = all.attempts.filter((a) => a.user_id === s.id)
    const perCourse = shownCourses.map((c) => courseProgress(c, lessons, attempts).percent)
    const last = [...lessons.map((l) => l.completed_at), ...attempts.map((a) => a.created_at)].sort().at(-1)
    return { s, perCourse, last, overall: perCourse.length ? Math.round(perCourse.reduce((a, b) => a + b, 0) / perCourse.length) : 0 }
  }).sort((a, b) => b.overall - a.overall)

  const exportCsv = () =>
    downloadCsv('student-progress.csv', [
      ['Name', 'Username', 'Email', ...shownCourses.map((c) => `${c.code || c.title} %`), 'Last active'],
      ...studentRows.map((r) => [fullName(r.s), r.s.username, r.s.email, ...r.perCourse, r.last ?? '']),
    ])

  return (
    <div className="space-y-8">
      <PageHeader
        eyebrow={overview ? `Welcome back, ${profile?.first_name || profile?.username}` : 'Teaching'}
        title={overview ? 'Teaching overview' : 'Student reports'}
        subtitle="How your students are doing across every course."
        actions={overview && (
          <>
            <ButtonLink to="/manage" variant="secondary"><PencilRuler className="size-4" /> Manage courses</ButtonLink>
            <ButtonLink to="/announcements" variant="secondary"><Megaphone className="size-4" /> Post announcement</ButtonLink>
          </>
        )}
      />

      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <Stat icon={<Users className="size-4" />} label="Students" value={students.length} sub={`${activeIds.size} active this week`} />
        <Stat icon={<BookOpen className="size-4" />} label="Courses" value={courses.length} sub={`${courses.filter((c) => c.published).length} published`} />
        <Stat icon={<Activity className="size-4" />} label="Quiz attempts" value={all.attempts.length} sub={`${recentAttempts.length} this week`} />
        <Stat icon={<Target className="size-4" />} label="Average score" value={all.attempts.length ? `${avg}%` : '—'} />
      </div>

      <section>
        <h2 className="font-display mb-4 text-xl font-bold">Quiz performance</h2>
        {quizRows.length === 0 ? <Empty icon={<Target className="size-8" />} title="No quizzes yet" /> : (
          <Card className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-stone-50 text-left text-xs text-stone-500 uppercase dark:bg-stone-800/50">
                <tr><th className="px-4 py-2">Quiz</th><th className="px-3 py-2">Students</th><th className="px-3 py-2">Attempts</th><th className="px-3 py-2">Avg score</th><th className="w-48 px-4 py-2">Pass rate</th></tr>
              </thead>
              <tbody className="divide-y divide-stone-100 dark:divide-stone-800">
                {quizRows.map((r) => (
                  <tr key={r.quiz.id}>
                    <td className="px-4 py-2.5">
                      <Link to={`/manage/quizzes/${r.quiz.id}`} className="font-medium hover:underline">{r.quiz.title}</Link>
                      <div className="text-xs text-stone-500">{r.course.code || r.course.title}</div>
                    </td>
                    <td className="px-3 py-2.5 tabular-nums">{r.students}</td>
                    <td className="px-3 py-2.5 tabular-nums">{r.attempts}</td>
                    <td className="px-3 py-2.5 tabular-nums">{r.avg != null ? `${r.avg}%` : '—'}</td>
                    <td className="px-4 py-2.5">
                      {r.passRate != null ? (
                        <div className="flex items-center gap-2"><ProgressBar value={r.passRate} className="flex-1" /><span className="w-10 text-right tabular-nums">{r.passRate}%</span></div>
                      ) : <span className="text-stone-400">No attempts</span>}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Card>
        )}
      </section>

      <section>
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <h2 className="font-display text-xl font-bold">Student progress</h2>
          <div className="flex gap-2">
            <Select aria-label="Filter by course" value={courseFilter} onChange={(e) => setCourseFilter(e.target.value === 'all' ? 'all' : Number(e.target.value))} className="w-56">
              <option value="all">All courses</option>
              {courses.map((c) => <option key={c.id} value={c.id}>{c.code || c.title}</option>)}
            </Select>
            <button onClick={exportCsv} className="inline-flex items-center gap-2 rounded-xl px-3 text-sm font-semibold ring-1 ring-stone-300 hover:bg-stone-100 dark:ring-stone-700 dark:hover:bg-stone-800">
              <Download className="size-4" /> CSV
            </button>
          </div>
        </div>
        {studentRows.length === 0 ? <Empty icon={<GraduationCap className="size-8" />} title="No students yet">Students appear here once they register.</Empty> : (
          <Card className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-stone-50 text-left text-xs text-stone-500 uppercase dark:bg-stone-800/50">
                <tr>
                  <th className="px-4 py-2">Student</th>
                  {shownCourses.map((c) => <th key={c.id} className="px-3 py-2 whitespace-nowrap">{c.code || c.title}</th>)}
                  <th className="px-4 py-2 text-right">Last active</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-stone-100 dark:divide-stone-800">
                {studentRows.map(({ s, perCourse, last }) => (
                  <tr key={s.id}>
                    <td className="px-4 py-2.5">
                      <div className="font-medium">{fullName(s)}</div>
                      <div className="text-xs text-stone-500">@{s.username}</div>
                    </td>
                    {perCourse.map((p, i) => (
                      <td key={i} className="px-3 py-2.5">
                        <div className="flex w-28 items-center gap-2"><ProgressBar value={p} className="flex-1" /><span className="w-9 text-right text-xs tabular-nums">{p}%</span></div>
                      </td>
                    ))}
                    <td className="px-4 py-2.5 text-right text-stone-500">{last ? timeAgo(last) : 'Never'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Card>
        )}
      </section>
    </div>
  )
}
