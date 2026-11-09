import { useState } from 'react'
import { Search, Users } from 'lucide-react'
import { api } from '../../lib/api'
import { useAction, useAsync, useAuth } from '../../lib/hooks'
import type { Role } from '../../lib/types'
import { Badge, Card, Empty, ErrorBox, Input, PageHeader, Select, Spinner, fullName, initials, timeAgo } from '../../components/ui'

const tone = { student: 'sky', faculty: 'green', admin: 'amber' } as const

export default function People() {
  const { profile, refresh } = useAuth()
  const list = useAsync(() => api.listProfiles(), [])
  const [q, setQ] = useState('')
  const [role, setRole] = useState<Role | 'all'>('all')
  const { run } = useAction()
  const isAdmin = profile?.role === 'admin'

  const changeRole = (id: string, r: Role) => {
    if (id === profile?.id && r !== 'admin' && !confirm('Remove your own admin access? You will lose access to this page.')) return
    run(async () => {
      await api.setRole(id, r)
      list.reload()
      if (id === profile?.id) await refresh()
    }, 'Role updated')
  }

  const needle = q.trim().toLowerCase()
  const shown = (list.data ?? []).filter((p) => (role === 'all' || p.role === role) && (!needle || `${p.first_name} ${p.last_name} ${p.username} ${p.email}`.toLowerCase().includes(needle)))

  return (
    <>
      <PageHeader
        eyebrow="Teaching"
        title="People"
        subtitle={isAdmin ? 'Everyone with an account. Admins can change roles.' : 'Everyone with an account. Ask an admin to change roles.'}
      />
      <div className="mb-4 flex flex-wrap gap-2">
        <div className="relative flex-1 sm:max-w-xs">
          <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400" />
          <Input placeholder="Search name, username, email…" value={q} onChange={(e) => setQ(e.target.value)} className="pl-9" aria-label="Search people" />
        </div>
        <Select value={role} onChange={(e) => setRole(e.target.value as Role | 'all')} className="w-40" aria-label="Filter by role">
          <option value="all">All roles</option>
          <option value="student">Students</option>
          <option value="faculty">Faculty</option>
          <option value="admin">Admins</option>
        </Select>
      </div>
      {list.loading && !list.data ? <Spinner /> : list.error ? <ErrorBox message={list.error} onRetry={list.reload} /> : shown.length === 0 ? (
        <Empty icon={<Users className="size-8" />} title="Nobody found" />
      ) : (
        <Card className="divide-y divide-stone-100 dark:divide-stone-800">
          {shown.map((p) => (
            <div key={p.id} className="flex flex-wrap items-center gap-4 p-4">
              <div className="grid size-10 shrink-0 place-items-center rounded-full bg-stone-200 text-sm font-bold dark:bg-stone-800">{initials(p)}</div>
              <div className="min-w-0 flex-1">
                <div className="font-semibold">{fullName(p)} {p.id === profile?.id && <span className="text-xs font-normal text-stone-500">(you)</span>}</div>
                <div className="truncate text-sm text-stone-500">@{p.username} · {p.email} · joined {timeAgo(p.created_at)}</div>
              </div>
              {isAdmin ? (
                <Select value={p.role} onChange={(e) => changeRole(p.id, e.target.value as Role)} className="w-32" aria-label={`Role for ${fullName(p)}`}>
                  <option value="student">Student</option>
                  <option value="faculty">Faculty</option>
                  <option value="admin">Admin</option>
                </Select>
              ) : (
                <Badge tone={tone[p.role]}>{p.role}</Badge>
              )}
            </div>
          ))}
        </Card>
      )}
    </>
  )
}
