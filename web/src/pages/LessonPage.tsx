import { Link, useNavigate, useParams } from 'react-router-dom'
import { ArrowLeft, ArrowRight, CheckCircle2, Circle, ClipboardCheck, Clock, Download, FileText, Paperclip } from 'lucide-react'
import { api } from '../lib/api'
import { embedUrl } from '../lib/markdown'
import { courseProgress, stepHref } from '../lib/progress'
import type { CourseTree } from '../lib/types'
import { useAction, useAsync } from '../lib/hooks'
import { Button, ButtonLink, Card, ErrorBox, Markdown, ProgressBar, Spinner } from '../components/ui'
import { useCourse } from './CoursePage'

export function CourseOutline({ course, progress, current }: { course?: CourseTree; progress: ReturnType<typeof courseProgress> | null; current: { kind: string; id: number } }) {
  if (!course || !progress) return null
  const courseId = course.id
  return (
    <Card className="sticky top-6 p-4">
      <Link to={`/courses/${courseId}`} className="font-display font-bold hover:underline">{course.title}</Link>
      <div className="mt-2 mb-4 flex items-center gap-2 text-xs text-stone-500">
        <ProgressBar value={progress.percent} className="flex-1" /> {progress.percent}%
      </div>
      <ol className="space-y-0.5 text-sm">
        {progress.steps.map((s) => {
          const active = s.kind === current.kind && s.id === current.id
          return (
            <li key={`${s.kind}${s.id}`}>
              <Link
                to={stepHref(courseId, s)}
                className={`flex items-center gap-2 rounded-lg px-2 py-1.5 ${active ? 'bg-emerald-50 font-semibold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'hover:bg-stone-100 dark:hover:bg-stone-800'}`}
              >
                {s.done ? <CheckCircle2 className="size-4 shrink-0 text-emerald-600" /> : s.kind === 'quiz' ? <ClipboardCheck className="size-4 shrink-0 text-stone-400" /> : <Circle className="size-4 shrink-0 text-stone-300" />}
                <span className="truncate">{s.title}</span>
              </Link>
            </li>
          )
        })}
      </ol>
    </Card>
  )
}

export default function LessonPage() {
  const params = useParams()
  const courseId = Number(params.courseId)
  const lessonId = Number(params.lessonId)
  const navigate = useNavigate()
  const lesson = useAsync(() => api.getLesson(lessonId), [lessonId])
  const { progress, course, mine, reload } = useCourse([lessonId])
  const { busy, run } = useAction()

  if (lesson.loading && !lesson.data) return <Spinner />
  if (lesson.error) return <ErrorBox message={lesson.error} onRetry={lesson.reload} />
  const l = lesson.data!
  const done = mine.lessons.some((p) => p.lesson_id === lessonId)
  const steps = progress?.steps ?? []
  const idx = steps.findIndex((s) => s.kind === 'lesson' && s.id === lessonId)
  const prev = idx > 0 ? steps[idx - 1] : null
  const next = idx >= 0 && idx < steps.length - 1 ? steps[idx + 1] : null
  const video = l.video_url ? embedUrl(l.video_url) : null

  const completeAndContinue = () =>
    run(async () => {
      if (!done) await api.setLessonComplete(lessonId, true)
      if (next) navigate(stepHref(courseId, next))
      else {
        reload()
        navigate(`/courses/${courseId}`)
      }
    }, done ? undefined : 'Lesson completed ✓')

  return (
    <div className="grid gap-8 lg:grid-cols-[1fr_18rem]">
      <article className="min-w-0">
        <Link to={`/courses/${courseId}`} className="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-800 dark:hover:text-stone-200">
          <ArrowLeft className="size-4" /> {course?.title ?? 'Back to course'}
        </Link>
        <h1 className="font-display text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">{l.title}</h1>
        <div className="mt-2 mb-8 flex items-center gap-3 text-sm text-stone-500">
          <span className="inline-flex items-center gap-1"><Clock className="size-4" /> {l.minutes} min read</span>
          {done && <span className="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400"><CheckCircle2 className="size-4" /> Completed</span>}
        </div>

        {l.video_url && (
          <div className="mb-8 overflow-hidden rounded-2xl bg-black shadow-lg">
            {video ? (
              <iframe src={video} title={l.title} className="aspect-video w-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowFullScreen />
            ) : (
              <video src={l.video_url} controls className="aspect-video w-full" />
            )}
          </div>
        )}

        <Markdown source={l.content} className="prose-lg" />

        {l.attachments.length > 0 && (
          <Card className="mt-8 p-5">
            <h2 className="mb-3 flex items-center gap-2 font-semibold"><Paperclip className="size-4" /> Attachments</h2>
            <ul className="space-y-2">
              {l.attachments.map((a) => (
                <li key={a.url}>
                  <a href={a.url} target="_blank" rel="noreferrer" download={a.name} className="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium ring-1 ring-stone-200 hover:bg-stone-50 dark:ring-stone-700 dark:hover:bg-stone-800">
                    <FileText className="size-4 text-emerald-700" /> <span className="flex-1 truncate">{a.name}</span> <Download className="size-4 text-stone-400" />
                  </a>
                </li>
              ))}
            </ul>
          </Card>
        )}

        <div className="no-print mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 pt-6 dark:border-stone-800">
          {prev ? (
            <ButtonLink to={stepHref(courseId, prev)} variant="ghost"><ArrowLeft className="size-4" /> Previous</ButtonLink>
          ) : <span />}
          <div className="flex gap-2">
            {done && (
              <Button variant="secondary" busy={busy} onClick={() => run(async () => { await api.setLessonComplete(lessonId, false); reload() })}>
                Mark as not done
              </Button>
            )}
            <Button busy={busy} onClick={completeAndContinue}>
              {done ? (next ? 'Next' : 'Back to course') : next ? 'Complete & continue' : 'Complete lesson'} <ArrowRight className="size-4" />
            </Button>
          </div>
        </div>
      </article>
      <aside className="no-print hidden lg:block">
        <CourseOutline course={course} progress={progress} current={{ kind: 'lesson', id: lessonId }} />
      </aside>
    </div>
  )
}
