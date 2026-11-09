import { useState } from 'react'
import { Link } from 'react-router-dom'
import { BookOpen, Clock, Search } from 'lucide-react'
import { courseProgress } from '../lib/progress'
import { Badge, Card, Empty, ErrorBox, Input, PageHeader, ProgressBar, Spinner, color } from '../components/ui'
import { useLearning } from './Dashboard'

export default function Catalog() {
  const learning = useLearning()
  const [q, setQ] = useState('')
  if (learning.loading && !learning.data) return <Spinner />
  if (learning.error) return <ErrorBox message={learning.error} onRetry={learning.reload} />
  const [courses, mine] = learning.data!
  const needle = q.trim().toLowerCase()
  const shown = courses.filter((c) => !needle || `${c.code} ${c.title} ${c.description}`.toLowerCase().includes(needle))

  return (
    <>
      <PageHeader
        title="Courses"
        subtitle="Every standard, broken into lessons and quizzes."
        actions={
          <div className="relative w-full sm:w-72">
            <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400" />
            <Input placeholder="Search courses…" value={q} onChange={(e) => setQ(e.target.value)} className="pl-9" aria-label="Search courses" />
          </div>
        }
      />
      {shown.length === 0 ? (
        <Empty icon={<BookOpen className="size-8" />} title={needle ? 'No matching courses' : 'No courses yet'} />
      ) : (
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {shown.map((c) => {
            const p = courseProgress(c, mine.lessons, mine.attempts)
            const lessons = c.modules.flatMap((m) => m.lessons)
            const minutes = lessons.reduce((s, l) => s + l.minutes, 0)
            const col = color(c.color)
            return (
              <Link key={c.id} to={`/courses/${c.id}`} className="group">
                <Card className="flex h-full flex-col overflow-hidden transition group-hover:-translate-y-0.5 group-hover:shadow-lg">
                  <div className={`furrows relative h-28 ${col.bg} p-5 text-white`}>
                    <div className="text-xs font-bold tracking-widest uppercase opacity-90">{c.code}</div>
                    <BookOpen className="absolute right-4 bottom-4 size-10 opacity-30" />
                    {!c.published && <span className="absolute top-4 right-4"><Badge tone="amber">Draft</Badge></span>}
                  </div>
                  <div className="flex flex-1 flex-col p-5">
                    <h2 className="font-display text-lg leading-snug font-bold">{c.title}</h2>
                    <p className="mt-1 line-clamp-2 flex-1 text-sm text-stone-500">{c.description}</p>
                    <div className="mt-4 flex items-center gap-3 text-xs text-stone-500">
                      <span>{c.modules.length} modules</span>·<span>{lessons.length} lessons</span>·
                      <span className="inline-flex items-center gap-1"><Clock className="size-3" />{minutes} min</span>
                    </div>
                    <div className="mt-3 flex items-center gap-3">
                      <ProgressBar value={p.percent} className="flex-1" />
                      <span className="text-xs font-semibold">{p.percent}%</span>
                    </div>
                  </div>
                </Card>
              </Link>
            )
          })}
        </div>
      )}
    </>
  )
}
