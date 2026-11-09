import { useState } from 'react'
import { Megaphone, Pencil, Pin, Plus, Trash2 } from 'lucide-react'
import { api } from '../lib/api'
import { isStaff, useAction, useAsync, useAuth } from '../lib/hooks'
import type { Announcement } from '../lib/types'
import { Badge, Button, Card, Empty, ErrorBox, Field, Input, Markdown, Modal, PageHeader, Spinner, Textarea, confirmDelete, timeAgo } from '../components/ui'

export default function Announcements() {
  const { profile } = useAuth()
  const staff = isStaff(profile)
  const list = useAsync(() => api.listAnnouncements(), [])
  const [editing, setEditing] = useState<Partial<Announcement> | null>(null)
  const { busy, run } = useAction()

  const save = () =>
    run(async () => {
      if (!editing?.title?.trim()) throw new Error('Title is required')
      await api.saveAnnouncement({ id: editing.id, title: editing.title.trim(), body: editing.body ?? '', pinned: !!editing.pinned })
      setEditing(null)
      list.reload()
    }, 'Announcement saved')

  return (
    <>
      <PageHeader
        title="Announcements"
        subtitle="News and reminders from your instructors."
        actions={staff && <Button onClick={() => setEditing({ title: '', body: '', pinned: false })}><Plus className="size-4" /> New announcement</Button>}
      />
      {list.loading && !list.data ? <Spinner /> : list.error ? <ErrorBox message={list.error} onRetry={list.reload} /> : !list.data?.length ? (
        <Empty icon={<Megaphone className="size-8" />} title="No announcements yet" />
      ) : (
        <div className="space-y-4">
          {list.data.map((a) => (
            <Card key={a.id} className="p-6">
              <div className="mb-3 flex items-start justify-between gap-4">
                <div>
                  <h2 className="font-display flex items-center gap-2 text-xl font-bold">
                    {a.title} {a.pinned && <Badge tone="amber"><Pin className="size-3" /> Pinned</Badge>}
                  </h2>
                  <p className="text-sm text-stone-500">{new Date(a.created_at).toLocaleDateString()} · {timeAgo(a.created_at)}</p>
                </div>
                {staff && (
                  <div className="flex gap-1">
                    <Button variant="ghost" className="px-2" aria-label="Edit" onClick={() => setEditing(a)}><Pencil className="size-4" /></Button>
                    <Button variant="ghost" className="px-2 text-red-600" aria-label="Delete"
                      onClick={() => confirmDelete(`"${a.title}"`) && run(async () => { await api.deleteAnnouncement(a.id); list.reload() }, 'Deleted')}>
                      <Trash2 className="size-4" />
                    </Button>
                  </div>
                )}
              </div>
              <Markdown source={a.body} />
            </Card>
          ))}
        </div>
      )}

      <Modal open={!!editing} onClose={() => setEditing(null)} title={editing?.id ? 'Edit announcement' : 'New announcement'}>
        {editing && (
          <div className="space-y-4">
            <Field label="Title">{(id) => <Input id={id} value={editing.title ?? ''} onChange={(e) => setEditing({ ...editing, title: e.target.value })} />}</Field>
            <Field label="Message" hint="Markdown supported: **bold**, *italic*, lists, links.">
              {(id) => <Textarea id={id} rows={6} value={editing.body ?? ''} onChange={(e) => setEditing({ ...editing, body: e.target.value })} />}
            </Field>
            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" checked={!!editing.pinned} onChange={(e) => setEditing({ ...editing, pinned: e.target.checked })} className="size-4 accent-emerald-700" />
              Pin to the top
            </label>
            <div className="flex justify-end gap-2">
              <Button variant="secondary" onClick={() => setEditing(null)}>Cancel</Button>
              <Button busy={busy} onClick={save}>Save</Button>
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}
