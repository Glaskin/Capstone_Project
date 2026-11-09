import { HashRouter, Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import { Spinner } from './components/ui'
import { AuthProvider, ToastProvider, isStaff, useAuth } from './lib/hooks'
import Landing from './pages/Landing'
import { ForgotPassword, Login, Register, ResetPassword } from './pages/Auth'
import Dashboard from './pages/Dashboard'
import Catalog from './pages/Catalog'
import CoursePage from './pages/CoursePage'
import LessonPage from './pages/LessonPage'
import QuizPage from './pages/QuizPage'
import Certificate from './pages/Certificate'
import Announcements from './pages/Announcements'
import ProfilePage from './pages/ProfilePage'
import ManageCourses from './pages/staff/ManageCourses'
import CourseEditor from './pages/staff/CourseEditor'
import LessonEditor from './pages/staff/LessonEditor'
import QuizEditor from './pages/staff/QuizEditor'
import Reports from './pages/staff/Reports'
import People from './pages/staff/People'

function AppRoutes() {
  const { profile, loading } = useAuth()
  if (loading) return <Spinner />

  if (!profile)
    return (
      <Routes>
        <Route path="/" element={<Landing />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/forgot-password" element={<ForgotPassword />} />
        <Route path="*" element={<Navigate to="/login" replace />} />
      </Routes>
    )

  const staff = isStaff(profile)
  return (
    <Routes>
      <Route path="/reset-password" element={<ResetPassword />} />
      <Route element={<Layout />}>
        <Route path="/" element={staff ? <Reports overview /> : <Dashboard />} />
        <Route path="/courses" element={<Catalog />} />
        <Route path="/courses/:courseId" element={<CoursePage />} />
        <Route path="/courses/:courseId/lessons/:lessonId" element={<LessonPage />} />
        <Route path="/courses/:courseId/quizzes/:quizId" element={<QuizPage />} />
        <Route path="/courses/:courseId/certificate" element={<Certificate />} />
        <Route path="/announcements" element={<Announcements />} />
        <Route path="/profile" element={<ProfilePage />} />
        {staff && (
          <>
            <Route path="/manage" element={<ManageCourses />} />
            <Route path="/manage/courses/:courseId" element={<CourseEditor />} />
            <Route path="/manage/lessons/:lessonId" element={<LessonEditor />} />
            <Route path="/manage/quizzes/:quizId" element={<QuizEditor />} />
            <Route path="/manage/reports" element={<Reports />} />
            <Route path="/manage/users" element={<People />} />
          </>
        )}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Route>
    </Routes>
  )
}

export default function App() {
  return (
    <HashRouter>
      <ToastProvider>
        <AuthProvider>
          <AppRoutes />
        </AuthProvider>
      </ToastProvider>
    </HashRouter>
  )
}
