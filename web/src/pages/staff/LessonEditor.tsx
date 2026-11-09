import { useEffect, useRef, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, Eye, ImagePlus, Paperclip, Save, Trash2 } from 'lucide-react'
import { api } from '../../lib/api'
import { embedUrl } from '../../lib/markdown'
import { useAction, useAsync } from '../../lib/hooks'
import type { Lesson } from '../../lib/types'
import { Button, ButtonLink, Card, ErrorBox, Field, Input, Markdown, PageHeader, Spinner, Textarea } from '../../components/ui'

export default function LessonEditor() {
  const lessonId = Number(useParams().lessonId)
  const loaded = useAsync(() => Promise.all([api.getLesson(lessonId), api.listCourseTrees()]), [lessonId])
  const [l, setL] = useState<Lesson | null>(null)
  const [dirty, setDirty] = useState(false)
  const { busy, run } = useAction()
  const imageInput = useRef<HTMLInputElement>(null)
  const fileInput = useRef<HTMLInputElement>(null)
  const textarea = useRef<HTMLTextAreaElement>(null)

  useEffect(() => {
    if (loaded.data) setL(loaded.data[0])
  }, [loaded.data])

  // Warn before leaving with unsaved changes.
  useEffect(() => {
    if (!dirty) return
    const h = (e: BeforeUnloadEvent) => e.preventDefault()
    addEventListener('beforeunload', h)
    return () => removeEventListener('beforeunload', h)
  }, [dirty])

  if (loaded.loading && !loaded.data) return <Spinner />
  if (loaded.error) return <ErrorBox message={loaded.error} onRetry={loaded.reload} />
  if (!l) return <Spinner />
  const course = loaded.data![1].find((c) => c.modules.some((m) => m.id === l.module_id))
  const update = (patch: Partial<Lesson>) => {
    setL({ ...l, ...patch })
    setDirty(true)
  }

  const save = () =>
    run(async () => {
      if (!l.title.trim()) throw new Error('Title is required')
      await api.saveLesson({ id: l.id, title: l.title.trim(), content: l.content, video_url: l.video_url.trim(), attachments: l.attachments, minutes: l.minutes })
      setDirty(false)
    }, 'Lesson saved')

  const insertAtCursor = (text: string) => {
    const ta = textarea.current
    const pos = ta?.selectionStart ?? l.content.length
    update({ content: l.content.slice(0, pos) + text + l.content.slice(pos) })
  }

  const upload = (file: File | undefined, asImage: boolean) =>
    file &&
    run(async () => {
      const url = await api.uploadFile(file)
      if (asImage) insertAtCursor(`\n![${file.name}](${url})\n`)
      else update({ attachments: [...l.attachments, { name: file.name, url }] })
    }, 'Uploaded — remember to save')

  return (
    <>
      <Link to={course ? `/manage/courses/${course.id}` : '/manage'} className="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
        <ArrowLeft className="size-4" /> {course?.title ?? 'Back'}
      </Link>
      <PageHeader
        eyebrow="Edit lesson"
        title={l.title || 'Untitled lesson'}
        actions={
          <>
            {course && <ButtonLink variant="secondary" to={`/courses/${course.id}/lessons/${l.id}`}><Eye className="size-4" /> View</ButtonLink>}
            <Button busy={busy} onClick={save}><Save className="size-4" /> {dirty ? 'Save changes' : 'Saved'}</Button>
          </>
        }
      />

      <div className="space-y-6">
        <Card className="grid gap-4 p-6 sm:grid-cols-[1fr_8rem]">
          <Field label="Title">{(id) => <Input id={id} value={l.title} onChange={(e) => update({ title: e.target.value })} />}</Field>
          <Field label="Minutes">{(id) => <Input id={id} type="number" min={1} value={l.minutes} onChange={(e) => update({ minutes: Number(e.target.value) })} />}</Field>
          <div className="sm:col-span-2">
            <Field label="Video (optional)" hint="YouTube or Vimeo link, or a direct link to an .mp4 file.">
              {(id) => <Input id={id} placeholder="https://www.youtube.com/watch?v=…" value={l.video_url} onChange={(e) => update({ video_url: e.target.value })} />}
            </Field>
            {l.video_url && !embedUrl(l.video_url) && !/\.(mp4|webm|ogg)(\?|$)/i.test(l.video_url) && (
              <p className="mt-1 text-xs text-amber-700">This doesn’t look like a YouTube/Vimeo link or video file — it may not play.</p>
            )}
          </div>
        </Card>

        <div className="grid gap-6 xl:grid-cols-2">
          <Card className="flex flex-col p-4">
            <div className="mb-2 flex items-center justify-between">
              <span className="text-sm font-semibold">Content (Markdown)</span>
              <Button variant="ghost" className="text-xs" busy={busy} onClick={() => imageInput.current?.click()}><ImagePlus className="size-4" /> Insert image</Button>
            </div>
            <Textarea ref={textarea} rows={24} value={l.content} onChange={(e) => update({ content: e.target.value })} className="flex-1 font-mono text-[13px] leading-relaxed"
              placeholder={'## Heading\n\nWrite your lesson here. Use **bold**, lists, tables and > quotes.'} />
            <p className="mt-2 text-xs text-stone-500">
              Markdown: <code>## Heading</code> · <code>**bold**</code> · <code>- list</code> · <code>| table |</code> · <code>&gt; quote</code>
            </p>
          </Card>
          <Card className="p-6">
            <div className="mb-4 text-sm font-semibold text-stone-500">Preview</div>
            <Markdown source={l.content || '*Nothing yet.*'} />
          </Card>
        </div>

        <Card className="p-6">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="flex items-center gap-2 font-semibold"><Paperclip className="size-4" /> Attachments</h2>
            <Button variant="secondary" busy={busy} onClick={() => fileInput.current?.click()}>Upload file</Button>
          </div>
          {l.attachments.length === 0 ? <p className="text-sm text-stone-500">PDFs, slides or worksheets students can download.</p> : (
            <ul className="space-y-2">
              {l.attachments.map((a, i) => (
                <li key={a.url} className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm ring-1 ring-stone-200 dark:ring-stone-700">
                  <Input aria-label="Attachment name" value={a.name} onChange={(e) => update({ attachments: l.attachments.map((x, j) => (j === i ? { ...x, name: e.target.value } : x)) })} />
                  <Button variant="ghost" className="px-2 text-red-600" aria-label="Remove attachment" onClick={() => update({ attachments: l.attachments.filter((_, j) => j !== i) })}>
                    <Trash2 className="size-4" />
                  </Button>
                </li>
              ))}
            </ul>
          )}
        </Card>
      </div>

      <input ref={imageInput} type="file" accept="image/*" hidden onChange={(e) => { upload(e.target.files?.[0], true); e.target.value = '' }} />
      <input ref={fileInput} type="file" hidden accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,image/*,video/*"
        onChange={(e) => { upload(e.target.files?.[0], false); e.target.value = '' }} />
    </>
  )
}
