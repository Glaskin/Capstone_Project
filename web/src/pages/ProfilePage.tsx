import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Award, KeyRound, Save } from 'lucide-react'
import { api } from '../lib/api'
import { useAction, useAuth } from '../lib/hooks'
import { courseProgress } from '../lib/progress'
import { Badge, Button, Card, ErrorBox, Field, Input, PageHeader, Spinner, fullName, initials, timeAgo } from '../components/ui'
import { useLearning } from './Dashboard'

export default function ProfilePage() {
  const { profile, refresh } = useAuth()
  const learning = useLearning()
  const [f, setF] = useState({ first_name: profile?.first_name ?? '', last_name: profile?.last_name ?? '', username: profile?.username ?? '' })
  const [pw, setPw] = useState({ password: '', confirm: '' })
  const { busy, run } = useAction()
  if (!profile) return null

  const saveProfile = () =>
    run(async () => {
      if (!/^[\w.-]{3,30}$/.test(f.username)) throw new Error('Username: 3–30 letters, numbers, dots, dashes or underscores.')
      await api.updateProfile({ first_name: f.first_name.trim(), last_name: f.last_name.trim(), username: f.username.trim() })
      await refresh()
    }, 'Profile saved')

  const savePassword = () =>
    run(async () => {
      if (pw.password.length < 8) throw new Error('Password must be at least 8 characters.')
      if (pw.password !== pw.confirm) throw new Error('Passwords do not match.')
      await api.updatePassword(pw.password)
      setPw({ password: '', confirm: '' })
    }, 'Password changed')

  return (
    <>
      <PageHeader title="My profile" />
      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6">
          <Card className="p-6 text-center">
            <div className="mx-auto grid size-20 place-items-center rounded-full bg-linear-to-br from-emerald-600 to-lime-600 text-2xl font-bold text-white">{initials(profile)}</div>
            <h2 className="font-display mt-4 text-xl font-bold">{fullName(profile)}</h2>
            <p className="text-sm text-stone-500">@{profile.username} · {profile.email}</p>
            <div className="mt-3"><Badge tone={profile.role === 'student' ? 'sky' : 'green'}>{profile.role}</Badge></div>
            <p className="mt-3 text-xs text-stone-400">Joined {timeAgo(profile.created_at)}</p>
          </Card>

          <Card className="space-y-4 p-6">
            <h2 className="font-semibold">Details</h2>
            <Field label="First name">{(id) => <Input id={id} value={f.first_name} onChange={(e) => setF({ ...f, first_name: e.target.value })} />}</Field>
            <Field label="Last name">{(id) => <Input id={id} value={f.last_name} onChange={(e) => setF({ ...f, last_name: e.target.value })} />}</Field>
            <Field label="Username">{(id) => <Input id={id} value={f.username} onChange={(e) => setF({ ...f, username: e.target.value })} />}</Field>
            <Button busy={busy} onClick={saveProfile} className="w-full"><Save className="size-4" /> Save details</Button>
          </Card>

          <Card className="space-y-4 p-6">
            <h2 className="flex items-center gap-2 font-semibold"><KeyRound className="size-4" /> Change password</h2>
            {api.mode === 'demo' && <p className="text-xs text-stone-500">Demo accounts don’t use passwords.</p>}
            <Field label="New password">{(id) => <Input id={id} type="password" autoComplete="new-password" value={pw.password} onChange={(e) => setPw({ ...pw, password: e.target.value })} />}</Field>
            <Field label="Confirm">{(id) => <Input id={id} type="password" autoComplete="new-password" value={pw.confirm} onChange={(e) => setPw({ ...pw, confirm: e.target.value })} />}</Field>
            <Button variant="secondary" busy={busy} onClick={savePassword} className="w-full">Update password</Button>
          </Card>
        </div>

        <div className="space-y-6 lg:col-span-2">
          {learning.loading && !learning.data ? <Spinner /> : learning.error ? <ErrorBox message={learning.error} /> : (() => {
            const [courses, mine] = learning.data!
            const quizzes = new Map(courses.flatMap((c) => c.modules.flatMap((m) => m.quizzes.map((q) => [q.id, { q, c }] as const))))
            return (
              <>
                <Card className="p-6">
                  <h2 className="mb-4 font-semibold">Course progress</h2>
                  <div className="space-y-4">
                    {courses.map((c) => {
                      const p = courseProgress(c, mine.lessons, mine.attempts)
                      return (
                        <div key={c.id} className="flex items-center gap-4">
                          <Link to={`/courses/${c.id}`} className="min-w-0 flex-1">
                            <div className="truncate font-medium hover:underline">{c.code} · {c.title}</div>
                            <div className="mt-1 h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800">
                              <div className="h-full rounded-full bg-emerald-600" style={{ width: `${p.percent}%` }} />
                            </div>
                          </Link>
                          <span className="w-12 text-right text-sm font-semibold">{p.percent}%</span>
                          {p.complete ? (
                            <Link to={`/courses/${c.id}/certificate`} title="View certificate"><Award className="size-5 text-amber-500" /></Link>
                          ) : <span className="size-5" />}
                        </div>
                      )
                    })}
                  </div>
                </Card>
                <Card className="overflow-hidden">
                  <h2 className="p-6 pb-3 font-semibold">Quiz history</h2>
                  {mine.attempts.length === 0 ? <p className="px-6 pb-6 text-sm text-stone-500">No attempts yet.</p> : (
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm">
                        <thead className="bg-stone-50 text-left text-xs text-stone-500 uppercase dark:bg-stone-800/50">
                          <tr><th className="px-6 py-2">Quiz</th><th className="px-3 py-2">Score</th><th className="px-3 py-2">Result</th><th className="px-6 py-2 text-right">When</th></tr>
                        </thead>
                        <tbody className="divide-y divide-stone-100 dark:divide-stone-800">
                          {mine.attempts.map((a) => {
                            const x = quizzes.get(a.quiz_id)
                            return (
                              <tr key={a.id}>
                                <td className="px-6 py-2.5">
                                  {x ? <Link className="font-medium hover:underline" to={`/courses/${x.c.id}/quizzes/${a.quiz_id}`}>{x.q.title}</Link> : 'Quiz'}
                                </td>
                                <td className="px-3 py-2.5 tabular-nums">{a.score}/{a.total} ({a.percent}%)</td>
                                <td className="px-3 py-2.5"><Badge tone={a.passed ? 'green' : 'red'}>{a.passed ? 'Passed' : 'Failed'}</Badge></td>
                                <td className="px-6 py-2.5 text-right text-stone-500">{timeAgo(a.created_at)}</td>
                              </tr>
                            )
                          })}
                        </tbody>
                      </table>
                    </div>
                  )}
                </Card>
              </>
            )
          })()}
        </div>
      </div>
    </>
  )
}
