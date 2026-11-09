import { useEffect, useState, type ReactNode } from 'react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import {
  BarChart3, BookOpen, FlaskConical, GraduationCap, LayoutDashboard, LogOut, Megaphone, Menu, Moon, PencilRuler, Sun, User, Users, X,
} from 'lucide-react'
import { api } from '../lib/api'
import { resetDemo } from '../lib/api/demo'
import { isStaff, useAuth } from '../lib/hooks'
import { initials } from './ui'

export function Logo({ light }: { light?: boolean }) {
  return (
    <span className="flex items-center gap-2">
      <img src="./favicon.svg" alt="" className="size-8" />
      <span className={`font-display text-xl font-bold tracking-tight ${light ? 'text-white' : 'text-stone-900 dark:text-white'}`}>
        PAES <span className="text-emerald-600 dark:text-emerald-400">Learn</span>
      </span>
    </span>
  )
}

export function ThemeToggle() {
  const [dark, setDark] = useState(() => document.documentElement.classList.contains('dark'))
  useEffect(() => {
    document.documentElement.classList.toggle('dark', dark)
    localStorage.theme = dark ? 'dark' : 'light'
  }, [dark])
  return (
    <button
      onClick={() => setDark(!dark)}
      className="rounded-xl p-2 text-stone-500 hover:bg-stone-200/60 dark:hover:bg-stone-800"
      aria-label={dark ? 'Switch to light mode' : 'Switch to dark mode'}
    >
      {dark ? <Sun className="size-5" /> : <Moon className="size-5" />}
    </button>
  )
}

export function DemoBanner() {
  if (api.mode !== 'demo') return null
  return (
    <div className="no-print flex flex-wrap items-center justify-center gap-x-3 gap-y-1 bg-amber-100 px-4 py-1.5 text-center text-xs text-amber-900 dark:bg-amber-900/40 dark:text-amber-200">
      <FlaskConical className="size-3.5" />
      <span>
        <b>Demo mode</b> — data is saved only in this browser. Connect Supabase to go live.
      </span>
      <button className="font-semibold underline" onClick={() => confirm('Reset all demo data?') && resetDemo()}>
        Reset demo
      </button>
    </div>
  )
}

function NavItem({ to, icon, children, end }: { to: string; icon: ReactNode; children: ReactNode; end?: boolean }) {
  return (
    <NavLink
      to={to}
      end={end}
      className={({ isActive }) =>
        `flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition ${
          isActive
            ? 'bg-emerald-700 text-white shadow-sm'
            : 'text-stone-600 hover:bg-stone-200/60 dark:text-stone-400 dark:hover:bg-stone-800'
        }`
      }
    >
      {icon}
      {children}
    </NavLink>
  )
}

export default function Layout() {
  const { profile } = useAuth()
  const [open, setOpen] = useState(false)
  const location = useLocation()
  const navigate = useNavigate()
  useEffect(() => {
    setOpen(false)
    window.scrollTo(0, 0)
  }, [location.pathname])
  if (!profile) return null
  const staff = isStaff(profile)
  const ic = 'size-4.5'

  const nav = (
    <nav className="flex flex-col gap-1">
      <NavItem to="/" end icon={<LayoutDashboard className={ic} />}>{staff ? 'Overview' : 'Dashboard'}</NavItem>
      <NavItem to="/courses" icon={<BookOpen className={ic} />}>Courses</NavItem>
      <NavItem to="/announcements" icon={<Megaphone className={ic} />}>Announcements</NavItem>
      <NavItem to="/profile" icon={<User className={ic} />}>My profile</NavItem>
      {staff && (
        <>
          <div className="mt-5 mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-stone-400">Teaching</div>
          <NavItem to="/manage" end icon={<PencilRuler className={ic} />}>Manage courses</NavItem>
          <NavItem to="/manage/reports" icon={<BarChart3 className={ic} />}>Student reports</NavItem>
          <NavItem to="/manage/users" icon={<Users className={ic} />}>People</NavItem>
        </>
      )}
    </nav>
  )

  const userBox = (
    <div className="flex items-center gap-3 rounded-2xl bg-stone-100 p-3 dark:bg-stone-900">
      <div className="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-700 text-sm font-bold text-white">{initials(profile)}</div>
      <div className="min-w-0 flex-1">
        <div className="truncate text-sm font-semibold">{profile.first_name || profile.username}</div>
        <div className="text-xs capitalize text-stone-500">{profile.role}</div>
      </div>
      <button
        onClick={async () => {
          await api.signOut()
          navigate('/')
        }}
        className="rounded-lg p-1.5 text-stone-500 hover:bg-stone-200 dark:hover:bg-stone-800"
        aria-label="Sign out"
        title="Sign out"
      >
        <LogOut className="size-4" />
      </button>
    </div>
  )

  return (
    <div className="min-h-screen">
      <DemoBanner />
      <div className="flex">
        {/* Desktop sidebar */}
        <aside className="no-print sticky top-0 hidden h-screen w-64 shrink-0 flex-col gap-6 border-r border-stone-200 p-4 lg:flex dark:border-stone-800">
          <div className="flex items-center justify-between px-2 pt-1">
            <Logo />
            <ThemeToggle />
          </div>
          <div className="flex-1 overflow-y-auto">{nav}</div>
          {userBox}
        </aside>

        {/* Mobile drawer */}
        {open && (
          <div className="fixed inset-0 z-40 lg:hidden">
            <div className="absolute inset-0 bg-stone-950/50" onClick={() => setOpen(false)} />
            <aside className="absolute inset-y-0 left-0 flex w-72 flex-col gap-6 bg-stone-50 p-4 shadow-xl dark:bg-stone-950">
              <div className="flex items-center justify-between px-2 pt-1">
                <Logo />
                <button onClick={() => setOpen(false)} aria-label="Close menu" className="rounded-xl p-2">
                  <X className="size-5" />
                </button>
              </div>
              <div className="flex-1 overflow-y-auto">{nav}</div>
              {userBox}
            </aside>
          </div>
        )}

        <div className="min-w-0 flex-1">
          <header className="no-print sticky top-0 z-30 flex items-center justify-between border-b border-stone-200 bg-stone-50/80 px-4 py-3 backdrop-blur lg:hidden dark:border-stone-800 dark:bg-stone-950/80">
            <button onClick={() => setOpen(true)} aria-label="Open menu" className="rounded-xl p-2">
              <Menu className="size-5" />
            </button>
            <Logo />
            <ThemeToggle />
          </header>
          <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-10">
            <Outlet />
          </main>
          <footer className="no-print mx-auto max-w-6xl px-4 pb-8 text-xs text-stone-400 sm:px-6 lg:px-10">
            <GraduationCap className="mr-1 inline size-3.5" /> PAES Learn · Philippine Agricultural Engineering Standards learning portal
          </footer>
        </div>
      </div>
    </div>
  )
}
