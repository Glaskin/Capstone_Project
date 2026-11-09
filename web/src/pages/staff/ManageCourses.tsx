import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { BookOpen, Eye, Pencil, Plus, Sparkles, Trash2 } from 'lucide-react'
import { api } from '../../lib/api'
import { importSeed } from '../../lib/seed'
import { useAction, useAsync } from '../../lib/hooks'
import type { Course } from '../../lib/types'
import { Badge, Button, COLORS, Card, Empty, ErrorBox, Field, Input, Modal, PageHeader, Select, Spinner, Textarea, color, confirmDelete } from '../../components/ui'

export function CourseForm({ initial, onSaved, onCancel }: { initial: Partial<Course>; onSaved: (c: Course) => void; onCancel?: () => void }) {
  const [c, setC] = useState(initial)
  const { busy, run } = useAction()
  const save = () =>
    run(async () => {
      if (!c.title?.trim()) throw new Error('Title is required')
      onSaved(await api.saveCourse({
        id: c.id, title: c.title.trim(), code: c.code?.trim() ?? '', description: c.description ?? '',
        color: c.color ?? 'emerald', published: c.published ?? true, position: c.position ?? 0,
      }))
    }, 'Course saved')
  return (
    <div className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-[8rem_1fr]">
        <Field label="Code">{(id) => <Input id={id} placeholder="PAES 103" value={c.code ?? ''} onChange={(e) => setC({ ...c, code: e.target.value })} />}</Field>
        <Field label="Title">{(id) => <Input id={id} value={c.title ?? ''} onChange={(e) => setC({ ...c, title: e.target.value })} />}</Field>
      </div>
      <Field label="Description">{(id) => <Textarea id={id} rows={3} value={c.description ?? ''} onChange={(e) => setC({ ...c, description: e.target.value })} />}</Field>
      <div className="grid gap-4 sm:grid-cols-2">
        <Field label="Colour">
          {(id) => (
            <Select id={id} value={c.color ?? 'emerald'} onChange={(e) => setC({ ...c, color: e.target.value })}>
              {Object.keys(COLORS).map((k) => <option key={k} value={k}>{k[0].toUpperCase() + k.slice(1)}</option>)}
            </Select>
          )}
        </Field>
        <Field label="Order" hint="Lower numbers appear first.">
          {(id) => <Input id={id} type="number" value={c.position ?? 0} onChange={(e) => setC({ ...c, position: Number(e.target.value) })} />}
        </Field>
      </div>
      <label className="flex items-center gap-2 text-sm">
        <input type="checkbox" className="size-4 accent-emerald-700" checked={c.published ?? true} onChange={(e) => setC({ ...c, published: e.target.checked })} />
        Published (visible to students)
      </label>
      <div className="flex justify-end gap-2">
        {onCancel && <Button variant="secondary" onClick={onCancel}>Cancel</Button>}
        <Button busy={busy} onClick={save}>Save course</Button>
      </div>
    </div>
  )
}

export default function ManageCourses() {
  const list = useAsync(() => api.listCourseTrees(), [])
  const [creating, setCreating] = useState(false)
  const [seeding, setSeeding] = useState('')
  const { run } = useAction()
  const navigate = useNavigate()

  const addSamples = () =>
    run(async () => {
      try {
        await importSeed(api, setSeeding)
      } finally {
        setSeeding('')
      }
      list.reload()
    }, 'Sample courses added')

  return (
    <>
      <PageHeader
        eyebrow="Teaching"
        title="Manage courses"
        subtitle="Create courses, then add modules, lessons and quizzes."
        actions={
          <>
            <Button variant="secondary" busy={!!seeding} onClick={() => confirm('Add the 3 sample PAES courses?') && addSamples()}>
              <Sparkles className="size-4" /> {seeding || 'Add sample courses'}
            </Button>
            <Button onClick={() => setCreating(true)}><Plus className="size-4" /> New course</Button>
          </>
        }
      />
      {list.loading && !list.data ? <Spinner /> : list.error ? <ErrorBox message={list.error} onRetry={list.reload} /> : !list.data?.length ? (
        <Empty icon={<BookOpen className="size-8" />} title="No courses yet">Create one, or add the sample courses to get started.</Empty>
      ) : (
        <Card className="divide-y divide-stone-100 dark:divide-stone-800">
          {list.data.map((c) => {
            const lessons = c.modules.reduce((s, m) => s + m.lessons.length, 0)
            const quizzes = c.modules.reduce((s, m) => s + m.quizzes.length, 0)
            return (
              <div key={c.id} className="flex flex-wrap items-center gap-4 p-4">
                <div className={`grid size-12 shrink-0 place-items-center rounded-xl ${color(c.color).bg} text-white`}><BookOpen className="size-5" /></div>
                <div className="min-w-0 flex-1">
                  <div className="flex items-center gap-2">
                    <Link to={`/manage/courses/${c.id}`} className="truncate font-semibold hover:underline">{c.code && `${c.code} · `}{c.title}</Link>
                    {!c.published && <Badge tone="amber">Draft</Badge>}
                  </div>
                  <div className="text-sm text-stone-500">{c.modules.length} modules · {lessons} lessons · {quizzes} quizzes</div>
                </div>
                <div className="flex gap-1">
                  <Button variant="ghost" className="px-2" title="View as student" onClick={() => navigate(`/courses/${c.id}`)}><Eye className="size-4" /></Button>
                  <Button variant="ghost" className="px-2" title="Edit" onClick={() => navigate(`/manage/courses/${c.id}`)}><Pencil className="size-4" /></Button>
                  <Button variant="ghost" className="px-2 text-red-600" title="Delete"
                    onClick={() => confirmDelete(`"${c.title}" and everything in it (modules, lessons, quizzes, student progress)`) && run(async () => { await api.deleteCourse(c.id); list.reload() }, 'Course deleted')}>
                    <Trash2 className="size-4" />
                  </Button>
                </div>
              </div>
            )
          })}
        </Card>
      )}
      <Modal open={creating} onClose={() => setCreating(false)} title="New course">
        <CourseForm initial={{ color: 'emerald', published: false, position: list.data?.length ?? 0 }} onCancel={() => setCreating(false)}
          onSaved={(c) => navigate(`/manage/courses/${c.id}`)} />
      </Modal>
    </>
  )
}
