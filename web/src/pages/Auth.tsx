import { useState, type FormEvent, type ReactNode } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { MailCheck } from 'lucide-react'
import { api } from '../lib/api'
import { DEMO_ACCOUNTS } from '../lib/api/demo'
import { useAuth } from '../lib/hooks'
import { DemoBanner, Logo, ThemeToggle } from '../components/Layout'
import { Button, ErrorBox, Field, Input } from '../components/ui'

function AuthShell({ title, subtitle, children }: { title: string; subtitle?: ReactNode; children: ReactNode }) {
  return (
    <div className="min-h-screen">
      <DemoBanner />
      <div className="grid min-h-[calc(100vh-2rem)] lg:grid-cols-2">
        <div className="furrows hidden flex-col justify-between bg-linear-to-br from-emerald-800 to-lime-700 p-12 text-white lg:flex">
          <Link to="/"><Logo light /></Link>
          <blockquote className="font-display max-w-md text-3xl leading-snug">
            “Good design removes a hazard first, guards it second, and only warns about it when neither is possible.”
            <footer className="mt-4 font-sans text-sm text-emerald-100">— PAES 101, Lesson 1</footer>
          </blockquote>
          <p className="text-sm text-emerald-100/80">Agricultural engineering standards, one lesson at a time.</p>
        </div>
        <div className="flex flex-col p-6">
          <div className="flex items-center justify-between lg:justify-end">
            <Link to="/" className="lg:hidden"><Logo /></Link>
            <ThemeToggle />
          </div>
          <div className="m-auto w-full max-w-sm py-10">
            <h1 className="font-display text-3xl font-bold">{title}</h1>
            {subtitle && <p className="mt-2 text-stone-500">{subtitle}</p>}
            <div className="mt-8">{children}</div>
          </div>
        </div>
      </div>
    </div>
  )
}

function useSubmit(fn: () => Promise<void>) {
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)
  const onSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setError('')
    setBusy(true)
    try {
      await fn()
    } catch (err) {
      setError((err as Error).message)
    } finally {
      setBusy(false)
    }
  }
  return { error, busy, onSubmit }
}

export function Login() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const { refresh } = useAuth()
  const { error, busy, onSubmit } = useSubmit(async () => {
    await api.signIn(email, password)
    await refresh()
  })
  const demoLogin = async (demoEmail: string) => {
    await api.signIn(demoEmail, '')
    await refresh()
  }

  return (
    <AuthShell title="Welcome back" subtitle={<>New here? <Link to="/register" className="font-semibold text-emerald-700 dark:text-emerald-400">Create an account</Link></>}>
      {api.mode === 'demo' && (
        <div className="mb-6 rounded-2xl bg-amber-50 p-4 ring-1 ring-amber-200 dark:bg-amber-950/30 dark:ring-amber-900">
          <p className="mb-3 text-sm font-semibold text-amber-900 dark:text-amber-200">Try it instantly as…</p>
          <div className="grid grid-cols-3 gap-2">
            {DEMO_ACCOUNTS.map((a) => (
              <Button key={a.email} variant="secondary" onClick={() => demoLogin(a.email)}>
                {a.label}
              </Button>
            ))}
          </div>
        </div>
      )}
      <form onSubmit={onSubmit} className="space-y-4">
        {error && <ErrorBox message={error} />}
        <Field label="Email">{(id) => <Input id={id} type="email" autoComplete="email" required value={email} onChange={(e) => setEmail(e.target.value)} />}</Field>
        <Field label="Password" hint={api.mode === 'demo' ? 'Demo mode: any password works.' : undefined}>
          {(id) => <Input id={id} type="password" autoComplete="current-password" required={api.mode !== 'demo'} value={password} onChange={(e) => setPassword(e.target.value)} />}
        </Field>
        <div className="flex justify-end">
          <Link to="/forgot-password" className="text-sm font-medium text-emerald-700 dark:text-emerald-400">Forgot password?</Link>
        </div>
        <Button busy={busy} className="w-full py-2.5">Sign in</Button>
      </form>
    </AuthShell>
  )
}

export function Register() {
  const [f, setF] = useState({ first_name: '', last_name: '', username: '', email: '', password: '', confirm: '' })
  const [sent, setSent] = useState(false)
  const { refresh } = useAuth()
  const set = (k: keyof typeof f) => (e: { target: { value: string } }) => setF({ ...f, [k]: e.target.value })
  const { error, busy, onSubmit } = useSubmit(async () => {
    if (f.password.length < 8) throw new Error('Password must be at least 8 characters.')
    if (f.password !== f.confirm) throw new Error('Passwords do not match.')
    if (!/^[\w.-]{3,30}$/.test(f.username)) throw new Error('Username: 3–30 letters, numbers, dots, dashes or underscores.')
    const { needsEmailConfirmation } = await api.signUp({
      email: f.email.trim(), password: f.password, username: f.username.trim(), first_name: f.first_name.trim(), last_name: f.last_name.trim(),
    })
    if (needsEmailConfirmation) setSent(true)
    else await refresh()
  })

  if (sent)
    return (
      <AuthShell title="Check your email">
        <div className="flex gap-3 rounded-2xl bg-emerald-50 p-4 text-emerald-900 ring-1 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900">
          <MailCheck className="size-5 shrink-0" />
          <p className="text-sm">We sent a confirmation link to <b>{f.email}</b>. Click it to activate your account, then sign in.</p>
        </div>
        <Link to="/login" className="mt-6 block text-center text-sm font-semibold text-emerald-700 dark:text-emerald-400">Back to sign in</Link>
      </AuthShell>
    )

  return (
    <AuthShell title="Create your account" subtitle={<>Already registered? <Link to="/login" className="font-semibold text-emerald-700 dark:text-emerald-400">Sign in</Link></>}>
      <form onSubmit={onSubmit} className="space-y-4">
        {error && <ErrorBox message={error} />}
        <div className="grid grid-cols-2 gap-3">
          <Field label="First name">{(id) => <Input id={id} required autoComplete="given-name" value={f.first_name} onChange={set('first_name')} />}</Field>
          <Field label="Last name">{(id) => <Input id={id} required autoComplete="family-name" value={f.last_name} onChange={set('last_name')} />}</Field>
        </div>
        <Field label="Username">{(id) => <Input id={id} required autoComplete="username" value={f.username} onChange={set('username')} />}</Field>
        <Field label="Email">{(id) => <Input id={id} type="email" required autoComplete="email" value={f.email} onChange={set('email')} />}</Field>
        <Field label="Password" hint="At least 8 characters.">{(id) => <Input id={id} type="password" required autoComplete="new-password" value={f.password} onChange={set('password')} />}</Field>
        <Field label="Confirm password">{(id) => <Input id={id} type="password" required autoComplete="new-password" value={f.confirm} onChange={set('confirm')} />}</Field>
        <Button busy={busy} className="w-full py-2.5">Create account</Button>
      </form>
    </AuthShell>
  )
}

export function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [sent, setSent] = useState(false)
  const { error, busy, onSubmit } = useSubmit(async () => {
    await api.sendPasswordReset(email.trim())
    setSent(true)
  })
  return (
    <AuthShell title="Reset your password" subtitle="We’ll email you a link to choose a new one.">
      {sent ? (
        <div className="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900 ring-1 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900">
          {api.mode === 'demo' ? 'Demo mode doesn’t send emails — any password works for demo accounts.' : <>If an account exists for <b>{email}</b>, a reset link is on its way.</>}
        </div>
      ) : (
        <form onSubmit={onSubmit} className="space-y-4">
          {error && <ErrorBox message={error} />}
          <Field label="Email">{(id) => <Input id={id} type="email" required value={email} onChange={(e) => setEmail(e.target.value)} />}</Field>
          <Button busy={busy} className="w-full py-2.5">Send reset link</Button>
        </form>
      )}
      <Link to="/login" className="mt-6 block text-center text-sm font-semibold text-emerald-700 dark:text-emerald-400">Back to sign in</Link>
    </AuthShell>
  )
}

export function ResetPassword() {
  const [password, setPassword] = useState('')
  const [confirm, setConfirm] = useState('')
  const navigate = useNavigate()
  const { error, busy, onSubmit } = useSubmit(async () => {
    if (password.length < 8) throw new Error('Password must be at least 8 characters.')
    if (password !== confirm) throw new Error('Passwords do not match.')
    await api.updatePassword(password)
    navigate('/')
  })
  return (
    <AuthShell title="Choose a new password">
      <form onSubmit={onSubmit} className="space-y-4">
        {error && <ErrorBox message={error} />}
        <Field label="New password">{(id) => <Input id={id} type="password" required autoComplete="new-password" value={password} onChange={(e) => setPassword(e.target.value)} />}</Field>
        <Field label="Confirm new password">{(id) => <Input id={id} type="password" required autoComplete="new-password" value={confirm} onChange={(e) => setConfirm(e.target.value)} />}</Field>
        <Button busy={busy} className="w-full py-2.5">Save password</Button>
      </form>
    </AuthShell>
  )
}
