import { useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { ArrowDown, ArrowLeft, ArrowUp, ClipboardCheck, Eye, FileText, Pencil, Plus, Trash2 } from 'lucide-react'
import { api } from '../../lib/api'
import { useAction, useAsync } from '../../lib/hooks'
import type { Module } from '../../lib/types'
import { Button, ButtonLink, Card, Empty, ErrorBox, Field, Input, Modal, PageHeader, Spinner, Textarea, confirmDelete } from '../../components/ui'
import { CourseForm } from './ManageCourses'

/** Moves item i up/down by renumbering positions, saving only what changed. */
async function move<T extends { id: number; position: number }>(items: T[], i: number, dir: -1 | 1, save: (x: Partial<T>) => Promise<unknown>) {
  const j = i + dir
  if (j < 0 || j >= items.length) return
  const order = [...items]
  ;[order[i], order[j]] = [order[j], order[i]]
  await Promise.all(order.map((x, pos) => (x.position !== pos ? save({ id: x.id, position: pos } as Partial<T>) : null)))
}

function Reorder({ onUp, onDown, first, last }: { onUp: () => void; onDown: () => void; first: boolean; last: boolean }) {
  return (
    <span className="flex">
      <Button variant="ghost" className="px-1.5" disabled={first} onClick={onUp} aria-label="Move up"><ArrowUp className="size-3.5" /></Button>
      <Button variant="ghost" className="px-1.5" disabled={last} onClick={onDown} aria-label="Move down"><ArrowDown className="size-3.5" /></Button>
    </span>
  )
}

export default function CourseEditor() {
  const courseId = Number(useParams().courseId)
  const navigate = useNavigate()
  const tree = useAsync(() => api.listCourseTrees(), [courseId])
  const [editingCourse, setEditingCourse] = useState(false)
  const [mod, setMod] = useState<Partial<Module> | null>(null)
  const { busy, run } = useAction()

  if (tree.loading && !tree.data) return <Spinner />
  if (tree.error) return <ErrorBox message={tree.error} onRetry={tree.reload} />
  const course = tree.data!.find((c) => c.id === courseId)
  if (!course) return <ErrorBox message="Course not found." />
  const act = (fn: () => Promise<unknown>, msg?: string) => run(async () => { await fn(); tree.reload() }, msg)

  const saveModule = () =>
    run(async () => {
      if (!mod?.title?.trim()) throw new Error('Title is required')
      await api.saveModule({ id: mod.id, title: mod.title.trim(), description: mod.description ?? '', course_id: courseId, position: mod.position ?? course.modules.length })
      setMod(null)
      tree.reload()
    }, 'Module saved')

  const addLesson = (m: (typeof course.modules)[number]) =>
    run(async () => {
      const l = await api.saveLesson({ module_id: m.id, title: 'Untitled lesson', content: '', video_url: '', attachments: [], minutes: 5, position: m.lessons.length })
      navigate(`/manage/lessons/${l.id}`)
    })
  const addQuiz = (m: (typeof course.modules)[number]) =>
    run(async () => {
      const q = await api.saveQuiz({ module_id: m.id, title: `${m.title} Quiz`, description: '', question_limit: null, passing_percent: 70, time_limit_minutes: null, position: m.quizzes.length })
      navigate(`/manage/quizzes/${q.id}`)
    })

  return (
    <>
      <Link to="/manage" className="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
        <ArrowLeft className="size-4" /> All courses
      </Link>
      <PageHeader
        eyebrow={course.code || 'Course'}
        title={course.title}
        subtitle={course.published ? 'Published — students can see this course.' : 'Draft — only staff can see this course.'}
        actions={
          <>
            <ButtonLink to={`/courses/${course.id}`} variant="secondary"><Eye className="size-4" /> Preview</ButtonLink>
            <Button variant="secondary" onClick={() => setEditingCourse(true)}><Pencil className="size-4" /> Course details</Button>
            <Button onClick={() => setMod({ title: '', description: '' })}><Plus className="size-4" /> Add module</Button>
          </>
        }
      />

      {course.modules.length === 0 && <Empty icon={<FileText className="size-8" />} title="No modules yet">Modules group related lessons and a quiz.</Empty>}

      <div className="space-y-5">
        {course.modules.map((m, mi) => (
          <Card key={m.id} className="overflow-hidden">
            <div className="flex flex-wrap items-center gap-3 border-b border-stone-100 bg-stone-50 p-4 dark:border-stone-800 dark:bg-stone-900/60">
              <div className="min-w-0 flex-1">
                <div className="text-xs font-semibold tracking-wider text-stone-400 uppercase">Module {mi + 1}</div>
                <div className="font-display text-lg font-bold">{m.title}</div>
              </div>
              <Reorder first={mi === 0} last={mi === course.modules.length - 1}
                onUp={() => act(() => move(course.modules, mi, -1, api.saveModule))} onDown={() => act(() => move(course.modules, mi, 1, api.saveModule))} />
              <Button variant="ghost" className="px-2" aria-label="Edit module" onClick={() => setMod(m)}><Pencil className="size-4" /></Button>
              <Button variant="ghost" className="px-2 text-red-600" aria-label="Delete module"
                onClick={() => confirmDelete(`module "${m.title}" with its lessons and quizzes`) && act(() => api.deleteModule(m.id), 'Module deleted')}>
                <Trash2 className="size-4" />
              </Button>
            </div>
            <ul className="divide-y divide-stone-100 dark:divide-stone-800">
              {m.lessons.map((l, li) => (
                <li key={`l${l.id}`} className="flex items-center gap-3 px-4 py-2">
                  <FileText className="size-4 text-stone-400" />
                  <Link to={`/manage/lessons/${l.id}`} className="flex-1 truncate font-medium hover:underline">{l.title}</Link>
                  <span className="hidden text-xs text-stone-400 sm:inline">{l.minutes} min</span>
                  <Reorder first={li === 0} last={li === m.lessons.length - 1}
                    onUp={() => act(() => move(m.lessons, li, -1, api.saveLesson))} onDown={() => act(() => move(m.lessons, li, 1, api.saveLesson))} />
                  <Button variant="ghost" className="px-2 text-red-600" aria-label="Delete lesson"
                    onClick={() => confirmDelete(`lesson "${l.title}"`) && act(() => api.deleteLesson(l.id), 'Lesson deleted')}>
                    <Trash2 className="size-4" />
                  </Button>
                </li>
              ))}
              {m.quizzes.map((q) => (
                <li key={`q${q.id}`} className="flex items-center gap-3 px-4 py-2">
                  <ClipboardCheck className="size-4 text-emerald-600" />
                  <Link to={`/manage/quizzes/${q.id}`} className="flex-1 truncate font-semibold hover:underline">{q.title}</Link>
                  <span className="hidden text-xs text-stone-400 sm:inline">pass {q.passing_percent}%</span>
                  <Button variant="ghost" className="px-2 text-red-600" aria-label="Delete quiz"
                    onClick={() => confirmDelete(`quiz "${q.title}" and all its attempts`) && act(() => api.deleteQuiz(q.id), 'Quiz deleted')}>
                    <Trash2 className="size-4" />
                  </Button>
                </li>
              ))}
            </ul>
            <div className="flex gap-2 p-3">
              <Button variant="ghost" busy={busy} onClick={() => addLesson(m)}><Plus className="size-4" /> Lesson</Button>
              <Button variant="ghost" busy={busy} onClick={() => addQuiz(m)}><Plus className="size-4" /> Quiz</Button>
            </div>
          </Card>
        ))}
      </div>

      <Modal open={editingCourse} onClose={() => setEditingCourse(false)} title="Course details">
        <CourseForm initial={course} onCancel={() => setEditingCourse(false)} onSaved={() => { setEditingCourse(false); tree.reload() }} />
      </Modal>
      <Modal open={!!mod} onClose={() => setMod(null)} title={mod?.id ? 'Edit module' : 'New module'}>
        {mod && (
          <div className="space-y-4">
            <Field label="Title">{(id) => <Input id={id} value={mod.title ?? ''} onChange={(e) => setMod({ ...mod, title: e.target.value })} />}</Field>
            <Field label="Description">{(id) => <Textarea id={id} rows={3} value={mod.description ?? ''} onChange={(e) => setMod({ ...mod, description: e.target.value })} />}</Field>
            <div className="flex justify-end gap-2">
              <Button variant="secondary" onClick={() => setMod(null)}>Cancel</Button>
              <Button busy={busy} onClick={saveModule}>Save module</Button>
            </div>
          </div>
        )}
      </Modal>
    </>
  )
}
