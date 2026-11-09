import { useEffect, useId, useRef, type ButtonHTMLAttributes, type ComponentProps, type ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { AlertTriangle, Loader2, X } from 'lucide-react'
import { renderMarkdown } from '../lib/markdown'

const cx = (...c: (string | false | null | undefined)[]) => c.filter(Boolean).join(' ')

// Course accent colours (stored as a name in the database).
export const COLORS: Record<string, { bg: string; soft: string; text: string; ring: string }> = {
  emerald: { bg: 'bg-emerald-700', soft: 'bg-emerald-50 dark:bg-emerald-950/40', text: 'text-emerald-700 dark:text-emerald-400', ring: 'stroke-emerald-600' },
  sky: { bg: 'bg-sky-700', soft: 'bg-sky-50 dark:bg-sky-950/40', text: 'text-sky-700 dark:text-sky-400', ring: 'stroke-sky-600' },
  amber: { bg: 'bg-amber-600', soft: 'bg-amber-50 dark:bg-amber-950/40', text: 'text-amber-700 dark:text-amber-400', ring: 'stroke-amber-500' },
  rose: { bg: 'bg-rose-700', soft: 'bg-rose-50 dark:bg-rose-950/40', text: 'text-rose-700 dark:text-rose-400', ring: 'stroke-rose-600' },
  violet: { bg: 'bg-violet-700', soft: 'bg-violet-50 dark:bg-violet-950/40', text: 'text-violet-700 dark:text-violet-400', ring: 'stroke-violet-600' },
  stone: { bg: 'bg-stone-700', soft: 'bg-stone-100 dark:bg-stone-800/60', text: 'text-stone-700 dark:text-stone-300', ring: 'stroke-stone-600' },
}
export const color = (name: string) => COLORS[name] ?? COLORS.emerald

type Variant = 'primary' | 'secondary' | 'ghost' | 'danger'
const variants: Record<Variant, string> = {
  primary: 'bg-emerald-700 text-white hover:bg-emerald-800 shadow-sm',
  secondary: 'bg-white text-stone-800 ring-1 ring-stone-300 hover:bg-stone-100 dark:bg-stone-900 dark:text-stone-100 dark:ring-stone-700 dark:hover:bg-stone-800',
  ghost: 'text-stone-600 hover:bg-stone-200/60 dark:text-stone-300 dark:hover:bg-stone-800',
  danger: 'bg-red-600 text-white hover:bg-red-700',
}
const btnBase = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition disabled:opacity-50 disabled:pointer-events-none'

export function Button({ variant = 'primary', busy, className, children, ...rest }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: Variant; busy?: boolean }) {
  return (
    <button className={cx(btnBase, variants[variant], className)} disabled={busy || rest.disabled} {...rest}>
      {busy && <Loader2 className="size-4 animate-spin" />}
      {children}
    </button>
  )
}

export function ButtonLink({ to, variant = 'primary', className, children }: { to: string; variant?: Variant; className?: string; children: ReactNode }) {
  return (
    <Link to={to} className={cx(btnBase, variants[variant], className)}>
      {children}
    </Link>
  )
}

export function Card({ className, children }: { className?: string; children: ReactNode }) {
  return <div className={cx('rounded-2xl bg-white ring-1 ring-stone-200 dark:bg-stone-900 dark:ring-stone-800', className)}>{children}</div>
}

const fieldCls = 'w-full rounded-xl border-0 bg-white px-3 py-2 text-sm ring-1 ring-stone-300 placeholder:text-stone-400 focus:ring-2 focus:ring-emerald-600 dark:bg-stone-900 dark:ring-stone-700'

export function Field({ label, hint, children }: { label: string; hint?: ReactNode; children: (id: string) => ReactNode }) {
  const id = useId()
  return (
    <div className="space-y-1">
      <label htmlFor={id} className="block text-sm font-medium text-stone-700 dark:text-stone-300">
        {label}
      </label>
      {children(id)}
      {hint && <p className="text-xs text-stone-500">{hint}</p>}
    </div>
  )
}

export const Input = ({ className, ...p }: ComponentProps<'input'>) => <input className={cx(fieldCls, className)} {...p} />
export const Textarea = ({ className, ...p }: ComponentProps<'textarea'>) => <textarea className={cx(fieldCls, className)} {...p} />
export const Select = ({ className, ...p }: ComponentProps<'select'>) => <select className={cx(fieldCls, className)} {...p} />

export function Badge({ tone = 'stone', children }: { tone?: 'stone' | 'green' | 'amber' | 'red' | 'sky'; children: ReactNode }) {
  const tones = {
    stone: 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300',
    green: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    sky: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300',
  }
  return <span className={cx('inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold', tones[tone])}>{children}</span>
}

export function ProgressBar({ value, className }: { value: number; className?: string }) {
  return (
    <div className={cx('h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800', className)} role="progressbar" aria-valuenow={value} aria-valuemin={0} aria-valuemax={100}>
      <div className="h-full rounded-full bg-emerald-600 transition-all duration-500" style={{ width: `${value}%` }} />
    </div>
  )
}

export function ProgressRing({ value, size = 56, ring = 'stroke-emerald-600' }: { value: number; size?: number; ring?: string }) {
  const r = 22
  const c = 2 * Math.PI * r
  return (
    <svg width={size} height={size} viewBox="0 0 50 50" className="-rotate-90" role="img" aria-label={`${value}% complete`}>
      <circle cx="25" cy="25" r={r} fill="none" strokeWidth="5" className="stroke-stone-200 dark:stroke-stone-800" />
      <circle cx="25" cy="25" r={r} fill="none" strokeWidth="5" strokeLinecap="round" className={cx(ring, 'transition-all duration-700')} strokeDasharray={c} strokeDashoffset={c - (c * value) / 100} />
      <text x="25" y="25" textAnchor="middle" dominantBaseline="central" className="fill-current text-[11px] font-bold" transform="rotate(90 25 25)">
        {value}%
      </text>
    </svg>
  )
}

export const Spinner = () => (
  <div className="flex justify-center py-16 text-stone-400" aria-label="Loading">
    <Loader2 className="size-6 animate-spin" />
  </div>
)

export function ErrorBox({ message, onRetry }: { message: string; onRetry?: () => void }) {
  return (
    <div className="flex items-start gap-3 rounded-2xl bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-200 dark:bg-red-950/40 dark:text-red-200 dark:ring-red-900">
      <AlertTriangle className="mt-0.5 size-4 shrink-0" />
      <div className="flex-1">{message}</div>
      {onRetry && (
        <button className="font-semibold underline" onClick={onRetry}>
          Retry
        </button>
      )}
    </div>
  )
}

export function Empty({ icon, title, children }: { icon: ReactNode; title: string; children?: ReactNode }) {
  return (
    <div className="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-stone-200 px-6 py-12 text-center dark:border-stone-800">
      <div className="text-stone-400">{icon}</div>
      <p className="font-semibold">{title}</p>
      {children && <div className="text-sm text-stone-500">{children}</div>}
    </div>
  )
}

export function PageHeader({ title, subtitle, actions, eyebrow }: { title: string; subtitle?: ReactNode; actions?: ReactNode; eyebrow?: ReactNode }) {
  return (
    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        {eyebrow && <div className="mb-1 text-sm font-medium text-stone-500">{eyebrow}</div>}
        <h1 className="font-display text-3xl font-bold tracking-tight text-stone-900 dark:text-white">{title}</h1>
        {subtitle && <p className="mt-1 text-stone-500">{subtitle}</p>}
      </div>
      {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
    </div>
  )
}

export function Modal({ open, onClose, title, children, wide }: { open: boolean; onClose: () => void; title: string; children: ReactNode; wide?: boolean }) {
  const ref = useRef<HTMLDialogElement>(null)
  useEffect(() => {
    const d = ref.current
    if (!d) return
    if (open && !d.open) d.showModal()
    if (!open && d.open) d.close()
  }, [open])
  return (
    <dialog
      ref={ref}
      onClose={onClose}
      onClick={(e) => e.target === ref.current && onClose()}
      className={cx('m-auto w-[calc(100%-2rem)] rounded-2xl bg-white p-0 text-stone-800 shadow-2xl backdrop:bg-stone-950/50 backdrop:backdrop-blur-sm dark:bg-stone-900 dark:text-stone-100', wide ? 'max-w-3xl' : 'max-w-lg')}
    >
      {open && (
        <div className="p-6">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="font-display text-xl font-bold">{title}</h2>
            <button onClick={onClose} className="rounded-lg p-1 text-stone-500 hover:bg-stone-100 dark:hover:bg-stone-800" aria-label="Close">
              <X className="size-5" />
            </button>
          </div>
          {children}
        </div>
      )}
    </dialog>
  )
}

export function Markdown({ source, className }: { source: string; className?: string }) {
  return (
    <div
      className={cx('prose prose-stone max-w-none dark:prose-invert prose-headings:font-display prose-a:text-emerald-700 prose-table:text-sm', className)}
      dangerouslySetInnerHTML={{ __html: renderMarkdown(source) }}
    />
  )
}

/** Confirm before destructive actions. */
export const confirmDelete = (what: string) => window.confirm(`Delete ${what}? This cannot be undone.`)

export const fullName = (p: { first_name: string; last_name: string; username: string }) => `${p.first_name} ${p.last_name}`.trim() || p.username

export const initials = (p: { first_name: string; last_name: string; username: string }) =>
  ((p.first_name[0] ?? '') + (p.last_name[0] ?? '')).toUpperCase() || p.username.slice(0, 2).toUpperCase()

export function timeAgo(iso: string) {
  const s = (Date.now() - new Date(iso).getTime()) / 1000
  if (s < 60) return 'just now'
  const units: [number, string][] = [[31536000, 'year'], [2592000, 'month'], [86400, 'day'], [3600, 'hour'], [60, 'minute']]
  for (const [secs, name] of units) {
    const n = Math.floor(s / secs)
    if (n >= 1) return `${n} ${name}${n > 1 ? 's' : ''} ago`
  }
  return 'just now'
}
