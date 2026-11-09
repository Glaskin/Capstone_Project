import { createContext, useCallback, useContext, useEffect, useState, type DependencyList, type ReactNode } from 'react'
import { api } from './api'
import type { Profile } from './types'

// ---------------------------------------------------------------- auth

interface AuthState {
  profile: Profile | null
  loading: boolean
  refresh: () => Promise<void>
}

const AuthContext = createContext<AuthState>({ profile: null, loading: true, refresh: async () => {} })

export function AuthProvider({ children }: { children: ReactNode }) {
  const [profile, setProfile] = useState<Profile | null>(null)
  const [loading, setLoading] = useState(true)

  const refresh = useCallback(async () => {
    try {
      setProfile(await api.currentProfile())
    } catch {
      setProfile(null)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    refresh()
    return api.onAuthChange(refresh)
  }, [refresh])

  return <AuthContext.Provider value={{ profile, loading, refresh }}>{children}</AuthContext.Provider>
}

export const useAuth = () => useContext(AuthContext)

export const isStaff = (p: Profile | null) => p?.role === 'faculty' || p?.role === 'admin'

// ---------------------------------------------------------------- data loading

/** Runs an async loader and tracks loading/error. `reload()` re-runs it. */
export function useAsync<T>(fn: () => Promise<T>, deps: DependencyList) {
  const [state, setState] = useState<{ data?: T; error?: string; loading: boolean }>({ loading: true })
  const [tick, setTick] = useState(0)

  useEffect(() => {
    let alive = true
    setState((s) => ({ ...s, loading: true, error: undefined }))
    fn().then(
      (data) => alive && setState({ data, loading: false }),
      (e: Error) => alive && setState({ error: e.message, loading: false }),
    )
    return () => {
      alive = false
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, tick])

  return { ...state, reload: () => setTick((t) => t + 1) }
}

// ---------------------------------------------------------------- toasts

type Toast = { id: number; text: string; tone: 'ok' | 'error' }
const ToastContext = createContext<(text: string, tone?: Toast['tone']) => void>(() => {})

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([])
  const push = useCallback((text: string, tone: Toast['tone'] = 'ok') => {
    const id = Date.now() + Math.random()
    setToasts((t) => [...t, { id, text, tone }])
    setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), 4000)
  }, [])
  return (
    <ToastContext.Provider value={push}>
      {children}
      <div className="fixed bottom-4 right-4 z-50 flex flex-col gap-2" role="status" aria-live="polite">
        {toasts.map((t) => (
          <div
            key={t.id}
            className={`animate-[fadeIn_.2s_ease-out] rounded-xl px-4 py-3 text-sm font-medium shadow-lg ${
              t.tone === 'ok' ? 'bg-stone-900 text-white dark:bg-white dark:text-stone-900' : 'bg-red-600 text-white'
            }`}
          >
            {t.text}
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  )
}

export const useToast = () => useContext(ToastContext)

/** Wraps an action: shows a toast on error (and optionally on success), and tracks busy state. */
export function useAction() {
  const toast = useToast()
  const [busy, setBusy] = useState(false)
  const run = useCallback(
    async <T,>(fn: () => Promise<T>, success?: string): Promise<T | undefined> => {
      setBusy(true)
      try {
        const r = await fn()
        if (success) toast(success)
        return r
      } catch (e) {
        toast((e as Error).message, 'error')
      } finally {
        setBusy(false)
      }
    },
    [toast],
  )
  return { busy, run }
}
