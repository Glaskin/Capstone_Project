import { Award, BookOpenCheck, ClipboardCheck, LineChart, ShieldCheck, Tractor } from 'lucide-react'
import { DemoBanner, Logo, ThemeToggle } from '../components/Layout'
import { ButtonLink } from '../components/ui'
import { SEED } from '../lib/seed'

const features = [
  { icon: BookOpenCheck, title: 'Bite-sized lessons', text: 'Each standard broken into short readings with tables, images and video.' },
  { icon: ClipboardCheck, title: 'Self-check quizzes', text: 'Randomized questions, instant feedback and explanations for every answer.' },
  { icon: LineChart, title: 'Progress you can see', text: 'Track every lesson and quiz, keep a learning streak, pick up where you left off.' },
  { icon: Award, title: 'Certificates', text: 'Finish a course and pass its quizzes to print a certificate of completion.' },
  { icon: ShieldCheck, title: 'Secure by design', text: 'Server-side grading and row-level security — answer keys never reach the browser.' },
  { icon: Tractor, title: 'Built for PAES', text: 'Made for Philippine Agricultural Engineering Standards, from machinery safety up.' },
]

export default function Landing() {
  return (
    <div className="min-h-screen">
      <DemoBanner />
      <header className="mx-auto flex max-w-6xl items-center justify-between px-6 py-5">
        <Logo />
        <div className="flex items-center gap-2">
          <ThemeToggle />
          <ButtonLink to="/login" variant="ghost">Sign in</ButtonLink>
          <ButtonLink to="/register" className="hidden sm:inline-flex">Get started</ButtonLink>
        </div>
      </header>

      <section className="mx-auto max-w-6xl px-6 pt-8 pb-16">
        <div className="furrows relative overflow-hidden rounded-[2rem] bg-linear-to-br from-emerald-800 via-emerald-700 to-lime-700 px-8 py-16 text-white shadow-xl sm:px-14 sm:py-20">
          <div className="absolute -right-16 -bottom-24 size-80 rounded-full bg-amber-300/25 blur-2xl" />
          <div className="relative max-w-2xl">
            <p className="mb-4 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide uppercase">
              Philippine Agricultural Engineering Standards
            </p>
            <h1 className="font-display text-4xl leading-tight font-bold sm:text-6xl">
              Learn the standards that keep farms safe and productive.
            </h1>
            <p className="mt-5 text-lg text-emerald-50/90">
              Short lessons, quick quizzes and real progress tracking for agricultural engineering students and faculty.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <ButtonLink to="/register" className="bg-white !text-emerald-800 hover:bg-emerald-50">Create a free account</ButtonLink>
              <ButtonLink to="/login" className="bg-white/10 ring-1 ring-white/40 hover:bg-white/20">I already have one</ButtonLink>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-6 pb-16">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {features.map(({ icon: Icon, title, text }) => (
            <div key={title} className="rounded-2xl bg-white p-6 ring-1 ring-stone-200 dark:bg-stone-900 dark:ring-stone-800">
              <div className="mb-4 grid size-10 place-items-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                <Icon className="size-5" />
              </div>
              <h3 className="font-semibold">{title}</h3>
              <p className="mt-1 text-sm text-stone-500">{text}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-6 pb-24">
        <h2 className="font-display mb-6 text-2xl font-bold">Starter courses</h2>
        <div className="grid gap-4 md:grid-cols-3">
          {SEED.map((c) => (
            <div key={c.code} className="rounded-2xl border border-stone-200 p-6 dark:border-stone-800">
              <div className="text-xs font-bold tracking-wider text-emerald-700 uppercase dark:text-emerald-400">{c.code}</div>
              <div className="font-display mt-1 text-lg font-bold">{c.title}</div>
              <p className="mt-2 text-sm text-stone-500">{c.description}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  )
}
